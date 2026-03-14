<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helper — build a fake Socialite Google user
    // -------------------------------------------------------------------------

    private function mockSocialiteUser(array $overrides = []): SocialiteUser
    {
        $socialUser = Mockery::mock(SocialiteUser::class);

        $socialUser->token        = $overrides['token']        ?? 'google-access-token';
        $socialUser->refreshToken = $overrides['refreshToken'] ?? 'google-refresh-token';
        $socialUser->expiresIn    = $overrides['expiresIn']    ?? 3600;

        $socialUser->shouldReceive('getId')     ->andReturn($overrides['id']     ?? 'google-uid-123');
        $socialUser->shouldReceive('getName')   ->andReturn($overrides['name']   ?? 'Test User');
        $socialUser->shouldReceive('getEmail')  ->andReturn($overrides['email']  ?? 'test@gmail.com');
        $socialUser->shouldReceive('getAvatar') ->andReturn($overrides['avatar'] ?? 'https://lh3.googleusercontent.com/photo.jpg');

        return $socialUser;
    }

    private function mockSocialiteDriver(SocialiteUser $socialUser): void
    {
        $driver = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/oauth'));
        $driver->shouldReceive('user')->andReturn($socialUser);

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($driver);

        $this->app->instance(SocialiteFactory::class, $factory);
    }

    // -------------------------------------------------------------------------
    // InvalidStateException handling
    // -------------------------------------------------------------------------

    public function test_invalid_state_exception_redirects_to_login_with_error(): void
    {
        $driver = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $driver->shouldReceive('user')->andThrow(new InvalidStateException());

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($driver);

        $this->app->instance(SocialiteFactory::class, $factory);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    // -------------------------------------------------------------------------
    // Redirect
    // -------------------------------------------------------------------------

    public function test_redirect_to_google_returns_redirect(): void
    {
        $driver = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/oauth'));

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($driver);

        $this->app->instance(SocialiteFactory::class, $factory);

        $response = $this->get('/auth/google/redirect');

        $this->assertTrue($response->isRedirect());
    }

    // -------------------------------------------------------------------------
    // Callback — new user, no existing account
    // -------------------------------------------------------------------------

    public function test_new_google_user_is_created_on_first_login(): void
    {
        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-001',
            'name'  => 'New User',
            'email' => 'newuser@gmail.com',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@gmail.com',
            'name'  => 'New User',
        ]);
    }

    public function test_social_account_is_created_on_first_login(): void
    {
        $socialUser = $this->mockSocialiteUser(['id' => 'google-uid-002']);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseHas('social_accounts', [
            'provider'    => 'google',
            'provider_id' => 'google-uid-002',
        ]);
    }

    public function test_google_login_stores_access_token(): void
    {
        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-003',
            'token' => 'google-access-token-xyz',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseHas('social_accounts', [
            'provider_id'  => 'google-uid-003',
            'access_token' => 'google-access-token-xyz',
        ]);
    }

    public function test_google_login_marks_email_as_verified(): void
    {
        $socialUser = $this->mockSocialiteUser(['email' => 'verified@gmail.com']);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $user = User::where('email', 'verified@gmail.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_login_sets_avatar(): void
    {
        $socialUser = $this->mockSocialiteUser([
            'email'  => 'avatar@gmail.com',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'email'  => 'avatar@gmail.com',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
        ]);
    }

    public function test_google_login_authenticates_the_user(): void
    {
        $socialUser = $this->mockSocialiteUser(['email' => 'auth@gmail.com']);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertAuthenticated();
    }

    public function test_google_login_redirects_to_dashboard(): void
    {
        $socialUser = $this->mockSocialiteUser();
        $this->mockSocialiteDriver($socialUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
    }

    // -------------------------------------------------------------------------
    // Callback — existing user, same Google account
    // -------------------------------------------------------------------------

    public function test_existing_google_user_is_logged_in_without_creating_duplicate(): void
    {
        $user = User::factory()->create(['email' => 'existing@gmail.com']);

        SocialAccount::create([
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => 'google-uid-existing',
            'access_token'=> 'old-token',
        ]);

        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-existing',
            'email' => 'existing@gmail.com',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertAuthenticatedAs($user);
    }

    public function test_returning_google_user_updates_access_token(): void
    {
        $user = User::factory()->create(['email' => 'refresh@gmail.com']);

        SocialAccount::create([
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => 'google-uid-refresh',
            'access_token'=> 'old-token',
        ]);

        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-refresh',
            'token' => 'new-fresh-token',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertDatabaseHas('social_accounts', [
            'provider_id'  => 'google-uid-refresh',
            'access_token' => 'new-fresh-token',
        ]);
    }

    // -------------------------------------------------------------------------
    // Callback — Google email matches existing email+password account (merge)
    // -------------------------------------------------------------------------

    public function test_google_login_merges_with_existing_email_password_account(): void
    {
        // User previously signed up with email+password
        $existing = User::factory()->create([
            'email'    => 'merge@gmail.com',
            'password' => bcrypt('secret'),
        ]);

        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-merge',
            'email' => 'merge@gmail.com', // same email
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        // No new user created — same account
        $this->assertDatabaseCount('users', 1);

        // Social account linked to existing user
        $this->assertDatabaseHas('social_accounts', [
            'user_id'     => $existing->id,
            'provider_id' => 'google-uid-merge',
        ]);

        $this->assertAuthenticatedAs($existing);
    }

    public function test_merged_account_gets_email_verified(): void
    {
        $existing = User::factory()->create([
            'email'             => 'unverified@gmail.com',
            'email_verified_at' => null,
        ]);

        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-unverified',
            'email' => 'unverified@gmail.com',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        $this->assertNotNull($existing->fresh()->email_verified_at);
    }

    // -------------------------------------------------------------------------
    // Callback — already verified account stays verified
    // -------------------------------------------------------------------------

    public function test_already_verified_user_stays_verified_after_google_login(): void
    {
        $verifiedAt = now()->subDays(5);

        $user = User::factory()->create([
            'email'             => 'already@gmail.com',
            'email_verified_at' => $verifiedAt,
        ]);

        SocialAccount::create([
            'user_id'     => $user->id,
            'provider'    => 'google',
            'provider_id' => 'google-uid-already',
            'access_token'=> 'token',
        ]);

        $socialUser = $this->mockSocialiteUser([
            'id'    => 'google-uid-already',
            'email' => 'already@gmail.com',
        ]);
        $this->mockSocialiteDriver($socialUser);

        $this->get('/auth/google/callback');

        // verified_at should not be changed
        $this->assertEquals(
            $verifiedAt->toDateTimeString(),
            $user->fresh()->email_verified_at->toDateTimeString()
        );
    }
}
