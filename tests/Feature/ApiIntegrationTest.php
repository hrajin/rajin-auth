<?php

namespace Tests\Feature;

use App\Jobs\SendBackChannelLogout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeClient(array $overrides = []): array
    {
        $secret   = Str::random(40);
        $clientId = (string) Str::uuid();

        DB::table('oauth_clients')->insert(array_merge([
            'id'                     => $clientId,
            'name'                   => 'Test App',
            'secret'                 => Hash::make($secret),
            'redirect'               => 'http://localhost/callback',
            'personal_access_client' => false,
            'password_client'        => false,
            'revoked'                => false,
            'created_at'             => now(),
            'updated_at'             => now(),
        ], $overrides));

        return ['id' => $clientId, 'secret' => $secret];
    }

    private function makeUserWithToken(?string $clientId = null): array
    {
        $client = $clientId ? ['id' => $clientId] : $this->makeClient();

        $user    = User::factory()->create(['email_verified_at' => now()]);
        $tokenId = Str::random(40);

        DB::table('oauth_access_tokens')->insert([
            'id'         => $tokenId,
            'user_id'    => $user->id,
            'client_id'  => $client['id'],
            'name'       => null,
            'scopes'     => '["openid"]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        // Build a fake JWT: header.payload.sig
        // We only decode the payload in introspect — signature is never verified by us
        $header  = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode(['jti' => $tokenId, 'sub' => $user->id])), '+/', '-_'), '=');
        $jwt     = $header . '.' . $payload . '.fakesig';

        return ['user' => $user, 'tokenId' => $tokenId, 'jwt' => $jwt, 'clientId' => $client['id']];
    }

    // -------------------------------------------------------------------------
    // GET /.well-known/openid-configuration
    // -------------------------------------------------------------------------

    public function test_discovery_returns_200(): void
    {
        $this->getJson('/.well-known/openid-configuration')
            ->assertOk();
    }

    public function test_discovery_returns_required_oidc_fields(): void
    {
        $response = $this->getJson('/.well-known/openid-configuration')->assertOk();

        $response->assertJsonStructure([
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'userinfo_endpoint',
            'jwks_uri',
            'revocation_endpoint',
            'introspection_endpoint',
            'end_session_endpoint',
            'response_types_supported',
            'scopes_supported',
            'claims_supported',
            'code_challenge_methods_supported',
        ]);
    }

    public function test_discovery_endpoints_use_app_url(): void
    {
        $base = rtrim(config('app.url'), '/');

        $this->getJson('/.well-known/openid-configuration')
            ->assertJsonPath('issuer', $base)
            ->assertJsonPath('token_endpoint', $base . '/oauth/token')
            ->assertJsonPath('userinfo_endpoint', $base . '/api/userinfo')
            ->assertJsonPath('jwks_uri', $base . '/.well-known/jwks.json');
    }

    public function test_discovery_advertises_pkce_support(): void
    {
        $this->getJson('/.well-known/openid-configuration')
            ->assertJsonPath('code_challenge_methods_supported', ['S256']);
    }

    public function test_discovery_advertises_backchannel_logout(): void
    {
        $this->getJson('/.well-known/openid-configuration')
            ->assertJsonPath('backchannel_logout_supported', true);
    }

    // -------------------------------------------------------------------------
    // GET /.well-known/jwks.json
    // -------------------------------------------------------------------------

    public function test_jwks_returns_200(): void
    {
        $this->getJson('/.well-known/jwks.json')
            ->assertOk();
    }

    public function test_jwks_returns_rsa_key_structure(): void
    {
        $this->getJson('/.well-known/jwks.json')
            ->assertJsonStructure([
                'keys' => [
                    ['kty', 'use', 'alg', 'kid', 'n', 'e'],
                ],
            ]);
    }

    public function test_jwks_key_is_rsa_for_sig(): void
    {
        $response = $this->getJson('/.well-known/jwks.json')->assertOk();

        $key = $response->json('keys.0');

        $this->assertEquals('RSA', $key['kty']);
        $this->assertEquals('sig', $key['use']);
        $this->assertEquals('RS256', $key['alg']);
    }

    public function test_jwks_n_and_e_are_base64url_encoded(): void
    {
        $response = $this->getJson('/.well-known/jwks.json')->assertOk();

        $n = $response->json('keys.0.n');
        $e = $response->json('keys.0.e');

        // Base64url strings must not contain +, /, or = padding
        $this->assertStringNotContainsString('+', $n);
        $this->assertStringNotContainsString('/', $n);
        $this->assertStringNotContainsString('=', $n);
        $this->assertStringNotContainsString('+', $e);
        $this->assertStringNotContainsString('/', $e);
    }

    // -------------------------------------------------------------------------
    // GET /api/health
    // -------------------------------------------------------------------------

    public function test_health_returns_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'timestamp']);
    }

    public function test_health_is_accessible_without_authentication(): void
    {
        $this->getJson('/api/health')->assertOk();
    }

    // -------------------------------------------------------------------------
    // POST /api/token/introspect
    // -------------------------------------------------------------------------

    public function test_introspect_returns_active_for_valid_token(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => $client['secret'],
            'token'         => $data['jwt'],
        ])->assertOk()->assertJsonPath('active', true);
    }

    public function test_introspect_returns_user_claims_for_valid_token(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $response = $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => $client['secret'],
            'token'         => $data['jwt'],
        ])->assertOk();

        $response->assertJsonPath('sub', (string) $data['user']->id)
                 ->assertJsonPath('client_id', $client['id'])
                 ->assertJsonStructure(['active', 'sub', 'client_id', 'scope', 'exp', 'iat']);
    }

    public function test_introspect_returns_inactive_for_revoked_token(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        DB::table('oauth_access_tokens')
            ->where('id', $data['tokenId'])
            ->update(['revoked' => true]);

        $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => $client['secret'],
            'token'         => $data['jwt'],
        ])->assertOk()->assertJsonPath('active', false);
    }

    public function test_introspect_returns_inactive_for_expired_token(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        DB::table('oauth_access_tokens')
            ->where('id', $data['tokenId'])
            ->update(['expires_at' => now()->subMinute()]);

        $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => $client['secret'],
            'token'         => $data['jwt'],
        ])->assertOk()->assertJsonPath('active', false);
    }

    public function test_introspect_returns_inactive_for_garbage_token(): void
    {
        $client = $this->makeClient();

        $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => $client['secret'],
            'token'         => 'not.a.token',
        ])->assertOk()->assertJsonPath('active', false);
    }

    public function test_introspect_rejects_wrong_client_secret(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $this->postJson('/api/token/introspect', [
            'client_id'     => $client['id'],
            'client_secret' => 'wrong_secret',
            'token'         => $data['jwt'],
        ])->assertUnauthorized()->assertJsonPath('error', 'invalid_client');
    }

    public function test_introspect_rejects_missing_client_credentials(): void
    {
        $this->postJson('/api/token/introspect', [
            'token' => 'some.token.here',
        ])->assertUnauthorized();
    }

    public function test_introspect_supports_basic_auth_header(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $credentials = base64_encode($client['id'] . ':' . $client['secret']);

        $this->withHeaders(['Authorization' => 'Basic ' . $credentials])
             ->postJson('/api/token/introspect', ['token' => $data['jwt']])
             ->assertOk()
             ->assertJsonPath('active', true);
    }

    // -------------------------------------------------------------------------
    // POST /api/logout (single session)
    // -------------------------------------------------------------------------

    public function test_session_logout_requires_authentication(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_session_logout_revokes_only_current_token(): void
    {
        $client  = $this->makeClient();
        $dataA   = $this->makeUserWithToken($client['id']);
        $dataB   = $this->makeUserWithToken($client['id']); // same user, second token

        // Log out using token A
        $tokenA = \Laravel\Passport\Token::find($dataA['tokenId']);
        $dataA['user']->withAccessToken($tokenA);

        $this->actingAs($dataA['user'], 'api')
             ->postJson('/api/logout')
             ->assertOk()
             ->assertJsonPath('message', 'Logged out successfully.');

        // Token A revoked
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $dataA['tokenId'], 'revoked' => true]);

        // Token B (same user, different session) untouched
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $dataB['tokenId'], 'revoked' => false]);
    }

    public function test_session_logout_revokes_associated_refresh_token(): void
    {
        $client         = $this->makeClient();
        $data           = $this->makeUserWithToken($client['id']);
        $refreshTokenId = Str::random(40);

        DB::table('oauth_refresh_tokens')->insert([
            'id'              => $refreshTokenId,
            'access_token_id' => $data['tokenId'],
            'revoked'         => false,
            'expires_at'      => now()->addDays(30),
        ]);

        $token = \Laravel\Passport\Token::find($data['tokenId']);
        $data['user']->withAccessToken($token);

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout')
             ->assertOk();

        $this->assertDatabaseHas('oauth_refresh_tokens', ['id' => $refreshTokenId, 'revoked' => true]);
    }

    // -------------------------------------------------------------------------
    // POST /api/logout/all (global logout)
    // -------------------------------------------------------------------------

    public function test_global_logout_requires_authentication(): void
    {
        $this->postJson('/api/logout/all')->assertUnauthorized();
    }

    public function test_global_logout_revokes_all_user_tokens(): void
    {
        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id'      => $data['tokenId'],
            'revoked' => true,
        ]);
    }

    public function test_global_logout_revokes_associated_refresh_tokens(): void
    {
        $client         = $this->makeClient();
        $data           = $this->makeUserWithToken($client['id']);
        $refreshTokenId = Str::random(40);

        DB::table('oauth_refresh_tokens')->insert([
            'id'              => $refreshTokenId,
            'access_token_id' => $data['tokenId'],
            'revoked'         => false,
            'expires_at'      => now()->addDays(30),
        ]);

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk();

        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id'      => $refreshTokenId,
            'revoked' => true,
        ]);
    }

    public function test_global_logout_returns_message_and_clients_notified(): void
    {
        $data = $this->makeUserWithToken();

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk()
             ->assertJsonStructure(['message', 'clients_notified']);
    }

    public function test_global_logout_dispatches_back_channel_job_for_client_with_logout_uri(): void
    {
        Queue::fake();

        $client = $this->makeClient(['logout_uri' => 'https://app.example.com/auth/logout', 'logout_secret' => Str::random(40)]);
        $data   = $this->makeUserWithToken($client['id']);

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk()
             ->assertJsonPath('clients_notified', 1);

        Queue::assertPushed(SendBackChannelLogout::class, function ($job) use ($client, $data) {
            return $job->clientId === $client['id'] && $job->userId === $data['user']->id;
        });
    }

    public function test_global_logout_does_not_dispatch_job_for_client_without_logout_uri(): void
    {
        Queue::fake();

        $client = $this->makeClient();
        $data   = $this->makeUserWithToken($client['id']);

        $this->actingAs($data['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk()
             ->assertJsonPath('clients_notified', 0);

        Queue::assertNothingPushed();
    }

    public function test_global_logout_only_revokes_tokens_for_authenticated_user(): void
    {
        $client = $this->makeClient();
        $dataA  = $this->makeUserWithToken($client['id']);
        $dataB  = $this->makeUserWithToken($client['id']);

        $this->actingAs($dataA['user'], 'api')
             ->postJson('/api/logout/all')
             ->assertOk();

        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $dataA['tokenId'], 'revoked' => true]);
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $dataB['tokenId'], 'revoked' => false]);
    }
}
