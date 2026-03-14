<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

class TokenIntrospectController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Resolve client credentials — support both Basic auth and POST body
        [$clientId, $clientSecret] = $this->resolveClientCredentials($request);

        if (!$clientId || !$clientSecret) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $client = Client::find($clientId);

        if (!$client || $client->revoked || !Hash::check($clientSecret, $client->secret)) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $tokenValue = $request->input('token');

        if (!$tokenValue) {
            return response()->json(['active' => false]);
        }

        $token = $this->findToken($tokenValue);

        if (!$token || $token->revoked || $token->expires_at->isPast()) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active'    => true,
            'sub'       => (string) $token->user_id,
            'client_id' => $token->client_id,
            'scope'     => implode(' ', $token->scopes),
            'exp'       => $token->expires_at->timestamp,
            'iat'       => $token->created_at->timestamp,
        ]);
    }

    private function resolveClientCredentials(Request $request): array
    {
        $authHeader = $request->header('Authorization', '');

        if (str_starts_with($authHeader, 'Basic ')) {
            $decoded = base64_decode(substr($authHeader, 6));
            if (str_contains($decoded, ':')) {
                return explode(':', $decoded, 2);
            }
        }

        return [
            $request->input('client_id'),
            $request->input('client_secret'),
        ];
    }

    private function findToken(string $tokenValue): ?Token
    {
        $parts = explode('.', $tokenValue);

        if (count($parts) !== 3) {
            return null;
        }

        $padding = strlen($parts[1]) % 4;
        $padded  = $padding ? $parts[1] . str_repeat('=', 4 - $padding) : $parts[1];
        $payload = json_decode(base64_decode(strtr($padded, '-_', '+/')), true);

        if (!$payload || empty($payload['jti'])) {
            return null;
        }

        return Token::find($payload['jti']);
    }
}
