<?php

namespace Tests\Feature;

use App\Jobs\SendBackChannelLogout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeClient(array $overrides = []): string
    {
        $clientId = (string) Str::uuid();

        DB::table('oauth_clients')->insert(array_merge([
            'id'                     => $clientId,
            'name'                   => 'Test App',
            'secret'                 => Hash::make(Str::random(40)),
            'redirect'               => 'http://localhost/callback',
            'personal_access_client' => false,
            'password_client'        => false,
            'revoked'                => false,
            'created_at'             => now(),
            'updated_at'             => now(),
        ], $overrides));

        return $clientId;
    }

    private function makeToken(int $userId, string $clientId, array $overrides = []): string
    {
        $tokenId = Str::random(40);

        DB::table('oauth_access_tokens')->insert(array_merge([
            'id'         => $tokenId,
            'user_id'    => $userId,
            'client_id'  => $clientId,
            'name'       => null,
            'scopes'     => '["openid"]',
            'revoked'    => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addHour(),
        ], $overrides));

        return $tokenId;
    }

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_change_password_requires_authentication(): void
    {
        $this->postJson('/api/password/change')->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_current_password_is_required(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1')]);

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'new_password'              => 'NewPass1',
                 'new_password_confirmation' => 'NewPass1',
             ])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['current_password']);
    }

    public function test_new_password_is_required(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1')]);

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'current_password' => 'OldPass1',
             ])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['new_password']);
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1')]);

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'current_password'          => 'OldPass1',
                 'new_password'              => 'NewPass1',
                 'new_password_confirmation' => 'WrongConfirm',
             ])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['new_password']);
    }

    public function test_new_password_must_meet_complexity_requirements(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1')]);

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'current_password'          => 'OldPass1',
                 'new_password'              => 'weak',
                 'new_password_confirmation' => 'weak',
             ])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['new_password']);
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1')]);

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'current_password'          => 'WrongPassword1',
                 'new_password'              => 'NewPass1',
                 'new_password_confirmation' => 'NewPass1',
             ])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['current_password']);
    }

    // -------------------------------------------------------------------------
    // Google-only accounts
    // -------------------------------------------------------------------------

    public function test_google_only_user_cannot_use_change_password(): void
    {
        $user = User::factory()->create(['password' => null]); // Google-only

        $this->actingAs($user, 'api')
             ->postJson('/api/password/change', [
                 'current_password'          => 'anything',
                 'new_password'              => 'NewPass1',
                 'new_password_confirmation' => 'NewPass1',
             ])
             ->assertUnprocessable()
             ->assertJsonPath('error', 'no_password_set');
    }

    // -------------------------------------------------------------------------
    // Successful password change
    // -------------------------------------------------------------------------

    public function test_password_is_updated_in_database(): void
    {
        $user      = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId  = $this->makeClient();
        $currentId = $this->makeToken($user->id, $clientId);

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewPass1', $user->fresh()->password));
    }

    public function test_response_contains_message_and_sessions_revoked(): void
    {
        $user      = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId  = $this->makeClient();
        $currentId = $this->makeToken($user->id, $clientId);

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])
        ->assertOk()
        ->assertJsonStructure(['message', 'sessions_revoked']);
    }

    // -------------------------------------------------------------------------
    // Other sessions revoked
    // -------------------------------------------------------------------------

    public function test_other_tokens_are_revoked_after_password_change(): void
    {
        $user      = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId  = $this->makeClient();
        $currentId = $this->makeToken($user->id, $clientId);
        $otherId   = $this->makeToken($user->id, $clientId);

        // Simulate acting as the user with the current token
        $currentToken = \Laravel\Passport\Token::find($currentId);
        $this->actingAs($user, 'api');

        // Override the token() method to return our current token
        $user->withAccessToken($currentToken);

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])->assertOk();

        // Other token should be revoked
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id'      => $otherId,
            'revoked' => true,
        ]);
    }

    public function test_other_refresh_tokens_are_revoked_after_password_change(): void
    {
        $user           = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId       = $this->makeClient();
        $currentId      = $this->makeToken($user->id, $clientId);
        $otherId        = $this->makeToken($user->id, $clientId);
        $refreshTokenId = Str::random(40);

        DB::table('oauth_refresh_tokens')->insert([
            'id'              => $refreshTokenId,
            'access_token_id' => $otherId,
            'revoked'         => false,
            'expires_at'      => now()->addDays(30),
        ]);

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])->assertOk();

        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id'      => $refreshTokenId,
            'revoked' => true,
        ]);
    }

    public function test_current_token_is_not_revoked_after_password_change(): void
    {
        $user      = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId  = $this->makeClient();
        $currentId = $this->makeToken($user->id, $clientId);

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])->assertOk();

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id'      => $currentId,
            'revoked' => false,
        ]);
    }

    public function test_sessions_revoked_count_is_accurate(): void
    {
        $user     = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId = $this->makeClient();

        $currentId = $this->makeToken($user->id, $clientId);
        $this->makeToken($user->id, $clientId); // other 1
        $this->makeToken($user->id, $clientId); // other 2

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])
        ->assertOk()
        ->assertJsonPath('sessions_revoked', 2);
    }

    // -------------------------------------------------------------------------
    // Back-channel logout on password change
    // -------------------------------------------------------------------------

    public function test_back_channel_logout_dispatched_for_clients_with_logout_uri(): void
    {
        Queue::fake();

        $user     = User::factory()->create(['password' => Hash::make('OldPass1')]);
        $clientId = $this->makeClient([
            'logout_uri'    => 'https://app.example.com/auth/logout',
            'logout_secret' => Str::random(40),
        ]);

        $currentId = $this->makeToken($user->id, $clientId);
        $this->makeToken($user->id, $clientId); // other session

        $currentToken = \Laravel\Passport\Token::find($currentId);
        $user->withAccessToken($currentToken);
        $this->actingAs($user, 'api');

        $this->postJson('/api/password/change', [
            'current_password'          => 'OldPass1',
            'new_password'              => 'NewPass1',
            'new_password_confirmation' => 'NewPass1',
        ])->assertOk();

        Queue::assertPushed(SendBackChannelLogout::class);
    }
}
