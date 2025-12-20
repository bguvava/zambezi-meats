<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role-Based Middleware Tests
 *
 * @requirement AUTH-006 Implement role-based middleware
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access admin routes.
     */
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertSuccessful();
    }

    /**
     * Test staff cannot access admin-only routes.
     */
    public function test_staff_cannot_access_admin_only_routes(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have permission to access this resource.',
            ]);
    }

    /**
     * Test staff can access staff routes.
     */
    public function test_staff_can_access_staff_routes(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/staff/dashboard');

        $response->assertSuccessful();
    }

    /**
     * Test admin can access staff routes.
     */
    public function test_admin_can_access_staff_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/staff/dashboard');

        $response->assertSuccessful();
    }

    /**
     * Test customer cannot access staff routes.
     */
    public function test_customer_cannot_access_staff_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/staff/dashboard');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                ],
            ]);
    }

    /**
     * Test customer cannot access admin routes.
     */
    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access protected routes.
     */
    public function test_guest_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/v1/customer/dashboard');

        $response->assertStatus(401);
    }

    /**
     * Test customer can access customer routes.
     */
    public function test_customer_can_access_customer_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/customer/dashboard');

        $response->assertSuccessful();
    }

    /**
     * Test admin can access customer routes.
     */
    public function test_admin_can_access_customer_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/customer/dashboard');

        $response->assertSuccessful();
    }

    /**
     * Test staff cannot access customer dashboard.
     */
    public function test_staff_cannot_access_customer_dashboard(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/customer/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated request returns 401.
     */
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test role middleware returns correct error structure.
     */
    public function test_role_middleware_returns_correct_error_structure(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'code',
                    'required_roles',
                    'current_role',
                ],
            ]);
    }
}
