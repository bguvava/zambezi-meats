<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\E2E;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-End Tests for Subscription Management
 *
 * Tests the complete flow of managing service subscriptions:
 * 1. View available services for subscription
 * 2. Create subscription (to be implemented)
 * 3. View active subscriptions
 * 4. View subscription details
 * 5. Cancel subscription (to be implemented)
 *
 * Note: Full subscription functionality requires Subscription model
 * and payment integration to be implemented in future phases.
 *
 * @requirement SERV-E2E-003 Subscription management
 */
class SubscriptionManagementE2ETest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private ServiceCategory $category;
    private Service $monthlyService;
    private Service $yearlyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->category = ServiceCategory::factory()->create([
            'name' => 'Subscription Services',
            'slug' => 'subscription-services',
            'is_active' => true,
        ]);

        $this->monthlyService = Service::factory()->create([
            'service_category_id' => $this->category->id,
            'name' => 'Monthly Meat Box',
            'slug' => 'monthly-meat-box',
            'description' => 'Fresh meat delivered monthly',
            'price_aud' => 150.00,
            'billing_cycle' => 'monthly',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->yearlyService = Service::factory()->create([
            'service_category_id' => $this->category->id,
            'name' => 'Yearly Premium Package',
            'slug' => 'yearly-premium-package',
            'description' => 'Annual premium meat package with discount',
            'price_aud' => 1500.00,
            'billing_cycle' => 'yearly',
            'is_active' => true,
            'is_featured' => false,
        ]);
    }

    /** @test */
    public function customer_can_view_available_subscription_services()
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/services?status=active');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data');

        // Verify both services are available
        $serviceNames = collect($response->json('data.data'))->pluck('name')->toArray();
        $this->assertContains('Monthly Meat Box', $serviceNames);
        $this->assertContains('Yearly Premium Package', $serviceNames);
    }

    /** @test */
    public function customer_can_filter_services_by_billing_cycle()
    {
        // View only monthly services
        $monthlyResponse = $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/services?billing_cycle=monthly&status=active');

        $monthlyResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Monthly Meat Box');

        // View only yearly services
        $yearlyResponse = $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/services?billing_cycle=yearly&status=active');

        $yearlyResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Yearly Premium Package');
    }

    /** @test */
    public function customer_can_view_service_details_before_subscribing()
    {
        $response = $this->actingAs($this->customer)
            ->getJson("/api/v1/admin/services/{$this->monthlyService->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Monthly Meat Box')
            ->assertJsonPath('data.price_aud', '150.00')
            ->assertJsonPath('data.billing_cycle', 'monthly')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'features',
                    'price_aud',
                    'billing_cycle',
                    'category',
                ],
            ]);
    }

    /** @test */
    public function customer_can_view_featured_subscription_services()
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/services?featured=1&status=active');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Monthly Meat Box')
            ->assertJsonPath('data.data.0.is_featured', true);
    }

    /**
     * @test
     * Note: This is a placeholder test. Actual subscription creation
     * requires Subscription model and payment integration.
     */
    public function customer_subscription_creation_requires_subscription_model()
    {
        $this->markTestIncomplete(
            'Subscription creation requires Subscription model and payment integration to be implemented.'
        );

        // Future test would look like:
        // $response = $this->actingAs($this->customer)
        //     ->postJson('/api/v1/customer/subscriptions', [
        //         'service_id' => $this->monthlyService->id,
        //         'payment_method' => 'card',
        //     ]);
        //
        // $response->assertStatus(201)
        //     ->assertJsonPath('success', true);
    }

    /**
     * @test
     * Note: This is a placeholder test. Viewing subscriptions
     * requires Subscription model to be implemented.
     */
    public function customer_can_view_their_active_subscriptions()
    {
        $this->markTestIncomplete(
            'Viewing subscriptions requires Subscription model to be implemented.'
        );

        // Future test would look like:
        // $response = $this->actingAs($this->customer)
        //     ->getJson('/api/v1/customer/subscriptions');
        //
        // $response->assertStatus(200)
        //     ->assertJsonStructure([
        //         'success',
        //         'data' => [
        //             '*' => [
        //                 'id',
        //                 'service',
        //                 'status',
        //                 'next_billing_date',
        //                 'amount',
        //             ],
        //         ],
        //     ]);
    }

    /**
     * @test
     * Note: This is a placeholder test. Canceling subscriptions
     * requires Subscription model to be implemented.
     */
    public function customer_can_cancel_their_subscription()
    {
        $this->markTestIncomplete(
            'Subscription cancellation requires Subscription model to be implemented.'
        );

        // Future test would look like:
        // $response = $this->actingAs($this->customer)
        //     ->deleteJson('/api/v1/customer/subscriptions/1');
        //
        // $response->assertStatus(200)
        //     ->assertJsonPath('success', true);
    }

    /** @test */
    public function guest_cannot_access_subscription_endpoints()
    {
        // This test verifies authentication is required
        $this->markTestIncomplete(
            'Subscription endpoints require Subscription model to be implemented.'
        );

        // Future test would look like:
        // $response = $this->getJson('/api/v1/customer/subscriptions');
        // $response->assertStatus(401);
    }

    /** @test */
    public function customer_can_only_view_their_own_subscriptions()
    {
        $this->markTestIncomplete(
            'Subscription authorization requires Subscription model to be implemented.'
        );

        // Future test would ensure customers can't access other customers' subscriptions
    }

    /** @test */
    public function service_price_and_billing_cycle_are_displayed_correctly()
    {
        $response = $this->actingAs($this->customer)
            ->getJson("/api/v1/admin/services/{$this->monthlyService->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.price_aud', '150.00')
            ->assertJsonPath('data.billing_cycle', 'monthly')
            ->assertJsonPath('data.billing_cycle_label', 'Monthly');

        $yearlyResponse = $this->actingAs($this->customer)
            ->getJson("/api/v1/admin/services/{$this->yearlyService->id}");

        $yearlyResponse->assertStatus(200)
            ->assertJsonPath('data.price_aud', '1500.00')
            ->assertJsonPath('data.billing_cycle', 'yearly')
            ->assertJsonPath('data.billing_cycle_label', 'Yearly');
    }

    /** @test */
    public function customer_can_search_for_subscription_services()
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/admin/services?search=meat+box&status=active');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Monthly Meat Box');
    }
}
