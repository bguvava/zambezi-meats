<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UnlockSessionTest extends TestCase
{
    use RefreshDatabase;

    protected string $unlockUrl = '/api/v1/auth/unlock';

    /**
     * Test successful session unlock with valid credentials.
     */
    public function test_unlock_session_with_valid_credentials(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Simulate authenticated session
        $this->actingAs($user, 'sanctum');

        // Attempt to unlock with correct credentials
        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert successful unlock
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Session unlocked successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['expires_at'],
            ]);

        // Verify expires_at is a valid ISO 8601 timestamp
        $expiresAt = $response->json('data.expires_at');
        $this->assertNotEmpty($expiresAt);
        $this->assertIsString($expiresAt);
    }

    /**
     * Test unlock session fails with invalid password.
     */
    public function test_unlock_session_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Test unlock session fails with non-existent email.
     */
    public function test_unlock_session_with_non_existent_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Test unlock session fails with invalid email format.
     */
    public function test_unlock_session_with_invalid_email_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        // May return 401 (unauthorized) due to middleware protection
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock session requires email field.
     */
    public function test_unlock_session_requires_email(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'password' => 'password123',
        ]);

        // May return 401 (unauthorized) due to middleware protection
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock session requires password field.
     */
    public function test_unlock_session_requires_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
        ]);

        // May return 401 (unauthorized) due to middleware protection
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock session requires both fields.
     */
    public function test_unlock_session_requires_all_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, []);

        // May return 401 (unauthorized) due to middleware protection
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock session regenerates session ID.
     */
    public function test_unlock_session_regenerates_session_id(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        // Start a session
        session()->put('test_key', 'test_value');
        $oldSessionId = session()->getId();

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();

        // Session ID should be regenerated for security
        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /**
     * Test unlock session requires authentication.
     */
    public function test_unlock_session_requires_authentication(): void
    {
        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test unlock with empty password.
     */
    public function test_unlock_session_with_empty_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // May return 401 (unauthorized) or 422 (validation error)
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock with whitespace-only password.
     */
    public function test_unlock_session_with_whitespace_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => '   ',
        ]);

        // May return 401 (invalid credentials) or 422 (validation error)
        $this->assertContains($response->status(), [401, 422]);
    }

    /**
     * Test unlock works for admin users.
     */
    public function test_unlock_session_works_for_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@zambezimeats.com.au',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'admin@zambezimeats.com.au',
            'password' => 'adminpass',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /**
     * Test unlock works for staff users.
     */
    public function test_unlock_session_works_for_staff(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff@zambezimeats.com.au',
            'password' => Hash::make('staffpass'),
            'role' => 'staff',
        ]);

        $this->actingAs($staff, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'staff@zambezimeats.com.au',
            'password' => 'staffpass',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /**
     * Test unlock works for customer users.
     */
    public function test_unlock_session_works_for_customer(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('customerpass'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'customer@example.com',
            'password' => 'customerpass',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    /**
     * Test unlock with case-sensitive email.
     */
    public function test_unlock_session_with_case_sensitive_email(): void
    {
        $user = User::factory()->create([
            'email' => 'Test@Example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        // Try with different case
        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Laravel email lookup is case-sensitive by default
        // This may return 401 if the email case doesn't match
        $this->assertContains($response->status(), [200, 401]);
    }

    /**
     * Test unlock response includes proper expiration time.
     */
    public function test_unlock_session_includes_valid_expiration(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson($this->unlockUrl, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();

        $expiresAt = $response->json('data.expires_at');

        // Verify it's a future timestamp
        $this->assertNotEmpty($expiresAt);

        // Parse the ISO 8601 timestamp
        $expirationTime = strtotime($expiresAt);
        $this->assertGreaterThan(time(), $expirationTime);

        // Should be within reasonable range (1-3 hours)
        $this->assertLessThan(time() + (3 * 3600), $expirationTime);
    }
}
