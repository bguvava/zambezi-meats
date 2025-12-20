<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * AdminControllerTest covers all admin dashboard functionality.
 *
 * @requirement ADMIN-001 to ADMIN-028
 */
class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->category = Category::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-026 Implement admin middleware
     */
    public function test_admin_can_access_dashboard(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'dashboard' => [
                    'today' => ['revenue', 'orders', 'new_customers', 'pending_orders'],
                    'revenue_chart',
                    'recent_orders',
                    'low_stock_products',
                    'top_products',
                ],
            ]);
    }

    /**
     * @requirement ADMIN-026 Implement admin middleware
     */
    public function test_staff_cannot_access_admin_dashboard(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * @requirement ADMIN-026 Implement admin middleware
     */
    public function test_customer_cannot_access_admin_dashboard(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * @requirement ADMIN-026 Implement admin middleware
     */
    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-002 Create dashboard overview with KPIs
     */
    public function test_dashboard_shows_todays_revenue(): void
    {
        Sanctum::actingAs($this->admin);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total' => 100.00,
            'status' => Order::STATUS_DELIVERED,
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total' => 50.00,
            'status' => Order::STATUS_CONFIRMED,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(150.00, $response->json('dashboard.today.revenue'));
    }

    /**
     * @requirement ADMIN-004 Create revenue chart (7/30 days)
     */
    public function test_dashboard_shows_revenue_chart(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $chart = $response->json('dashboard.revenue_chart');
        $this->assertCount(7, $chart);
        $this->assertArrayHasKey('date', $chart[0]);
        $this->assertArrayHasKey('day', $chart[0]);
        $this->assertArrayHasKey('revenue', $chart[0]);
    }

    /**
     * @requirement ADMIN-002 Create dashboard overview with KPIs
     */
    public function test_dashboard_shows_low_stock_products(): void
    {
        Sanctum::actingAs($this->admin);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $lowStock = $response->json('dashboard.low_stock_products');
        $this->assertCount(1, $lowStock);
        $this->assertEquals(5, $lowStock[0]['stock']);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-005 Create orders management page
     */
    public function test_can_get_orders(): void
    {
        Sanctum::actingAs($this->admin);

        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/admin/orders');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'orders');
    }

    /**
     * @requirement ADMIN-006 Implement order filtering
     */
    public function test_can_filter_orders_by_status(): void
    {
        Sanctum::actingAs($this->admin);

        Order::factory()->count(2)->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_PENDING,
        ]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response = $this->getJson('/api/v1/admin/orders?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'orders');
    }

    /**
     * @requirement ADMIN-006 Implement order filtering
     */
    public function test_can_filter_orders_by_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now()->subDays(5),
        ]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/orders?date_from=' . now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'orders');
    }

    /**
     * @requirement ADMIN-006 Implement order filtering
     */
    public function test_can_search_orders(): void
    {
        Sanctum::actingAs($this->admin);

        $user = User::factory()->create(['name' => 'Unique Admin Search Customer']);
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/admin/orders?search=Unique Admin Search');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'orders');
    }

    /**
     * @requirement ADMIN-007 Create order detail view
     */
    public function test_can_get_single_order(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/admin/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.id', $order->id);
    }

    /**
     * @requirement ADMIN-008 Implement order actions
     */
    public function test_can_update_order(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->putJson('/api/v1/admin/orders/' . $order->id, [
            'delivery_instructions' => 'Leave at back door',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('Leave at back door', $order->fresh()->delivery_instructions);
    }

    /**
     * @requirement ADMIN-008 Implement order actions
     */
    public function test_can_update_order_status(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this->putJson('/api/v1/admin/orders/' . $order->id . '/status', [
            'status' => Order::STATUS_CONFIRMED,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    /**
     * @requirement ADMIN-009 Assign orders to staff
     */
    public function test_can_assign_order_to_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->postJson('/api/v1/admin/orders/' . $order->id . '/assign', [
            'staff_id' => $this->staff->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals($this->staff->id, $order->fresh()->assigned_to);
    }

    /**
     * @requirement ADMIN-009 Assign orders to staff
     */
    public function test_cannot_assign_order_to_non_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->postJson('/api/v1/admin/orders/' . $order->id . '/assign', [
            'staff_id' => $this->customer->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * @requirement ADMIN-010 Process refunds
     */
    public function test_can_refund_order(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total' => 100.00,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $response = $this->postJson('/api/v1/admin/orders/' . $order->id . '/refund', [
            'amount' => 50.00,
            'reason' => 'Customer requested partial refund',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertEquals(Payment::STATUS_REFUNDED, $order->payment->status);
    }

    /**
     * @requirement ADMIN-010 Process refunds
     */
    public function test_cannot_refund_order_twice(): void
    {
        Sanctum::actingAs($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total' => 100.00,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'status' => Payment::STATUS_REFUNDED,
        ]);

        $response = $this->postJson('/api/v1/admin/orders/' . $order->id . '/refund', [
            'reason' => 'Another refund attempt',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-011 Create products management page
     */
    public function test_can_get_products(): void
    {
        Sanctum::actingAs($this->admin);

        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/v1/admin/products');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'products');
    }

    /**
     * @requirement ADMIN-011 Create products management page
     */
    public function test_can_filter_products_by_category(): void
    {
        Sanctum::actingAs($this->admin);

        $category2 = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $this->category->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->getJson('/api/v1/admin/products?category_id=' . $this->category->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'products');
    }

    /**
     * @requirement ADMIN-012 Create add/edit product form
     */
    public function test_can_create_product(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/products', [
            'name' => 'Wagyu Steak',
            'description' => 'Premium wagyu beef',
            'price_aud' => 99.99,
            'category_id' => $this->category->id,
            'stock' => 50,
            'unit' => 'kg',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('product.name', 'Wagyu Steak');

        $this->assertDatabaseHas('products', ['name' => 'Wagyu Steak']);
    }

    /**
     * @requirement ADMIN-012 Create add/edit product form
     */
    public function test_product_auto_generates_sku(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/products', [
            'name' => 'Test Auto SKU Product',
            'price_aud' => 25.00,
            'category_id' => $this->category->id,
            'stock' => 10,
            'unit' => 'piece',
            // No SKU provided - should auto-generate
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('product.sku'));
        $this->assertStringStartsWith('SKU-', $response->json('product.sku'));
    }

    /**
     * @requirement ADMIN-012 Create add/edit product form
     */
    public function test_can_update_product(): void
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price_aud' => 50.00,
        ]);

        $response = $this->putJson('/api/v1/admin/products/' . $product->id, [
            'price_aud' => 75.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('75.00', $product->fresh()->price_aud);
    }

    /**
     * @requirement ADMIN-015 Delete products (single)
     */
    public function test_can_delete_product(): void
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->deleteJson('/api/v1/admin/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Soft delete by marking as inactive
        $this->assertFalse($product->fresh()->is_active);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-014 Create categories management
     */
    public function test_can_get_categories(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['categories']);
    }

    /**
     * @requirement ADMIN-014 Create categories management
     */
    public function test_can_create_category(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Poultry',
            'description' => 'Fresh poultry products',
            'sort_order' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('category.name', 'Poultry');

        $this->assertDatabaseHas('categories', ['name' => 'Poultry']);
    }

    /**
     * @requirement ADMIN-014 Create categories management
     */
    public function test_can_update_category(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/admin/categories/' . $category->id, [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('New Name', $category->fresh()->name);
    }

    /**
     * @requirement ADMIN-014 Create categories management
     */
    public function test_can_delete_empty_category(): void
    {
        Sanctum::actingAs($this->admin);

        $emptyCategory = Category::factory()->create();

        $response = $this->deleteJson('/api/v1/admin/categories/' . $emptyCategory->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $emptyCategory->id]);
    }

    /**
     * @requirement ADMIN-014 Create categories management
     */
    public function test_cannot_delete_category_with_products(): void
    {
        Sanctum::actingAs($this->admin);

        Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->deleteJson('/api/v1/admin/categories/' . $this->category->id);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-016 Create customers management page
     */
    public function test_can_get_customers(): void
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(3)->create(['role' => User::ROLE_CUSTOMER]);

        $response = $this->getJson('/api/v1/admin/customers');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Should return at least 4 (3 new + 1 from setUp)
        $this->assertGreaterThanOrEqual(4, count($response->json('customers')));
    }

    /**
     * @requirement ADMIN-016 Create customers management page
     */
    public function test_can_search_customers(): void
    {
        Sanctum::actingAs($this->admin);

        User::factory()->create([
            'name' => 'Unique Admin Customer Name',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->getJson('/api/v1/admin/customers?search=Unique Admin Customer');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'customers');
    }

    /**
     * @requirement ADMIN-017 View customer details
     */
    public function test_can_get_customer_details(): void
    {
        Sanctum::actingAs($this->admin);

        Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/admin/customers/' . $this->customer->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('customer.id', $this->customer->id)
            ->assertJsonStructure([
                'customer',
                'recent_orders',
                'addresses',
                'support_tickets',
            ]);
    }

    /**
     * @requirement ADMIN-017 View customer details
     */
    public function test_can_update_customer(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/customers/' . $this->customer->id, [
            'phone' => '0412345678',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('0412345678', $this->customer->fresh()->phone);
    }

    /*
    |--------------------------------------------------------------------------
    | Staff Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-018 Create staff management page
     */
    public function test_can_get_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/staff');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Should include admin and staff from setUp
        $this->assertGreaterThanOrEqual(2, count($response->json('staff')));
    }

    /**
     * @requirement ADMIN-019 Create/edit staff accounts
     */
    public function test_can_create_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/staff', [
            'name' => 'New Staff Member',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'role' => User::ROLE_STAFF,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('staff.name', 'New Staff Member');

        $this->assertDatabaseHas('users', [
            'email' => 'newstaff@example.com',
            'role' => User::ROLE_STAFF,
        ]);
    }

    /**
     * @requirement ADMIN-019 Create/edit staff accounts
     */
    public function test_can_update_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/staff/' . $this->staff->id, [
            'phone' => '0498765432',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('0498765432', $this->staff->fresh()->phone);
    }

    /**
     * @requirement ADMIN-020 Activate/deactivate staff
     */
    public function test_can_deactivate_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/staff/' . $this->staff->id, [
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertFalse($this->staff->fresh()->is_active);
    }

    /**
     * @requirement ADMIN-018 Create staff management page
     */
    public function test_can_delete_staff(): void
    {
        Sanctum::actingAs($this->admin);

        $staffToDelete = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->deleteJson('/api/v1/admin/staff/' . $staffToDelete->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $staffToDelete->id]);
    }

    /**
     * @requirement ADMIN-018 Create staff management page
     */
    public function test_admin_cannot_delete_self(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/v1/admin/staff/' . $this->admin->id);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * @requirement ADMIN-021 View staff activity
     */
    public function test_can_view_staff_activity(): void
    {
        Sanctum::actingAs($this->admin);

        // Create some activity
        ActivityLog::create([
            'user_id' => $this->staff->id,
            'action' => 'test_action',
            'loggable_type' => Order::class,
            'loggable_id' => 1,
            'new_values' => ['test' => 'value'],
        ]);

        $response = $this->getJson('/api/v1/admin/staff/' . $this->staff->id . '/activity');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'staff',
                'activity',
                'stats' => ['orders_processed', 'deliveries_completed'],
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Promotion Management Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-022 Create promotions management
     */
    public function test_can_get_promotions(): void
    {
        Sanctum::actingAs($this->admin);

        Promotion::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/promotions');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'promotions');
    }

    /**
     * @requirement ADMIN-022 Create promotions management
     */
    public function test_can_create_promotion(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/promotions', [
            'name' => 'Summer Sale',
            'code' => 'SUMMER20',
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 20,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('promotion.code', 'SUMMER20');

        $this->assertDatabaseHas('promotions', ['code' => 'SUMMER20']);
    }

    /**
     * @requirement ADMIN-022 Create promotions management
     */
    public function test_can_update_promotion(): void
    {
        Sanctum::actingAs($this->admin);

        $promotion = Promotion::factory()->create(['value' => 10]);

        $response = $this->putJson('/api/v1/admin/promotions/' . $promotion->id, [
            'value' => 25,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('25.00', $promotion->fresh()->value);
    }

    /**
     * @requirement ADMIN-022 Create promotions management
     */
    public function test_can_delete_promotion(): void
    {
        Sanctum::actingAs($this->admin);

        $promotion = Promotion::factory()->create();

        $response = $this->deleteJson('/api/v1/admin/promotions/' . $promotion->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Log Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement ADMIN-023 Create audit/activity logs page
     */
    public function test_can_get_activity_logs(): void
    {
        Sanctum::actingAs($this->admin);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'test_action',
            'loggable_type' => User::class,
            'loggable_id' => 1,
            'new_values' => ['test' => 'value'],
        ]);

        $response = $this->getJson('/api/v1/admin/logs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * @requirement ADMIN-023 Create audit/activity logs page
     */
    public function test_can_filter_activity_logs(): void
    {
        Sanctum::actingAs($this->admin);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'product_created',
            'loggable_type' => Product::class,
            'loggable_id' => 1,
            'new_values' => ['name' => 'Test'],
        ]);

        ActivityLog::create([
            'user_id' => $this->staff->id,
            'action' => 'order_updated',
            'loggable_type' => Order::class,
            'loggable_id' => 1,
            'new_values' => ['status' => 'confirmed'],
        ]);

        $response = $this->getJson('/api/v1/admin/logs?action=product_created');

        $response->assertStatus(200);
        $logs = $response->json('logs');
        foreach ($logs as $log) {
            $this->assertEquals('product_created', $log['action']);
        }
    }

    /**
     * @requirement ADMIN-024 Implement bulk delete for activity logs
     */
    public function test_can_bulk_delete_activity_logs(): void
    {
        Sanctum::actingAs($this->admin);

        $log1 = ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'test1',
            'loggable_type' => User::class,
            'loggable_id' => 1,
            'new_values' => ['test' => '1'],
        ]);

        $log2 = ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'test2',
            'loggable_type' => User::class,
            'loggable_id' => 2,
            'new_values' => ['test' => '2'],
        ]);

        $response = $this->deleteJson('/api/v1/admin/logs/bulk', [
            'ids' => [$log1->id, $log2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted_count', 2);

        $this->assertDatabaseMissing('activity_logs', ['id' => $log1->id]);
        $this->assertDatabaseMissing('activity_logs', ['id' => $log2->id]);
    }

    /**
     * @requirement ADMIN-024 Implement bulk delete for activity logs
     */
    public function test_can_bulk_delete_logs_by_date(): void
    {
        Sanctum::actingAs($this->admin);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'old_action',
            'loggable_type' => User::class,
            'loggable_id' => 1,
            'new_values' => ['test' => 'old'],
            'created_at' => now()->subDays(30),
        ]);

        $response = $this->deleteJson('/api/v1/admin/logs/bulk', [
            'before_date' => now()->subDays(7)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Tests - All Endpoints
    |--------------------------------------------------------------------------
    */

    /**
     * Test that admin-only endpoints require admin role.
     * Note: Products and categories are accessible by staff per PROD-002.
     */
    public function test_all_admin_endpoints_require_admin_role(): void
    {
        Sanctum::actingAs($this->staff);

        // Admin-only endpoints (staff should get 403)
        $adminOnlyEndpoints = [
            ['GET', '/api/v1/admin/dashboard'],
            ['GET', '/api/v1/admin/orders'],
            ['GET', '/api/v1/admin/customers'],
            ['GET', '/api/v1/admin/staff'],
            ['GET', '/api/v1/admin/promotions'],
            ['GET', '/api/v1/admin/logs'],
        ];

        foreach ($adminOnlyEndpoints as [$method, $endpoint]) {
            $response = match ($method) {
                'GET' => $this->getJson($endpoint),
                'POST' => $this->postJson($endpoint, []),
                'PUT' => $this->putJson($endpoint, []),
                'DELETE' => $this->deleteJson($endpoint),
            };

            $response->assertStatus(403, "Failed asserting $method $endpoint requires admin role");
        }

        // Staff-accessible endpoints (staff should get 200)
        $staffAccessibleEndpoints = [
            ['GET', '/api/v1/admin/products'],
            ['GET', '/api/v1/admin/categories'],
        ];

        foreach ($staffAccessibleEndpoints as [$method, $endpoint]) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(200, "Failed asserting staff can access $endpoint");
        }
    }

    public function test_all_admin_endpoints_require_authentication(): void
    {
        $endpoints = [
            ['GET', '/api/v1/admin/dashboard'],
            ['GET', '/api/v1/admin/orders'],
            ['GET', '/api/v1/admin/products'],
            ['GET', '/api/v1/admin/categories'],
            ['GET', '/api/v1/admin/customers'],
            ['GET', '/api/v1/admin/staff'],
            ['GET', '/api/v1/admin/promotions'],
            ['GET', '/api/v1/admin/logs'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = match ($method) {
                'GET' => $this->getJson($endpoint),
                'POST' => $this->postJson($endpoint, []),
                'PUT' => $this->putJson($endpoint, []),
                'DELETE' => $this->deleteJson($endpoint),
            };

            $response->assertStatus(401, "Failed asserting $method $endpoint requires authentication");
        }
    }
}
