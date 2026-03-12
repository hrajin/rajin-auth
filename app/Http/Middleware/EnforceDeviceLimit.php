<?php

namespace App\Http\Middleware;

use App\Models\DeviceSession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnforceDeviceLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $grantType = $request->input('grant_type');

        // Refresh token = same device rotating its token, not a new device login
        if ($grantType === 'refresh_token' || !in_array($grantType, ['authorization_code', 'password'])) {
            return $next($request);
        }

        $clientId = $request->input('client_id');
        $client = DB::table('oauth_clients')->where('id', $clientId)->first();

        // No limit configured for this client
        if (!$client || $client->max_devices_per_user === null) {
            return $next($request);
        }

        $userId = $this->resolveUserId($request, $grantType);

        // Can't identify the user yet — let Passport handle the error
        if (!$userId) {
            return $next($request);
        }

        $fingerprint = hash('sha256', $request->userAgent() ?? '');

        // Same device re-authenticating — always allow
        $alreadyRegistered = DeviceSession::where('user_id', $userId)
            ->where('client_id', $clientId)
            ->where('device_fingerprint', $fingerprint)
            ->active()
            ->exists();

        if ($alreadyRegistered) {
            return $next($request);
        }

        // Count how many distinct active devices this user has for this client
        $activeDeviceCount = DeviceSession::where('user_id', $userId)
            ->where('client_id', $clientId)
            ->active()
            ->count();

        if ($activeDeviceCount < $client->max_devices_per_user) {
            return $next($request);
        }

        // Limit reached — apply the configured strategy
        $strategy = $client->device_limit_strategy ?? 'block';

        if ($strategy === 'evict_oldest') {
            $this->evictOldestDevice($userId, $clientId);
            return $next($request);
        }

        // Default: block
        return response()->json([
            'error'             => 'device_limit_exceeded',
            'error_description' => 'You have reached the maximum number of devices ('
                . $client->max_devices_per_user
                . ') allowed for this application. Please log out from another device first.',
        ], 403);
    }

    /**
     * Revoke the least recently active device session so the new device can log in.
     * Revoking the access token also invalidates any refresh tokens tied to it.
     */
    private function evictOldestDevice(int $userId, string $clientId): void
    {
        $oldest = DeviceSession::where('user_id', $userId)
            ->where('client_id', $clientId)
            ->active()
            ->orderBy('last_active_at') // least recently used first
            ->first();

        if (!$oldest || !$oldest->token_id) {
            return;
        }

        DB::table('oauth_access_tokens')
            ->where('id', $oldest->token_id)
            ->update(['revoked' => true]);

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $oldest->token_id)
            ->update(['revoked' => true]);
    }

    private function resolveUserId(Request $request, string $grantType): ?int
    {
        if ($grantType === 'password') {
            return User::where('email', $request->input('username'))->value('id');
        }

        if ($grantType === 'authorization_code') {
            // oauth_auth_codes.id IS the raw authorization code
            return DB::table('oauth_auth_codes')
                ->where('id', $request->input('code'))
                ->where('revoked', false)
                ->value('user_id');
        }

        return null;
    }
}
