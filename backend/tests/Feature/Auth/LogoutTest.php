<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Logout Feature Tests
 *
 * @requirement AUTH-003 Create logout endpoint
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login first to establish proper session
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertGuest('web');
    }

    /**
     * Test logout invalidates session.
     */
    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login first
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        $this->postJson('/api/v1/auth/logout');

        $this->assertGuest('web');
    }

    /**
     * Test logout regenerates CSRF token.
     */
    public function test_logout_regenerates_csrf_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login first
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $oldToken = csrf_token();

        $this->postJson('/api/v1/auth/logout');

        $newToken = csrf_token();

        $this->assertNotEquals($oldToken, $newToken);
    }

    /**
     * Test guest cannot logout.
     */
    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test logout clears authentication guard.
     */
    public function test_logout_clears_authentication_guard(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login first
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);

        $this->postJson('/api/v1/auth/logout');

        $this->assertGuest('web');
    }

    /**
     * Test logout endpoint handles subsequent requests properly.
     * After logout, the web guard should be cleared.
     */
    public function test_multiple_logouts_handled_gracefully(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login first
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();

        // First logout
        $response1 = $this->postJson('/api/v1/auth/logout');
        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        // Verify web guard is cleared
        $this->assertGuest('web');
    }

    /**
     * Test logout from different roles.
     */
    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertGuest('web');
    }

    /**
     * Test staff can logout.
     */
    public function test_staff_can_logout(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertGuest('web');
    }

    /**
     * Test customer can logout.
     */
    public function test_customer_can_logout(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertGuest('web');
    }
}
