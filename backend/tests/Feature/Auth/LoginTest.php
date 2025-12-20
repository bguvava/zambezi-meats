<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Login Feature Tests
 *
 * @requirement AUTH-002 Create login endpoint
 * @requirement AUTH-011 Implement "Remember Me" functionality
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertAuthenticated();
    }

    /**
     * Test login fails with invalid email.
     */
    public function test_login_fails_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertApiValidationErrors(['email']);

        $this->assertGuest();
    }

    /**
     * Test login fails with invalid password.
     */
    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertApiValidationErrors(['email']);

        $this->assertGuest();
    }

    /**
     * Test login fails without credentials.
     */
    public function test_login_fails_without_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertApiValidationErrors(['email', 'password']);
    }

    /**
     * Test login with remember me option.
     */
    public function test_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertAuthenticated();
    }

    /**
     * Test login updates last_login_at timestamp.
     */
    public function test_login_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'last_login_at' => null,
        ]);

        $this->assertNull($user->fresh()->last_login_at);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    /**
     * Test login regenerates session.
     */
    public function test_login_regenerates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $oldSessionId = session()->getId();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    /**
     * Test admin can login.
     */
    public function test_admin_can_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'admin',
                    ],
                ],
            ]);
    }

    /**
     * Test staff can login.
     */
    public function test_staff_can_login(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => bcrypt('password123'),
            'role' => 'staff',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'staff',
                    ],
                ],
            ]);
    }

    /**
     * Test customer can login.
     */
    public function test_customer_can_login(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'customer',
                    ],
                ],
            ]);
    }

    /**
     * Test login with empty email.
     */
    public function test_login_fails_with_empty_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test login with empty password.
     */
    public function test_login_fails_with_empty_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertApiValidationErrors(['password']);
    }
}
