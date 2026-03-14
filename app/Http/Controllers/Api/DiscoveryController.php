<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DiscoveryController extends Controller
{
    public function configuration(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');

        return response()->json([
            'issuer'                                => $base,
            'authorization_endpoint'                => $base . '/oauth/authorize',
            'token_endpoint'                        => $base . '/oauth/token',
            'userinfo_endpoint'                     => $base . '/api/userinfo',
            'jwks_uri'                              => $base . '/.well-known/jwks.json',
            'revocation_endpoint'                   => $base . '/oauth/token/revoke',
            'introspection_endpoint'                => $base . '/api/token/introspect',
            'end_session_endpoint'                  => $base . '/api/logout',
            'response_types_supported'              => ['code'],
            'grant_types_supported'                 => ['authorization_code', 'refresh_token', 'client_credentials'],
            'subject_types_supported'               => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported'                      => ['openid', 'profile', 'email', 'phone', 'address', 'offline_access'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported'                      => [
                'sub', 'name', 'email', 'email_verified', 'picture',
                'phone_number', 'phone_number_verified', 'gender',
                'birthdate', 'website', 'bio', 'address', 'updated_at',
            ],
            'code_challenge_methods_supported'      => ['S256'],
            'backchannel_logout_supported'          => true,
        ]);
    }

    public function jwks(): JsonResponse
    {
        $publicKey = file_get_contents(storage_path('oauth-public.key'));

        $keyResource = openssl_pkey_get_public($publicKey);
        $keyDetails  = openssl_pkey_get_details($keyResource);

        $n = rtrim(strtr(base64_encode($keyDetails['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($keyDetails['rsa']['e']), '+/', '-_'), '=');

        return response()->json([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'kid' => substr(md5($publicKey), 0, 8),
                    'n'   => $n,
                    'e'   => $e,
                ],
            ],
        ]);
    }
}
