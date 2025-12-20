<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User management controller tests.
 *
 * @requirement USER-014 Create users API endpoints
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->admin()->create();
        $this->staff = User::factory()->staff()->create();
        $this->customer = User::factory()->customer()->create();
    }

    /**
     * Test admin can list all users.
     *
     * @requirement USER-001 Create users listing page
     */
    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'status',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Test non-admin cannot list users.
     */
    public function test_non_admin_cannot_list_users(): void
    {
        $response = $this->actingAs($this->customer)->getJson('/api/v1/admin/users');

        $response->assertForbidden();
    }

    /**
     * Test user search functionality.
     *
     * @requirement USER-002 Implement user search
     */
    public function test_can_search_users_by_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?search=John');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'John Doe']);
    }

    /**
     * Test user search by email.
     *
     * @requirement USER-002 Implement user search
     */
    public function test_can_search_users_by_email(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?search=john@example');

        $response->assertOk()
            ->assertJsonFragment(['email' => 'john@example.com']);
    }

    /**
     * Test filter by role.
     *
     * @requirement USER-003 Implement user filters (max 3)
     */
    public function test_can_filter_users_by_role(): void
    {
        User::factory()->customer()->count(3)->create();
        User::factory()->staff()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?role=customer');

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $user) {
            $this->assertEquals('customer', $user['role']);
        }
    }

    /**
     * Test filter by status.
     *
     * @requirement USER-003 Implement user filters (max 3)
     */
    public function test_can_filter_users_by_status(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->suspended()->create();
        User::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?status=suspended');

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $user) {
            $this->assertEquals('suspended', $user['status']);
        }
    }

    /**
     * Test create new user.
     *
     * @requirement USER-004 Create new user form
     */
    public function test_admin_can_create_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'phone' => '+61 2 1234 5678',
            'role' => 'customer',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/users', $userData);

        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'New User',
                'email' => 'newuser@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'status' => 'active',
        ]);
    }

    /**
     * Test create user validation.
     */
    public function test_create_user_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/users', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'code',
                    'errors' => [
                        'name',
                        'email',
                        'password',
                        'role',
                    ],
                ],
            ]);
    }

    /**
     * Test create user with duplicate email.
     */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/users', [
                'name' => 'New User',
                'email' => $existingUser->email,
                'password' => 'Password123!',
                'role' => 'customer',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'code',
                    'errors' => [
                        'email',
                    ],
                ],
            ]);
    }

    /**
     * Test show user details.
     */
    public function test_admin_can_view_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/users/{$user->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    /**
     * Test update user.
     *
     * @requirement USER-005 Create edit user form
     */
    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test update user validation.
     */
    public function test_update_user_validation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$user->id}", [
                'email' => $otherUser->email,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'code',
                    'errors' => [
                        'email',
                    ],
                ],
            ]);
    }

    /**
     * Test pagination.
     */
    public function test_users_are_paginated(): void
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);

        // Should have 15 per page as per requirement USER-001
        $this->assertCount(15, $response->json('data'));
    }
}
