<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/profile
    // -------------------------------------------------------------------------

    public function test_get_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_get_profile_returns_user_fields(): void
    {
        $user = User::factory()->create([
            'name'              => 'Test User',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'api')
             ->getJson('/api/profile')
             ->assertOk()
             ->assertJsonPath('name', 'Test User')
             ->assertJsonPath('email', $user->email)
             ->assertJsonPath('email_verified', true);
    }

    public function test_get_profile_returns_profile_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        UserProfile::create([
            'user_id'    => $user->id,
            'bio'        => 'Hello world',
            'gender'     => 'male',
            'city'       => 'Dhaka',
            'country'    => 'Bangladesh',
        ]);

        $this->actingAs($user, 'api')
             ->getJson('/api/profile')
             ->assertOk()
             ->assertJsonPath('profile.bio', 'Hello world')
             ->assertJsonPath('profile.gender', 'male')
             ->assertJsonPath('profile.city', 'Dhaka')
             ->assertJsonPath('profile.country', 'Bangladesh');
    }

    public function test_get_profile_returns_nulls_when_no_profile_exists(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->getJson('/api/profile')
             ->assertOk()
             ->assertJsonPath('profile.bio', null)
             ->assertJsonPath('profile.gender', null);
    }

    public function test_get_profile_returns_full_structure(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->getJson('/api/profile')
             ->assertOk()
             ->assertJsonStructure([
                 'id', 'name', 'email', 'email_verified',
                 'phone_number', 'phone_verified', 'avatar', 'updated_at',
                 'profile' => [
                     'date_of_birth', 'gender', 'bio', 'website',
                     'street_address', 'city', 'state', 'postal_code', 'country',
                 ],
             ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /api/profile
    // -------------------------------------------------------------------------

    public function test_update_profile_requires_authentication(): void
    {
        $this->patchJson('/api/profile')->assertUnauthorized();
    }

    public function test_can_update_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['name' => 'New Name'])
             ->assertOk()
             ->assertJsonPath('name', 'New Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_can_update_phone_number(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['phone_number' => '+8801711111111'])
             ->assertOk()
             ->assertJsonPath('phone_number', '+8801711111111');
    }

    public function test_changing_phone_number_resets_phone_verified(): void
    {
        $user = User::factory()->create(['phone_verified_at' => now()]);

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['phone_number' => '+8801722222222'])
             ->assertOk()
             ->assertJsonPath('phone_verified', false);

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_same_phone_number_does_not_reset_verification(): void
    {
        $user = User::factory()->create([
            'phone_number'     => '+8801711111111',
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['phone_number' => '+8801711111111'])
             ->assertOk()
             ->assertJsonPath('phone_verified', true);
    }

    public function test_can_update_profile_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', [
                 'bio'     => 'Learning Bangla vocabulary.',
                 'gender'  => 'male',
                 'city'    => 'Dhaka',
                 'country' => 'Bangladesh',
             ])
             ->assertOk()
             ->assertJsonPath('profile.bio', 'Learning Bangla vocabulary.')
             ->assertJsonPath('profile.gender', 'male')
             ->assertJsonPath('profile.city', 'Dhaka')
             ->assertJsonPath('profile.country', 'Bangladesh');
    }

    public function test_update_creates_profile_row_if_not_exists(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['bio' => 'Hello'])
             ->assertOk();

        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'bio' => 'Hello']);
    }

    public function test_partial_update_does_not_clear_other_fields(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        UserProfile::create([
            'user_id' => $user->id,
            'bio'     => 'Existing bio',
            'country' => 'Bangladesh',
        ]);

        // Only update city — name and bio should stay
        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['city' => 'Dhaka'])
             ->assertOk()
             ->assertJsonPath('name', 'Original Name')
             ->assertJsonPath('profile.bio', 'Existing bio')
             ->assertJsonPath('profile.city', 'Dhaka');
    }

    public function test_phone_number_must_be_unique(): void
    {
        User::factory()->create(['phone_number' => '+8801711111111']);
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['phone_number' => '+8801711111111'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_gender_must_be_valid_value(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['gender' => 'robot'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['gender']);
    }

    public function test_date_of_birth_must_be_in_the_past(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['date_of_birth' => now()->addYear()->format('Y-m-d')])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['date_of_birth']);
    }

    public function test_website_must_be_valid_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', ['website' => 'not-a-url'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['website']);
    }

    public function test_empty_patch_returns_unchanged_profile(): void
    {
        $user = User::factory()->create(['name' => 'Unchanged']);

        $this->actingAs($user, 'api')
             ->patchJson('/api/profile', [])
             ->assertOk()
             ->assertJsonPath('name', 'Unchanged');
    }

    // -------------------------------------------------------------------------
    // POST /api/profile/avatar
    // -------------------------------------------------------------------------

    public function test_upload_avatar_requires_authentication(): void
    {
        $this->postJson('/api/profile/avatar')->assertUnauthorized();
    }

    public function test_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $file])
             ->assertOk()
             ->assertJsonStructure(['message', 'avatar'])
             ->assertJsonPath('message', 'Avatar updated successfully.');
    }

    public function test_avatar_is_stored_on_disk(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.png', 200, 200);

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $file])
             ->assertOk();

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());
    }

    public function test_avatar_url_is_saved_in_database(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.png');

        $response = $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $file])
             ->assertOk();

        $this->assertNotNull($user->fresh()->avatar);
        $this->assertEquals($response->json('avatar'), $user->fresh()->avatar);
    }

    public function test_old_local_avatar_is_deleted_on_new_upload(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $old  = UploadedFile::fake()->image('old.jpg');
        $new  = UploadedFile::fake()->image('new.jpg');

        // Upload first avatar
        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $old]);

        $oldPath = 'avatars/' . $old->hashName();
        Storage::disk('public')->assertExists($oldPath);

        // Set the user's avatar to the old file path
        $user->update(['avatar' => Storage::disk('public')->url($oldPath)]);

        // Upload second avatar — old should be gone
        $this->actingAs($user->fresh(), 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $new])
             ->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_avatar_field_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $file])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_must_not_exceed_2mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('big.jpg')->size(3000);

        $this->actingAs($user, 'api')
             ->postJson('/api/profile/avatar', ['avatar' => $file])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['avatar']);
    }
}
