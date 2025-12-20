<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User Endpoint Feature Tests
 *
 * @requirement AUTH-009 Create current user endpoint
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can get their own data.
     */
    public function test_authenticated_user_can_get_own_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'customer',
            'phone' => '0412345678',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'phone',
                        'currency_preference',
                        'created_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'role' => 'customer',
                    ],
                ],
            ]);
    }

    /**
     * Test guest cannot access user endpoint.
     */
    public function test_guest_cannot_access_user_endpoint(): void
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test user endpoint does not expose password.
     */
    public function test_user_endpoint_does_not_expose_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/user');

        $response->assertJsonMissing(['password']);
        $response->assertJsonMissing(['remember_token']);
    }

    /**
     * Test admin can access user endpoint.
     */
    public function test_admin_can_access_user_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/auth/user');

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
     * Test staff can access user endpoint.
     */
    public function test_staff_can_access_user_endpoint(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/auth/user');

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
     * Test customer can access user endpoint.
     */
    public function test_customer_can_access_user_endpoint(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/auth/user');

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
     * Test user endpoint returns correct data structure.
     */
    public function test_user_endpoint_returns_correct_data_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/user');

        $response->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'status',
                    'is_active',
                    'currency_preference',
                    'created_at',
                ],
            ],
        ]);
    }
}
