<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * User actions tests (status, password reset, activity).
 *
 * @requirement USER-006 Implement status change functionality
 * @requirement USER-007 Implement password reset by admin
 * @requirement USER-008 Create user activity history view
 * @requirement USER-010 Prevent admin self-deletion
 */
class UserActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->targetUser = User::factory()->customer()->create();
    }

    /**
     * Test admin can change user status.
     *
     * @requirement USER-006 Implement status change functionality
     */
    public function test_admin_can_change_user_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$this->targetUser->id}/status", [
                'status' => 'suspended',
            ]);

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'suspended',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->targetUser->id,
            'status' => 'suspended',
            'is_active' => false,
        ]);
    }

    /**
     * Test admin cannot change own status.
     *
     * @requirement USER-010 Prevent admin self-deletion
     */
    public function test_admin_cannot_change_own_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$this->admin->id}/status", [
                'status' => 'suspended',
            ]);

        $response->assertForbidden();

        // Status should remain active
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test status change validation.
     */
    public function test_status_change_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$this->targetUser->id}/status", [
                'status' => 'invalid_status',
            ]);

        // Expecting 422 Unprocessable Entity with validation errors
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'code',
                    'errors' => [
                        'status',
                    ],
                ],
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);
    }

    /**
     * Test status change is logged.
     *
     * @requirement USER-011 Log all user management actions
     */
    public function test_status_change_is_logged(): void
    {
        $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$this->targetUser->id}/status", [
                'status' => 'suspended',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'model_type' => User::class,
            'model_id' => $this->targetUser->id,
        ]);
    }

    /**
     * Test all valid status transitions.
     */
    public function test_all_status_transitions(): void
    {
        $statuses = ['active', 'suspended', 'inactive'];

        foreach ($statuses as $status) {
            $user = User::factory()->create();

            $response = $this->actingAs($this->admin)
                ->putJson("/api/v1/admin/users/{$user->id}/status", [
                    'status' => $status,
                ]);

            $response->assertOk();
            $this->assertEquals($status, $user->fresh()->status);
        }
    }

    /**
     * Test admin can reset user password.
     *
     * @requirement USER-007 Implement password reset by admin
     */
    public function test_admin_can_reset_user_password(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/admin/users/{$this->targetUser->id}/reset-password");

        $response->assertOk()
            ->assertJson([
                'message' => 'Password reset email sent successfully',
            ]);

        // Check password reset token was created
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->targetUser->email,
        ]);
    }

    /**
     * Test password reset is logged.
     *
     * @requirement USER-011 Log all user management actions
     */
    public function test_password_reset_is_logged(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->postJson("/api/v1/admin/users/{$this->targetUser->id}/reset-password");

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'Password reset requested by admin',
            'model_id' => $this->targetUser->id,
        ]);
    }

    /**
     * Test admin can view user activity history.
     *
     * @requirement USER-008 Create user activity history view
     */
    public function test_admin_can_view_user_activity_history(): void
    {
        // Create some activity logs for the target user
        ActivityLog::factory()->count(5)->create([
            'user_id' => $this->targetUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/users/{$this->targetUser->id}/activity");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'action',
                            'created_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test activity history is paginated.
     */
    public function test_activity_history_is_paginated(): void
    {
        // Create more than 20 activity logs
        ActivityLog::factory()->count(25)->create([
            'user_id' => $this->targetUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/users/{$this->targetUser->id}/activity");

        $response->assertOk();

        // Should have 20 per page
        $this->assertCount(20, $response->json('data.data'));
    }

    /**
     * Test user can view own activity history.
     */
    public function test_user_can_view_own_activity_history(): void
    {
        ActivityLog::factory()->count(3)->create([
            'user_id' => $this->targetUser->id,
        ]);

        $response = $this->actingAs($this->targetUser)
            ->getJson("/api/v1/users/{$this->targetUser->id}/activity");

        $response->assertOk();
    }

    /**
     * Test user cannot view other users' activity.
     */
    public function test_user_cannot_view_other_users_activity(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->targetUser)
            ->getJson("/api/v1/admin/users/{$otherUser->id}/activity");

        $response->assertForbidden();
    }

    /**
     * Test non-admin cannot change status.
     */
    public function test_non_admin_cannot_change_status(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->putJson("/api/v1/admin/users/{$this->targetUser->id}/status", [
                'status' => 'suspended',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test non-admin cannot reset password.
     */
    public function test_non_admin_cannot_reset_password(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->postJson("/api/v1/admin/users/{$this->targetUser->id}/reset-password");

        $response->assertForbidden();
    }
}
