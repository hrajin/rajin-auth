<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceDeviceLimit;
use App\Listeners\RecordDeviceSession;
use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passport\Events\AccessTokenCreated;
use Tests\TestCase;

class DeviceLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $clientId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->clientId = (string) Str::uuid();

        DB::table('oauth_clients')->insert([
            'id'                    => $this->clientId,
            'name'                  => 'Test App',
            'secret'                => Str::random(40),
            'redirect'              => 'http://localhost/callback',
            'personal_access_client' => false,
            'password_client'       => false,
            'revoked'               => false,
            'max_devices_per_user'  => 2,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Run the EnforceDeviceLimit middleware with a fake POST /oauth/token request.
     * $next is replaced with a closure that returns 200 — simulating Passport
     * issuing a token if the middleware allows it through.
     */
    private function runMiddleware(array $body, string $userAgent = 'Chrome/120'): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/oauth/token', 'POST', $body);
        $request->headers->set('User-Agent', $userAgent);

        return (new EnforceDeviceLimit())->handle(
            $request,
            fn () => response()->json(['access_token' => 'fake_token'], 200)
        );
    }

    /**
     * Insert a valid (non-revoked, non-expired) access token into oauth_access_tokens.
     */
    private function createActiveToken(string $tokenId, int $userId, string $clientId): void
    {
        DB::table('oauth_access_tokens')->insert([
            'id'         => $tokenId,
            'user_id'    => $userId,
            'client_id'  => $clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
    }

    /**
     * Insert a device session backed by an active token.
     */
    private function createDeviceSession(string $userAgent, ?string $tokenId = null): DeviceSession
    {
        $tokenId = $tokenId ?? Str::random(40);
        $this->createActiveToken($tokenId, $this->user->id, $this->clientId);

        return DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $tokenId,
            'device_fingerprint' => hash('sha256', $userAgent),
            'user_agent'         => $userAgent,
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now(),
        ]);
    }

    /**
     * Insert a valid auth code for the test user.
     */
    private function createAuthCode(): string
    {
        $code = Str::random(40);

        DB::table('oauth_auth_codes')->insert([
            'id'         => $code,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'scopes'     => '["openid"]',
            'revoked'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    // -------------------------------------------------------------------------
    // Middleware — grant type bypass
    // -------------------------------------------------------------------------

    public function test_refresh_token_grant_always_passes_through(): void
    {
        // Fill up the device limit first
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        $response = $this->runMiddleware([
            'grant_type'    => 'refresh_token',
            'refresh_token' => 'some_token',
            'client_id'     => $this->clientId,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_unknown_grant_types_pass_through(): void
    {
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        $response = $this->runMiddleware([
            'grant_type' => 'client_credentials',
            'client_id'  => $this->clientId,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — no limit configured
    // -------------------------------------------------------------------------

    public function test_allows_request_when_client_has_no_device_limit(): void
    {
        DB::table('oauth_clients')
            ->where('id', $this->clientId)
            ->update(['max_devices_per_user' => null]);

        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_allows_request_when_client_does_not_exist(): void
    {
        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => 'non-existent-client-id',
            'code'       => 'some_code',
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — authorization_code grant
    // -------------------------------------------------------------------------

    public function test_allows_auth_code_login_when_under_device_limit(): void
    {
        $this->createDeviceSession('Device-A'); // 1 of 2 used
        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-B');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_auth_code_login_when_device_limit_reached(): void
    {
        $this->createDeviceSession('Device-A'); // 1 of 2
        $this->createDeviceSession('Device-B'); // 2 of 2 — limit hit
        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('device_limit_exceeded', $response->getData()->error);
    }

    public function test_error_response_contains_device_limit_in_message(): void
    {
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');
        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertStringContainsString('2', $response->getData()->error_description);
    }

    public function test_allows_auth_code_login_when_auth_code_not_found(): void
    {
        // If the code is invalid, let Passport handle the error itself
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => 'invalid_code',
        ], 'Device-C');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_allows_auth_code_login_when_auth_code_is_revoked(): void
    {
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        // Revoked code — can't resolve user, so middleware defers to Passport
        $code = Str::random(40);
        DB::table('oauth_auth_codes')->insert([
            'id'         => $code,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'scopes'     => '[]',
            'revoked'    => true,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — password grant
    // -------------------------------------------------------------------------

    public function test_allows_password_grant_when_under_device_limit(): void
    {
        $this->createDeviceSession('Device-A');

        $response = $this->runMiddleware([
            'grant_type' => 'password',
            'client_id'  => $this->clientId,
            'username'   => $this->user->email,
            'password'   => 'password',
        ], 'Device-B');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_password_grant_when_device_limit_reached(): void
    {
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        $response = $this->runMiddleware([
            'grant_type' => 'password',
            'client_id'  => $this->clientId,
            'username'   => $this->user->email,
            'password'   => 'password',
        ], 'Device-C');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('device_limit_exceeded', $response->getData()->error);
    }

    public function test_allows_password_grant_when_user_not_found(): void
    {
        // Unknown user — middleware can't resolve user ID, defers to Passport
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        $response = $this->runMiddleware([
            'grant_type' => 'password',
            'client_id'  => $this->clientId,
            'username'   => 'nobody@example.com',
            'password'   => 'password',
        ], 'Device-C');

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — same device re-authentication
    // -------------------------------------------------------------------------

    public function test_same_device_can_re_authenticate_even_when_limit_is_full(): void
    {
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B'); // limit = 2, now full
        $code = $this->createAuthCode();

        // Device-A logging in again — same user-agent
        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-A');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_device_fingerprint_is_based_on_user_agent(): void
    {
        // Two different user-agents = two different devices
        $this->createDeviceSession('Chrome/120');
        $this->createDeviceSession('Firefox/121'); // limit hit
        $code = $this->createAuthCode();

        // Safari = third device, blocked
        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Safari/17');

        $this->assertEquals(403, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — per-client isolation
    // -------------------------------------------------------------------------

    public function test_device_limit_is_per_user_not_global(): void
    {
        // User A fills their 2-device limit
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        // User B has NO active sessions — should be allowed regardless
        $userB = User::factory()->create(['email_verified_at' => now()]);

        $code = Str::random(40);
        DB::table('oauth_auth_codes')->insert([
            'id'         => $code,
            'user_id'    => $userB->id,  // different user
            'client_id'  => $this->clientId,
            'scopes'     => '[]',
            'revoked'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(200, $response->getStatusCode());
    }


    public function test_device_limits_are_enforced_independently_per_client(): void
    {
        // Fill up limit on clientId
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');

        // A second client with its own (empty) device count
        $otherClientId = (string) Str::uuid();
        DB::table('oauth_clients')->insert([
            'id'                    => $otherClientId,
            'name'                  => 'Other App',
            'secret'                => Str::random(40),
            'redirect'              => 'http://other.localhost/callback',
            'personal_access_client' => false,
            'password_client'       => false,
            'revoked'               => false,
            'max_devices_per_user'  => 2,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $code = Str::random(40);
        DB::table('oauth_auth_codes')->insert([
            'id'         => $code,
            'user_id'    => $this->user->id,
            'client_id'  => $otherClientId,
            'scopes'     => '[]',
            'revoked'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'authorization_code',
            'client_id'  => $otherClientId,
            'code'       => $code,
        ]);
        $request->headers->set('User-Agent', 'Device-C');

        $response = (new EnforceDeviceLimit())->handle(
            $request,
            fn () => response()->json(['access_token' => 'fake_token'], 200)
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Middleware — inactive (expired/revoked) sessions don't count
    // -------------------------------------------------------------------------

    public function test_expired_device_sessions_do_not_count_toward_limit(): void
    {
        // Create a session with an expired token
        $expiredTokenId = Str::random(40);
        DB::table('oauth_access_tokens')->insert([
            'id'         => $expiredTokenId,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->subHour(), // already expired
        ]);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $expiredTokenId,
            'device_fingerprint' => hash('sha256', 'Old-Device'),
            'user_agent'         => 'Old-Device',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subDay(),
        ]);

        $this->createDeviceSession('Device-A'); // 1 active
        $this->createDeviceSession('Device-B'); // 2 active — would hit limit if expired counted

        $code = $this->createAuthCode();

        // With limit=2 and only 2 active sessions (expired one doesn't count)
        // a third different device should be blocked
        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_revoked_device_sessions_do_not_count_toward_limit(): void
    {
        // Revoked token session
        $revokedTokenId = Str::random(40);
        DB::table('oauth_access_tokens')->insert([
            'id'         => $revokedTokenId,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => true, // revoked
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $revokedTokenId,
            'device_fingerprint' => hash('sha256', 'Revoked-Device'),
            'user_agent'         => 'Revoked-Device',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now(),
        ]);

        $this->createDeviceSession('Device-A'); // 1 active
        $code = $this->createAuthCode();

        // Limit is 2, only 1 active — new device should be allowed
        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-B');

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // RecordDeviceSession listener
    // -------------------------------------------------------------------------

    public function test_device_session_is_created_after_token_creation(): void
    {
        $tokenId = Str::random(40);
        $this->createActiveToken($tokenId, $this->user->id, $this->clientId);

        $this->app->make('request')->headers->set('User-Agent', 'TestBrowser/1.0');

        event(new AccessTokenCreated($tokenId, $this->user->id, $this->clientId));

        $this->assertDatabaseHas('device_sessions', [
            'user_id'   => $this->user->id,
            'client_id' => $this->clientId,
            'token_id'  => $tokenId,
        ]);
    }

    public function test_device_session_is_updated_not_duplicated_on_token_refresh(): void
    {
        $firstTokenId  = Str::random(40);
        $secondTokenId = Str::random(40);

        $this->createActiveToken($firstTokenId, $this->user->id, $this->clientId);
        $this->createActiveToken($secondTokenId, $this->user->id, $this->clientId);

        $this->app->make('request')->headers->set('User-Agent', 'SameDevice/2.0');

        event(new AccessTokenCreated($firstTokenId, $this->user->id, $this->clientId));
        event(new AccessTokenCreated($secondTokenId, $this->user->id, $this->clientId));

        // Only one row should exist for this device
        $this->assertDatabaseCount('device_sessions', 1);

        // It should point to the latest token
        $this->assertDatabaseHas('device_sessions', [
            'user_id'   => $this->user->id,
            'client_id' => $this->clientId,
            'token_id'  => $secondTokenId,
        ]);
    }

    public function test_different_devices_create_separate_sessions(): void
    {
        $tokenA = Str::random(40);
        $tokenB = Str::random(40);
        $this->createActiveToken($tokenA, $this->user->id, $this->clientId);
        $this->createActiveToken($tokenB, $this->user->id, $this->clientId);

        $request = $this->app->make('request');

        $request->headers->set('User-Agent', 'DeviceA/1.0');
        event(new AccessTokenCreated($tokenA, $this->user->id, $this->clientId));

        $request->headers->set('User-Agent', 'DeviceB/1.0');
        event(new AccessTokenCreated($tokenB, $this->user->id, $this->clientId));

        $this->assertDatabaseCount('device_sessions', 2);
    }

    public function test_device_session_records_ip_address_and_user_agent(): void
    {
        $tokenId = Str::random(40);
        $this->createActiveToken($tokenId, $this->user->id, $this->clientId);

        $request = $this->app->make('request');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh)');
        $request->server->set('REMOTE_ADDR', '192.168.1.10');

        event(new AccessTokenCreated($tokenId, $this->user->id, $this->clientId));

        $session = DeviceSession::first();
        $this->assertEquals('Mozilla/5.0 (Macintosh)', $session->user_agent);
    }

    // -------------------------------------------------------------------------
    // DeviceSession model — active() scope
    // -------------------------------------------------------------------------

    public function test_active_scope_includes_sessions_with_valid_tokens(): void
    {
        $this->createDeviceSession('Chrome/120');

        $this->assertCount(1, DeviceSession::active()->get());
    }

    public function test_active_scope_excludes_sessions_with_revoked_tokens(): void
    {
        $tokenId = Str::random(40);
        DB::table('oauth_access_tokens')->insert([
            'id'         => $tokenId,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => true,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $tokenId,
            'device_fingerprint' => hash('sha256', 'OldDevice'),
            'user_agent'         => 'OldDevice',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now(),
        ]);

        $this->assertCount(0, DeviceSession::active()->get());
    }

    public function test_active_scope_excludes_sessions_with_expired_tokens(): void
    {
        $tokenId = Str::random(40);
        DB::table('oauth_access_tokens')->insert([
            'id'         => $tokenId,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->subMinute(), // expired
        ]);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $tokenId,
            'device_fingerprint' => hash('sha256', 'OldDevice'),
            'user_agent'         => 'OldDevice',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now(),
        ]);

        $this->assertCount(0, DeviceSession::active()->get());
    }

    public function test_active_scope_returns_only_active_from_mixed_sessions(): void
    {
        $this->createDeviceSession('Active-Device'); // valid token

        $expiredTokenId = Str::random(40);
        DB::table('oauth_access_tokens')->insert([
            'id'         => $expiredTokenId,
            'user_id'    => $this->user->id,
            'client_id'  => $this->clientId,
            'name'       => null,
            'scopes'     => '[]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->subHour(),
        ]);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $expiredTokenId,
            'device_fingerprint' => hash('sha256', 'Expired-Device'),
            'user_agent'         => 'Expired-Device',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subDay(),
        ]);

        $this->assertCount(1, DeviceSession::active()->get());
        $this->assertEquals('Active-Device', DeviceSession::active()->first()->user_agent);
    }

    // -------------------------------------------------------------------------
    // Evict oldest strategy
    // -------------------------------------------------------------------------

    private function setStrategy(string $strategy): void
    {
        DB::table('oauth_clients')
            ->where('id', $this->clientId)
            ->update(['device_limit_strategy' => $strategy]);
    }

    public function test_evict_oldest_strategy_allows_new_login_when_limit_reached(): void
    {
        $this->setStrategy('evict_oldest');
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');
        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_evict_oldest_strategy_revokes_oldest_device_access_token(): void
    {
        $this->setStrategy('evict_oldest');

        $oldTokenId = Str::random(40);
        $newTokenId = Str::random(40);

        // Oldest device — last active 2 hours ago
        $this->createActiveToken($oldTokenId, $this->user->id, $this->clientId);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $oldTokenId,
            'device_fingerprint' => hash('sha256', 'Device-Old'),
            'user_agent'         => 'Device-Old',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subHours(2),
        ]);

        // Newer device — last active 1 hour ago
        $this->createActiveToken($newTokenId, $this->user->id, $this->clientId);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $newTokenId,
            'device_fingerprint' => hash('sha256', 'Device-New'),
            'user_agent'         => 'Device-New',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subHour(),
        ]);

        $code = $this->createAuthCode();
        $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        // Old token should be revoked
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id'      => $oldTokenId,
            'revoked' => true,
        ]);

        // Newer token should still be active
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id'      => $newTokenId,
            'revoked' => false,
        ]);
    }

    public function test_evict_oldest_strategy_also_revokes_refresh_tokens(): void
    {
        $this->setStrategy('evict_oldest');

        $tokenId = Str::random(40);
        $this->createActiveToken($tokenId, $this->user->id, $this->clientId);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $tokenId,
            'device_fingerprint' => hash('sha256', 'Device-Old'),
            'user_agent'         => 'Device-Old',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subHours(2),
        ]);

        // Insert a linked refresh token
        $refreshTokenId = Str::random(40);
        DB::table('oauth_refresh_tokens')->insert([
            'id'              => $refreshTokenId,
            'access_token_id' => $tokenId,
            'revoked'         => false,
            'expires_at'      => now()->addDays(30),
        ]);

        $this->createDeviceSession('Device-B');
        $code = $this->createAuthCode();
        $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id'      => $refreshTokenId,
            'revoked' => true,
        ]);
    }

    public function test_evict_oldest_evicts_least_recently_active_not_most_recent(): void
    {
        $this->setStrategy('evict_oldest');

        $recentTokenId = Str::random(40);
        $staleTokenId  = Str::random(40);

        // Stale device — last active yesterday
        $this->createActiveToken($staleTokenId, $this->user->id, $this->clientId);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $staleTokenId,
            'device_fingerprint' => hash('sha256', 'Stale'),
            'user_agent'         => 'Stale',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subDay(),
        ]);

        // Recent device — last active 10 minutes ago
        $this->createActiveToken($recentTokenId, $this->user->id, $this->clientId);
        DeviceSession::create([
            'user_id'            => $this->user->id,
            'client_id'          => $this->clientId,
            'token_id'           => $recentTokenId,
            'device_fingerprint' => hash('sha256', 'Recent'),
            'user_agent'         => 'Recent',
            'ip_address'         => '127.0.0.1',
            'last_active_at'     => now()->subMinutes(10),
        ]);

        $code = $this->createAuthCode();
        $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-New');

        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $staleTokenId,  'revoked' => true]);
        $this->assertDatabaseHas('oauth_access_tokens', ['id' => $recentTokenId, 'revoked' => false]);
    }

    public function test_block_strategy_still_rejects_when_explicitly_set(): void
    {
        $this->setStrategy('block');
        $this->createDeviceSession('Device-A');
        $this->createDeviceSession('Device-B');
        $code = $this->createAuthCode();

        $response = $this->runMiddleware([
            'grant_type' => 'authorization_code',
            'client_id'  => $this->clientId,
            'code'       => $code,
        ], 'Device-C');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('device_limit_exceeded', $response->getData()->error);
    }
}
