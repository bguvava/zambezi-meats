<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Email Availability Check Tests
 *
 * @requirement AUTH-001 Registration with email validation
 * @requirement AUTH-020 Write comprehensive auth tests
 */
class EmailAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test email availability check returns available for new email.
     */
    public function test_email_availability_returns_available_for_new_email(): void
    {
        $response = $this->postJson('/api/v1/public/check-email', [
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => true,
                ],
            ]);
    }

    /**
     * Test email availability check returns unavailable for existing email.
     */
    public function test_email_availability_returns_unavailable_for_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/public/check-email', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => false,
                ],
            ]);
    }

    /**
     * Test email availability check validates email format.
     */
    public function test_email_availability_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/public/check-email', [
            'email' => 'invalid-email',
        ]);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test email availability check requires email.
     */
    public function test_email_availability_requires_email(): void
    {
        $response = $this->postJson('/api/v1/public/check-email', []);

        $response->assertApiValidationErrors(['email']);
    }

    /**
     * Test email availability check is case insensitive.
     */
    public function test_email_availability_check_is_case_insensitive(): void
    {
        User::factory()->create(['email' => 'Test@Example.com']);

        $response = $this->postJson('/api/v1/public/check-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'available' => false,
                ],
            ]);
    }

    /**
     * Test email availability can be checked by unauthenticated users.
     */
    public function test_email_availability_accessible_by_guests(): void
    {
        $response = $this->postJson('/api/v1/public/check-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
    }
}
