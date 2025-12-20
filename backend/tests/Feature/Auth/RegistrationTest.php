<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registration Feature Tests
 *
 * @requirement AUTH-001 Create registration endpoint
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0412345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'phone',
                        'currency_preference',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'customer',
        ]);

        // Verify user is authenticated after registration
        $this->assertAuthenticated();
    }

    /**
     * Test registration without phone number (optional field).
     */
    public function test_user_can_register_without_phone(): void
    {
        $userData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'phone' => null,
        ]);
    }

    /**
     * Test registration fails with existing email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test registration fails with invalid email format.
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test registration fails with short password.
     */
    public function test_registration_fails_with_short_password(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['password']);
    }

    /**
     * Test registration fails with mismatched passwords.
     */
    public function test_registration_fails_with_mismatched_passwords(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['password']);
    }

    /**
     * Test registration fails without required fields.
     */
    public function test_registration_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertApiValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration fails with empty name.
     */
    public function test_registration_fails_with_empty_name(): void
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['name']);
    }

    /**
     * Test registered user has customer role by default.
     */
    public function test_registered_user_has_customer_role(): void
    {
        $userData = [
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->postJson('/api/v1/auth/register', $userData);

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertEquals('customer', $user->role);
    }

    /**
     * Test password is hashed in database.
     */
    public function test_password_is_hashed_in_database(): void
    {
        $plainPassword = 'password123';
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
        ];

        $this->postJson('/api/v1/auth/register', $userData);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(\Hash::check($plainPassword, $user->password));
    }

    /**
     * Test registration with invalid phone format.
     */
    public function test_registration_fails_with_invalid_phone(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => 'invalid-phone',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertApiValidationErrors(['phone']);
    }
}
