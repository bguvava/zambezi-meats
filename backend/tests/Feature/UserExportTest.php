<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User export to PDF tests.
 *
 * @requirement USER-009 Implement user export to PDF
 */
class UserExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Test admin can export users to PDF.
     */
    public function test_admin_can_export_users_to_pdf(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export with search filter.
     */
    public function test_export_with_search_filter(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export?search=John');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export with role filter.
     */
    public function test_export_with_role_filter(): void
    {
        User::factory()->customer()->count(3)->create();
        User::factory()->staff()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export?role=customer');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export with status filter.
     */
    public function test_export_with_status_filter(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->suspended()->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export?status=suspended');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export with multiple filters.
     */
    public function test_export_with_multiple_filters(): void
    {
        User::factory()->customer()->create(['status' => 'active', 'name' => 'Test User']);

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export?role=customer&status=active&search=Test');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export with no results.
     */
    public function test_export_with_no_results(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export?search=nonexistent');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test export is logged.
     *
     * @requirement USER-011 Log all user management actions
     */
    public function test_export_is_logged(): void
    {
        $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'Users exported to PDF',
        ]);
    }

    /**
     * Test non-admin cannot export.
     */
    public function test_non_admin_cannot_export(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->get('/api/v1/admin/users/export');

        $response->assertForbidden();
    }

    /**
     * Test filename contains date.
     */
    public function test_filename_contains_date(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/api/v1/admin/users/export');

        $response->assertOk();

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('zambezi-meats-users-', $disposition);
        $this->assertStringContainsString(now()->format('Y-m-d'), $disposition);
    }
}
