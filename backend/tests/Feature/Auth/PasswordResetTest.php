<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Password Reset Feature Tests
 *
 * @requirement AUTH-005 Create password reset flow
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can request password reset link.
     */
    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
    }

    /**
     * Test password reset request fails with invalid email.
     */
    public function test_password_reset_request_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test password reset request fails with empty email.
     */
    public function test_password_reset_request_fails_with_empty_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => '',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test password reset request validates email format.
     */
    public function test_password_reset_request_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test user can reset password with valid token.
     */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ]);

        // Verify new password works
        $this->assertTrue(
            \Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    /**
     * Test password reset fails with invalid token.
     */
    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test password reset fails with mismatched passwords.
     */
    public function test_password_reset_fails_with_mismatched_passwords(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertApiValidationErrors(['password']);
    }

    /**
     * Test password reset fails with short password.
     */
    public function test_password_reset_fails_with_short_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertApiValidationErrors(['password']);
    }

    /**
     * Test password reset fails without required fields.
     */
    public function test_password_reset_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', []);

        $response->assertApiValidationErrors(['email', 'token', 'password']);
    }

    /**
     * Test password reset invalidates old remember tokens.
     */
    public function test_password_reset_invalidates_remember_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'remember_token' => 'old-token',
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertNotEquals('old-token', $user->fresh()->remember_token);
    }

    /**
     * Test password reset with wrong email for token.
     */
    public function test_password_reset_fails_with_wrong_email_for_token(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $token = Password::createToken($user1);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'user2@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test password reset can be done only once per token.
     */
    public function test_password_reset_token_can_only_be_used_once(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = Password::createToken($user);

        // First reset - should work
        $response1 = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response1->assertStatus(200);

        // Second reset with same token - should fail
        $response2 = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ]);
        $response2->assertStatus(422);
    }
}
