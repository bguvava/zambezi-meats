<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+61412345678',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_user_can_get_their_profile(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '+61412345678',
                    'role' => $this->user->role,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'avatar',
                    'role',
                    'currency_preference',
                    'created_at',
                ],
            ]);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    }

    public function test_user_can_update_their_profile(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+61987654321',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'phone' => '+61987654321',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+61987654321',
        ]);
    }

    public function test_update_profile_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => '',
                'email' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_update_profile_validates_email_format(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_profile_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_keep_same_email_when_updating(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'John Updated',
                'email' => $this->user->email,
            ]);

        $response->assertOk();
    }

    public function test_update_profile_validates_phone_format(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => 'invalid-phone-format!@#',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_phone_field_is_optional(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => null,
            ]);

        $response->assertOk();
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        // Use create() instead of image() to avoid GD extension requirement
        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Avatar uploaded successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['avatar'],
            ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar);
        Storage::disk('public')->assertExists($this->user->avatar);
    }

    public function test_avatar_upload_validates_file_type(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_validates_file_size(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.jpg', 3000, 'image/jpeg'); // 3MB

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_requires_file(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_replaces_old_avatar(): void
    {
        Storage::fake('public');

        // Upload first avatar
        $file1 = UploadedFile::fake()->create('avatar1.jpg', 100, 'image/jpeg');
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $file1]);

        $this->user->refresh();
        $oldAvatar = $this->user->avatar;

        // Upload second avatar
        $file2 = UploadedFile::fake()->create('avatar2.jpg', 100, 'image/jpeg');
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $file2]);

        $this->user->refresh();

        // Old avatar should be deleted
        Storage::disk('public')->assertMissing($oldAvatar);
        // New avatar should exist
        Storage::disk('public')->assertExists($this->user->avatar);
    }

    public function test_user_can_delete_avatar(): void
    {
        Storage::fake('public');

        // Upload avatar first
        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $file]);

        $this->user->refresh();
        $avatarPath = $this->user->avatar;

        // Delete avatar
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/profile/avatar');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Avatar deleted successfully.',
            ]);

        $this->user->refresh();
        $this->assertNull($this->user->avatar);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_deleting_avatar_when_none_exists_succeeds(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/profile/avatar');

        $response->assertOk();
    }

    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);

        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    public function test_change_password_validates_current_password(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ]);
    }

    public function test_change_password_requires_all_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    public function test_change_password_requires_confirmation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_change_password_validates_minimum_length(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/profile/change-password', [
                'current_password' => 'password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_upload_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_change_password(): void
    {
        $response = $this->postJson('/api/v1/profile/change-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertUnauthorized();
    }
}
