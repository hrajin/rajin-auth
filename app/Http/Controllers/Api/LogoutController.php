<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBackChannelLogout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;

class LogoutController extends Controller
{
    /**
     * Single session logout — revokes only the token used in this request.
     * The user stays logged in on all other devices and apps.
     */
    public function session(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $token->id)
            ->update(['revoked' => true]);

        $token->revoke();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Global logout — revokes all tokens across every device and app.
     * Fires back-channel logout to each registered client with a logout_uri.
     */
    public function global(Request $request): JsonResponse
    {
        $user = $request->user();

        $tokenIds = Token::where('user_id', $user->id)->pluck('id');

        $clientsToNotify = Token::where('user_id', $user->id)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->with('client')
            ->get()
            ->pluck('client')
            ->filter(fn($c) => $c && !$c->revoked && $c->logout_uri)
            ->unique('id')
            ->values();

        Token::where('user_id', $user->id)->update(['revoked' => true]);

        DB::table('oauth_refresh_tokens')
            ->whereIn('access_token_id', $tokenIds)
            ->update(['revoked' => true]);

        foreach ($clientsToNotify as $client) {
            SendBackChannelLogout::dispatch($client->id, $user->id);
        }

        return response()->json([
            'message'          => 'Logged out from all devices.',
            'clients_notified' => $clientsToNotify->count(),
        ]);
    }
}
