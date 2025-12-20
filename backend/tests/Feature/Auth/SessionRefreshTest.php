<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Session Refresh Feature Tests
 *
 * @requirement AUTH-012 Create session activity tracker
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class SessionRefreshTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can refresh session.
     */
    public function test_authenticated_user_can_refresh_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'expires_at',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    /**
     * Test guest cannot refresh session.
     */
    public function test_guest_cannot_refresh_session(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh');

        $response->assertStatus(401);
    }

    /**
     * Test session refresh regenerates session ID.
     */
    public function test_session_refresh_regenerates_session_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $oldSessionId = session()->getId();

        $this->postJson('/api/v1/auth/refresh');

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /**
     * Test session refresh returns expiration time.
     */
    public function test_session_refresh_returns_expiration_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'expires_at',
                ],
            ]);

        $expiresAt = $response->json('data.expires_at');
        $this->assertNotEmpty($expiresAt);
    }

    /**
     * Test admin can refresh session.
     */
    public function test_admin_can_refresh_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test staff can refresh session.
     */
    public function test_staff_can_refresh_session(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test customer can refresh session.
     */
    public function test_customer_can_refresh_session(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test multiple session refreshes work correctly.
     */
    public function test_multiple_session_refreshes_work_correctly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        // First refresh
        $response1 = $this->postJson('/api/v1/auth/refresh');
        $response1->assertStatus(200);

        // Second refresh
        $response2 = $this->postJson('/api/v1/auth/refresh');
        $response2->assertStatus(200);

        // Third refresh
        $response3 = $this->postJson('/api/v1/auth/refresh');
        $response3->assertStatus(200);

        $this->assertAuthenticated();
    }
}
