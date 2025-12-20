<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Category;
use App\Models\DeliveryProof;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Staff Controller Feature Tests.
 *
 * Tests all staff dashboard endpoints for STAFF-001 to STAFF-024.
 */
class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $admin;
    private User $customer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->category = Category::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-001 Create staff dashboard homepage
     * @requirement STAFF-002 Show quick stats for staff
     */
    public function test_staff_can_get_dashboard(): void
    {
        Sanctum::actingAs($this->staff);

        // Create some test data
        Order::factory()->count(3)->create(['status' => Order::STATUS_PENDING]);
        Order::factory()->count(2)->create(['status' => Order::STATUS_PROCESSING]);

        $response = $this->getJson('/api/v1/staff/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'dashboard' => [
                    'today' => ['orders_today', 'deliveries_today', 'completed_today'],
                    'queue' => ['pending', 'processing', 'ready_for_pickup', 'out_for_delivery'],
                    'weekly_summary' => ['orders_this_week', 'deliveries_this_week', 'waste_logs_this_week'],
                    'low_stock_alerts',
                ],
            ]);
    }

    public function test_admin_can_access_staff_dashboard(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/staff/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_customer_cannot_access_staff_dashboard(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/staff/dashboard');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_staff_routes(): void
    {
        $response = $this->getJson('/api/v1/staff/dashboard');

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Queue Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-003 Create order queue view
     * @requirement STAFF-004 Add sorting/filtering for queue
     */
    public function test_can_get_order_queue(): void
    {
        Sanctum::actingAs($this->staff);

        Order::factory()->count(5)->create(['status' => Order::STATUS_PENDING]);
        Order::factory()->count(3)->create(['status' => Order::STATUS_PROCESSING]);

        $response = $this->getJson('/api/v1/staff/orders');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(8, 'orders')
            ->assertJsonStructure([
                'orders',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        Sanctum::actingAs($this->staff);

        Order::factory()->count(3)->create(['status' => Order::STATUS_PENDING]);
        Order::factory()->count(2)->create(['status' => Order::STATUS_PROCESSING]);
        Order::factory()->count(1)->create(['status' => Order::STATUS_DELIVERED]);

        $response = $this->getJson('/api/v1/staff/orders?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'orders');
    }

    public function test_can_search_orders(): void
    {
        Sanctum::actingAs($this->staff);

        $user = User::factory()->create(['name' => 'Unique Zambezi Test Customer']);
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
        ]);
        Order::factory()->count(3)->create(['status' => Order::STATUS_PENDING]);

        $response = $this->getJson('/api/v1/staff/orders?search=Unique Zambezi');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'orders');
    }

    /**
     * @requirement STAFF-006 Create order detail view for staff
     */
    public function test_can_get_single_order(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->getJson('/api/v1/staff/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.id', $order->id);
    }

    /**
     * @requirement STAFF-007 Implement status update buttons
     */
    public function test_can_update_order_status(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/status', [
            'status' => Order::STATUS_PROCESSING,
            'notes' => 'Starting to process order',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_PROCESSING);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    public function test_cannot_make_invalid_status_transition(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        // Cannot go directly from pending to delivered
        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/status', [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * @requirement STAFF-008 Add internal notes to orders
     */
    public function test_can_add_order_note(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create();

        $response = $this->postJson('/api/v1/staff/orders/' . $order->id . '/notes', [
            'note' => 'Customer called about delivery time.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertNotEmpty($order->staff_notes);
        $this->assertEquals('Customer called about delivery time.', $order->staff_notes[0]['content']);
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-009 Create "Today's Deliveries" list
     * @requirement STAFF-010 Show delivery details
     */
    public function test_can_get_todays_deliveries(): void
    {
        Sanctum::actingAs($this->staff);

        $address = Address::factory()->create();

        // Create deliveries for today
        Order::factory()->count(2)->create([
            'status' => Order::STATUS_READY,
            'delivery_method' => 'delivery',
            'address_id' => $address->id,
            'delivery_date' => now()->toDateString(),
        ]);

        Order::factory()->create([
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
            'delivery_method' => 'delivery',
            'address_id' => $address->id,
            'delivery_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/staff/deliveries/today');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'date',
                'deliveries' => ['pending_dispatch', 'in_transit', 'completed'],
                'summary' => ['total', 'pending', 'in_transit', 'completed'],
            ])
            ->assertJsonPath('summary.total', 3);
    }

    /**
     * @requirement STAFF-011 Mark orders "Out for Delivery"
     */
    public function test_can_mark_order_out_for_delivery(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_READY]);

        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/out-for-delivery');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_OUT_FOR_DELIVERY);
    }

    public function test_cannot_mark_non_ready_order_out_for_delivery(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/out-for-delivery');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Proof of Delivery Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-012 Create POD capture interface
     * @requirement STAFF-013 Store POD with order
     */
    public function test_can_upload_proof_of_delivery(): void
    {
        Sanctum::actingAs($this->staff);
        Storage::fake('public');

        $order = Order::factory()->create(['status' => Order::STATUS_OUT_FOR_DELIVERY]);

        $response = $this->postJson('/api/v1/staff/orders/' . $order->id . '/proof-of-delivery', [
            'recipient_name' => 'John Smith',
            'notes' => 'Left with neighbor',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('delivery_proofs', [
            'order_id' => $order->id,
            'recipient_name' => 'John Smith',
            'notes' => 'Left with neighbor',
        ]);

        // Order should be marked as delivered
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_DELIVERED,
        ]);
    }

    public function test_cannot_upload_pod_for_non_out_for_delivery_order(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_READY]);

        $response = $this->postJson('/api/v1/staff/orders/' . $order->id . '/proof-of-delivery', [
            'recipient_name' => 'John Smith',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * @requirement STAFF-014 View POD history
     */
    public function test_can_get_proof_of_delivery(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create(['status' => Order::STATUS_DELIVERED]);
        DeliveryProof::factory()->create([
            'order_id' => $order->id,
            'recipient_name' => 'Jane Doe',
            'captured_by' => $this->staff->id,
        ]);

        $response = $this->getJson('/api/v1/staff/orders/' . $order->id . '/proof-of-delivery');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('proof_of_delivery.recipient_name', 'Jane Doe');
    }

    public function test_get_pod_returns_404_when_none_exists(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create();

        $response = $this->getJson('/api/v1/staff/orders/' . $order->id . '/proof-of-delivery');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Waste Logging Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-015 Create waste logging interface
     * @requirement STAFF-016 Record reason for waste
     */
    public function test_can_log_waste(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
            'price_aud' => 10.00,
        ]);

        $response = $this->postJson('/api/v1/staff/waste', [
            'product_id' => $product->id,
            'quantity' => 5,
            'reason' => WasteLog::REASON_DAMAGED,
            'notes' => 'Damaged in storage',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('waste_log.quantity', '5.00')
            ->assertJsonPath('waste_log.reason', WasteLog::REASON_DAMAGED)
            ->assertJsonPath('waste_log.total_cost', '50.00');

        // Verify stock was reduced
        $product->refresh();
        $this->assertEquals(45, $product->stock);
    }

    public function test_waste_log_validates_reason(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->postJson('/api/v1/staff/waste', [
            'product_id' => $product->id,
            'quantity' => 5,
            'reason' => 'invalid_reason',
        ]);

        $response->assertStatus(422);
    }

    /**
     * @requirement STAFF-017 View waste log history
     */
    public function test_can_get_waste_logs(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create(['category_id' => $this->category->id]);
        WasteLog::factory()->count(5)->create([
            'product_id' => $product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->getJson('/api/v1/staff/waste');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'waste_logs')
            ->assertJsonStructure([
                'waste_logs',
                'totals' => ['total_quantity', 'total_cost'],
                'meta',
            ]);
    }

    public function test_can_filter_waste_logs_by_reason(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create(['category_id' => $this->category->id]);
        WasteLog::factory()->count(3)->create([
            'product_id' => $product->id,
            'logged_by' => $this->staff->id,
            'reason' => WasteLog::REASON_DAMAGED,
        ]);
        WasteLog::factory()->count(2)->create([
            'product_id' => $product->id,
            'logged_by' => $this->staff->id,
            'reason' => WasteLog::REASON_EXPIRED,
        ]);

        $response = $this->getJson('/api/v1/staff/waste?reason=' . WasteLog::REASON_DAMAGED);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'waste_logs');
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Check Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-018 Create quick stock check
     */
    public function test_can_get_stock_check(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->count(5)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'stock' => 50,
        ]);

        $response = $this->getJson('/api/v1/staff/stock');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'products',
                'summary' => ['total_products', 'low_stock', 'out_of_stock'],
                'meta',
            ]);
    }

    public function test_can_filter_low_stock_products(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'stock' => 5, // Low stock
        ]);
        Product::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'stock' => 50, // Normal stock
        ]);

        $response = $this->getJson('/api/v1/staff/stock?low_stock_only=true');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'products');
    }

    public function test_can_search_stock(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Beef Steak',
            'sku' => 'BEEF-001',
            'is_active' => true,
        ]);
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/staff/stock?search=Beef');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'products');
    }

    /**
     * @requirement STAFF-019 Quick stock adjustment
     */
    public function test_can_update_stock(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        $response = $this->putJson('/api/v1/staff/stock/' . $product->id, [
            'adjustment' => 10,
            'reason' => 'New stock received',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('product.old_stock', 50)
            ->assertJsonPath('product.new_stock', 60);

        $product->refresh();
        $this->assertEquals(60, $product->stock);
    }

    public function test_stock_cannot_go_below_zero(): void
    {
        Sanctum::actingAs($this->staff);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
        ]);

        $response = $this->putJson('/api/v1/staff/stock/' . $product->id, [
            'adjustment' => -10,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('product.new_stock', 0);

        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }

    /*
    |--------------------------------------------------------------------------
    | Activity & Performance Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-020 Create activity log for staff actions
     * @requirement STAFF-021 Track order status changes
     */
    public function test_can_get_activity_log(): void
    {
        Sanctum::actingAs($this->staff);

        // Create some activity
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        WasteLog::factory()->count(2)->create([
            'product_id' => $product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->getJson('/api/v1/staff/activity');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'date',
                'activity' => ['status_changes', 'waste_logs', 'deliveries'],
                'summary',
            ]);
    }

    /**
     * @requirement STAFF-022 Show staff performance metrics
     */
    public function test_can_get_performance_stats(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/v1/staff/performance');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'staff' => ['id', 'name'],
                'this_week' => ['orders_processed', 'deliveries_completed', 'waste_logs'],
                'this_month' => ['orders_processed', 'deliveries_completed', 'waste_logs'],
            ])
            ->assertJsonPath('staff.id', $this->staff->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Pickup Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement STAFF-024 Create pickup management view
     */
    public function test_can_get_todays_pickups(): void
    {
        Sanctum::actingAs($this->staff);

        // Create pickups for today
        Order::factory()->count(2)->create([
            'status' => Order::STATUS_READY,
            'delivery_method' => 'pickup',
            'pickup_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/staff/pickups/today');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'date',
                'pickups' => ['awaiting', 'picked_up'],
                'summary' => ['total', 'awaiting', 'picked_up'],
            ])
            ->assertJsonPath('summary.awaiting', 2);
    }

    public function test_can_mark_order_as_picked_up(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create([
            'status' => Order::STATUS_READY,
            'delivery_method' => 'pickup',
        ]);

        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/picked-up');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', Order::STATUS_DELIVERED);
    }

    public function test_cannot_mark_delivery_order_as_picked_up(): void
    {
        Sanctum::actingAs($this->staff);

        $order = Order::factory()->create([
            'status' => Order::STATUS_READY,
            'delivery_method' => 'delivery',
        ]);

        $response = $this->putJson('/api/v1/staff/orders/' . $order->id . '/picked-up');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Tests
    |--------------------------------------------------------------------------
    */

    public function test_all_staff_endpoints_require_staff_role(): void
    {
        Sanctum::actingAs($this->customer);

        $endpoints = [
            ['GET', '/api/v1/staff/dashboard'],
            ['GET', '/api/v1/staff/orders'],
            ['GET', '/api/v1/staff/deliveries/today'],
            ['GET', '/api/v1/staff/waste'],
            ['GET', '/api/v1/staff/stock'],
            ['GET', '/api/v1/staff/activity'],
            ['GET', '/api/v1/staff/performance'],
            ['GET', '/api/v1/staff/pickups/today'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(403, "Expected 403 for {$method} {$uri}");
        }
    }

    public function test_all_staff_endpoints_require_authentication(): void
    {
        $endpoints = [
            ['GET', '/api/v1/staff/dashboard'],
            ['GET', '/api/v1/staff/orders'],
            ['GET', '/api/v1/staff/deliveries/today'],
            ['GET', '/api/v1/staff/waste'],
            ['GET', '/api/v1/staff/stock'],
            ['GET', '/api/v1/staff/activity'],
            ['GET', '/api/v1/staff/performance'],
            ['GET', '/api/v1/staff/pickups/today'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401, "Expected 401 for {$method} {$uri}");
        }
    }
}
