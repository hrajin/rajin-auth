<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBackChannelLogout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Token;

class ChangePasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        // Google-only accounts have no password — they must set one first via a different flow
        if (is_null($user->password)) {
            return response()->json([
                'message' => 'Your account uses Google sign-in and has no password. Use the set-password endpoint instead.',
                'error'   => 'no_password_set',
            ], 422);
        }

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'new_password'     => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update(['password' => Hash::make($request->new_password)]);

        // Revoke all tokens except the current one — forces re-login on other devices
        $currentTokenId = $user->token()->id;

        $otherTokenIds = Token::where('user_id', $user->id)
            ->where('id', '!=', $currentTokenId)
            ->pluck('id');

        if ($otherTokenIds->isNotEmpty()) {
            // Collect clients to notify before revoking
            $clientsToNotify = Token::where('user_id', $user->id)
                ->where('id', '!=', $currentTokenId)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->with('client')
                ->get()
                ->pluck('client')
                ->filter(fn($c) => $c && !$c->revoked && $c->logout_uri)
                ->unique('id')
                ->values();

            Token::whereIn('id', $otherTokenIds)->update(['revoked' => true]);

            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', $otherTokenIds)
                ->update(['revoked' => true]);

            foreach ($clientsToNotify as $client) {
                SendBackChannelLogout::dispatch($client->id, $user->id);
            }
        }

        return response()->json([
            'message'          => 'Password updated successfully.',
            'sessions_revoked' => $otherTokenIds->count(),
        ]);
    }
}
