<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Category;
use App\Models\DeliveryProof;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * DeliveryController tests.
 *
 * @requirement DEL-019 Write delivery module tests
 */
class DeliveryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private DeliveryZone $zone;
    private Address $address;
    private Order $deliveryOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->zone = DeliveryZone::factory()->create([
            'name' => 'Engadine Local',
            'suburbs' => ['Engadine', 'Heathcote'],
            'delivery_fee' => 0,
            'free_delivery_threshold' => 100,
            'is_active' => true,
        ]);

        $this->address = Address::factory()->create([
            'user_id' => $this->customer->id,
            'suburb' => 'Engadine',
            'postcode' => '2233',
        ]);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->deliveryOrder = Order::factory()->create([
            'user_id' => $this->customer->id,
            'address_id' => $this->address->id,
            'delivery_zone_id' => $this->zone->id,
            'delivery_method' => 'delivery',
            'status' => 'confirmed',
            'scheduled_date' => now()->toDateString(),
            'total' => 150.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->deliveryOrder->id,
            'product_id' => $product->id,
        ]);
    }

    // =========================================================================
    // AUTHORIZATION TESTS
    // =========================================================================

    /** @test */
    public function admin_can_access_delivery_dashboard(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'today',
                    'weekly',
                    'staff_performance',
                ],
            ]);
    }

    /** @test */
    public function staff_cannot_access_admin_delivery_routes(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/v1/admin/deliveries/dashboard');

        $response->assertForbidden();
    }

    /** @test */
    public function customer_cannot_access_delivery_routes(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/admin/deliveries/dashboard');

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_delivery_routes(): void
    {
        $response = $this->getJson('/api/v1/admin/deliveries/dashboard');

        $response->assertUnauthorized();
    }

    // =========================================================================
    // DASHBOARD TESTS
    // =========================================================================

    /** @test */
    public function dashboard_shows_todays_delivery_stats(): void
    {
        Sanctum::actingAs($this->admin);

        // Create more orders for today
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_method' => 'delivery',
            'status' => 'out_for_delivery',
            'scheduled_date' => now()->toDateString(),
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_method' => 'delivery',
            'status' => 'delivered',
            'scheduled_date' => now()->toDateString(),
            'delivered_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/deliveries/dashboard');

        $response->assertOk();
        $today = $response->json('data.today');

        $this->assertArrayHasKey('total', $today);
        $this->assertArrayHasKey('pending', $today);
        $this->assertArrayHasKey('in_progress', $today);
        $this->assertArrayHasKey('completed', $today);
    }

    // =========================================================================
    // DELIVERIES LIST TESTS
    // =========================================================================

    /** @test */
    public function can_get_deliveries_list(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function can_filter_deliveries_by_status(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries?status=confirmed');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $delivery) {
            $this->assertEquals('confirmed', $delivery['status']);
        }
    }

    /** @test */
    public function can_filter_deliveries_by_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        $startDate = now()->subDays(7)->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/v1/admin/deliveries?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk();
    }

    /** @test */
    public function can_filter_deliveries_by_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $this->deliveryOrder->update(['assigned_to' => $this->staff->id]);

        $response = $this->getJson("/api/v1/admin/deliveries?staff_id={$this->staff->id}");

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $delivery) {
            $this->assertEquals($this->staff->id, $delivery['assigned_to']['id']);
        }
    }

    /** @test */
    public function can_search_deliveries(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries?search=' . $this->deliveryOrder->order_number);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertNotEmpty($data);
    }

    // =========================================================================
    // DELIVERY DETAIL TESTS
    // =========================================================================

    /** @test */
    public function can_get_delivery_detail(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'order_number',
                    'customer',
                    'address',
                    'status',
                    'scheduled_date',
                    'items',
                    'total',
                ],
            ]);

        $this->assertEquals($this->deliveryOrder->id, $response->json('data.id'));
    }

    /** @test */
    public function returns_404_for_nonexistent_delivery(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries/999999');

        $response->assertNotFound();
    }

    /** @test */
    public function returns_404_for_pickup_order(): void
    {
        Sanctum::actingAs($this->admin);

        $pickupOrder = Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_method' => 'pickup',
        ]);

        $response = $this->getJson("/api/v1/admin/deliveries/{$pickupOrder->id}");

        $response->assertNotFound();
    }

    // =========================================================================
    // ASSIGN DELIVERY TESTS
    // =========================================================================

    /** @test */
    public function can_assign_delivery_to_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/assign", [
            'staff_id' => $this->staff->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->deliveryOrder->refresh();
        $this->assertEquals($this->staff->id, $this->deliveryOrder->assigned_to);
    }

    /** @test */
    public function can_reassign_delivery(): void
    {
        Sanctum::actingAs($this->admin);

        $this->deliveryOrder->update(['assigned_to' => $this->staff->id]);

        $newStaff = User::factory()->create(['role' => 'staff']);

        $response = $this->putJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/assign", [
            'staff_id' => $newStaff->id,
            'reason' => 'Staff unavailable',
        ]);

        $response->assertOk();

        $this->deliveryOrder->refresh();
        $this->assertEquals($newStaff->id, $this->deliveryOrder->assigned_to);
    }

    /** @test */
    public function cannot_assign_delivery_to_non_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/assign", [
            'staff_id' => $this->customer->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Selected user is not a staff member',
            ]);
    }

    // =========================================================================
    // PROOF OF DELIVERY TESTS
    // =========================================================================

    /** @test */
    public function can_get_proof_of_delivery(): void
    {
        Sanctum::actingAs($this->admin);

        DeliveryProof::factory()->create([
            'order_id' => $this->deliveryOrder->id,
            'captured_by' => $this->staff->id,
            'captured_at' => now(),
            'recipient_name' => 'John Doe',
            'left_at_door' => false,
        ]);

        $response = $this->getJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/pod");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'order_id',
                    'captured_at',
                    'recipient_name',
                    'left_at_door',
                ],
            ]);
    }

    /** @test */
    public function returns_404_when_no_pod_exists(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/pod");

        $response->assertNotFound();
    }

    // =========================================================================
    // DELIVERY ISSUE TESTS
    // =========================================================================

    /** @test */
    public function can_resolve_delivery_issue(): void
    {
        Sanctum::actingAs($this->admin);

        $this->deliveryOrder->update([
            'delivery_issue' => 'Customer not home',
            'delivery_issue_reported_at' => now(),
        ]);

        $response = $this->putJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/issue", [
            'resolved' => true,
            'resolution' => 'Rescheduled for next day',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Delivery issue marked as resolved',
            ]);

        $this->deliveryOrder->refresh();
        $this->assertNotNull($this->deliveryOrder->delivery_issue_resolved_at);
    }

    /** @test */
    public function cannot_resolve_nonexistent_issue(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/deliveries/{$this->deliveryOrder->id}/issue", [
            'resolved' => true,
            'resolution' => 'Fixed',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'No issue reported for this delivery',
            ]);
    }

    // =========================================================================
    // DELIVERY ZONES TESTS
    // =========================================================================

    /** @test */
    public function can_get_delivery_zones(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/delivery-zones');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    /** @test */
    public function can_filter_zones_by_active_status(): void
    {
        Sanctum::actingAs($this->admin);

        DeliveryZone::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/admin/delivery-zones?active=true');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $zone) {
            $this->assertTrue($zone['is_active']);
        }
    }

    /** @test */
    public function can_create_delivery_zone(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/delivery-zones', [
            'name' => 'Sydney CBD',
            'suburbs' => ['Sydney', 'Haymarket', 'Surry Hills'],
            'delivery_fee' => 15.00,
            'free_delivery_threshold' => 150.00,
            'estimated_days' => 2,
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone created successfully',
            ]);

        $this->assertDatabaseHas('delivery_zones', [
            'name' => 'Sydney CBD',
            'delivery_fee' => 15.00,
        ]);
    }

    /** @test */
    public function create_zone_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/delivery-zones', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'name',
                        'suburbs',
                        'delivery_fee',
                        'estimated_days',
                    ],
                ],
            ]);
    }

    /** @test */
    public function cannot_create_zone_with_duplicate_name(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/delivery-zones', [
            'name' => 'Engadine Local', // Already exists
            'suburbs' => ['Test Suburb'],
            'delivery_fee' => 10.00,
            'estimated_days' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'name',
                    ],
                ],
            ]);
    }

    /** @test */
    public function can_update_delivery_zone(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/delivery-zones/{$this->zone->id}", [
            'delivery_fee' => 8.00,
            'free_delivery_threshold' => 120.00,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone updated successfully',
            ]);

        $this->zone->refresh();
        $this->assertEquals(8.00, $this->zone->delivery_fee);
        $this->assertEquals(120.00, $this->zone->free_delivery_threshold);
    }

    /** @test */
    public function can_delete_delivery_zone(): void
    {
        Sanctum::actingAs($this->admin);

        $newZone = DeliveryZone::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/delivery-zones/{$newZone->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Delivery zone deleted successfully',
            ]);

        $this->assertDatabaseMissing('delivery_zones', ['id' => $newZone->id]);
    }

    /** @test */
    public function cannot_delete_zone_with_active_orders(): void
    {
        Sanctum::actingAs($this->admin);

        // deliveryOrder uses this->zone
        $response = $this->deleteJson("/api/v1/admin/delivery-zones/{$this->zone->id}");

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function returns_404_for_nonexistent_zone(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/delivery-zones/999999', [
            'delivery_fee' => 10.00,
        ]);

        $response->assertNotFound();
    }

    // =========================================================================
    // DELIVERY SETTINGS TESTS
    // =========================================================================

    /** @test */
    public function can_get_delivery_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/delivery-settings');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'free_delivery_threshold',
                    'per_km_rate',
                    'base_fee',
                    'max_delivery_distance_km',
                    'store_address',
                ],
            ]);
    }

    /** @test */
    public function can_update_delivery_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/delivery-settings', [
            'free_delivery_threshold' => 150.00,
            'per_km_rate' => 0.20,
            'base_fee' => 7.00,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Delivery settings updated successfully',
            ]);

        $this->assertEquals(150.00, Setting::getValue('delivery.free_threshold'));
        $this->assertEquals(0.20, Setting::getValue('delivery.per_km_rate'));
        $this->assertEquals(7.00, Setting::getValue('delivery.base_fee'));
    }

    /** @test */
    public function settings_validate_numeric_values(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/delivery-settings', [
            'free_delivery_threshold' => -50,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'free_delivery_threshold',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // MAP DATA TESTS
    // =========================================================================

    /** @test */
    public function can_get_delivery_map_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries/map');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date',
                    'store',
                    'deliveries',
                    'count',
                ],
            ]);
    }

    /** @test */
    public function can_get_map_data_for_specific_date(): void
    {
        Sanctum::actingAs($this->admin);

        $date = now()->addDay()->toDateString();

        $response = $this->getJson("/api/v1/admin/deliveries/map?date={$date}");

        $response->assertOk();
        $this->assertEquals($date, $response->json('data.date'));
    }

    // =========================================================================
    // REPORT TESTS
    // =========================================================================

    /** @test */
    public function can_get_delivery_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries/report');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'summary',
                    'by_status',
                    'staff_performance',
                    'issues',
                ],
            ]);
    }

    /** @test */
    public function can_get_report_with_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        $startDate = now()->subMonth()->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/v1/admin/deliveries/report?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk();
        $this->assertEquals($startDate, $response->json('data.period.start_date'));
        $this->assertEquals($endDate, $response->json('data.period.end_date'));
    }

    // =========================================================================
    // EXPORT TESTS
    // =========================================================================

    /** @test */
    public function can_export_deliveries(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/deliveries/export');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'summary',
                    'deliveries',
                ],
            ]);
    }

    // =========================================================================
    // SECURITY TESTS
    // =========================================================================

    /** @test */
    public function all_delivery_endpoints_require_admin_role(): void
    {
        Sanctum::actingAs($this->staff);

        $endpoints = [
            ['GET', '/api/v1/admin/deliveries/dashboard'],
            ['GET', '/api/v1/admin/deliveries'],
            ['GET', '/api/v1/admin/deliveries/map'],
            ['GET', '/api/v1/admin/deliveries/report'],
            ['GET', '/api/v1/admin/deliveries/export'],
            ['GET', '/api/v1/admin/delivery-zones'],
            ['POST', '/api/v1/admin/delivery-zones'],
            ['GET', '/api/v1/admin/delivery-settings'],
            ['PUT', '/api/v1/admin/delivery-settings'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertTrue(
                $response->status() === 403,
                "Expected 403 for {$method} {$endpoint}, got {$response->status()}"
            );
        }
    }

    /** @test */
    public function all_delivery_endpoints_require_authentication(): void
    {
        $endpoints = [
            ['GET', '/api/v1/admin/deliveries/dashboard'],
            ['GET', '/api/v1/admin/deliveries'],
            ['GET', '/api/v1/admin/delivery-zones'],
            ['GET', '/api/v1/admin/delivery-settings'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertTrue(
                $response->status() === 401,
                "Expected 401 for {$method} {$endpoint}, got {$response->status()}"
            );
        }
    }
}
