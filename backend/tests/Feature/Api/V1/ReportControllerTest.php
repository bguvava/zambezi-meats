<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test suite for ReportController.
 *
 * @requirement RPT-022 Write reports module tests
 */
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    // ==================== Authentication Tests ====================

    /** @test */
    public function test_reports_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin/reports/dashboard');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_reports_dashboard_requires_admin_role(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/admin/reports/dashboard');

        $response->assertStatus(403);
    }

    // ==================== Dashboard Tests ====================

    /** @test */
    public function test_admin_can_access_reports_dashboard(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range' => ['start', 'end', 'period_days'],
                    'quick_stats' => [
                        'revenue' => ['value', 'change'],
                        'orders' => ['value', 'change'],
                        'customers' => ['value', 'change'],
                        'avg_order' => ['value', 'change'],
                    ],
                    'top_products',
                    'top_customers',
                ],
            ]);
    }

    /** @test */
    public function test_dashboard_with_date_preset(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=today');

        $response->assertStatus(200)
            ->assertJsonPath('data.date_range.start', now()->toDateString())
            ->assertJsonPath('data.date_range.end', now()->toDateString());
    }

    /** @test */
    public function test_dashboard_with_custom_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        $startDate = now()->subDays(7)->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/v1/admin/reports/dashboard?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonPath('data.date_range.start', $startDate)
            ->assertJsonPath('data.date_range.end', $endDate);
    }

    /** @test */
    public function test_dashboard_calculates_percentage_changes(): void
    {
        Sanctum::actingAs($this->admin);

        // Create orders in current and previous periods
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $zone = DeliveryZone::factory()->create();

        // Current period order
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'total' => 100,
            'status' => 'delivered',
            'created_at' => now(),
        ]);

        // Previous period order
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'total' => 50,
            'status' => 'delivered',
            'created_at' => now()->subDays(60),
        ]);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'quick_stats' => [
                        'revenue' => ['value', 'change'],
                    ],
                ],
            ]);
    }

    // ==================== Sales Summary Tests ====================

    /** @test */
    public function test_admin_can_get_sales_summary_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/sales-summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'summary' => [
                        'total_revenue',
                        'total_orders',
                        'avg_order_value',
                        'total_items_sold',
                    ],
                    'revenue_by_status',
                    'daily_revenue',
                ],
            ]);
    }

    /** @test */
    public function test_sales_summary_with_orders(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price_aud' => 25]);
        $zone = DeliveryZone::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'total' => 100,
            'subtotal' => 90,
            'status' => 'delivered',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 4,
            'unit_price' => 25,
            'total_price' => 100,
        ]);

        $response = $this->getJson('/api/v1/admin/reports/sales-summary?preset=month');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_orders', 1);

        $this->assertEquals(100.0, (float) $response->json('data.summary.total_revenue'));
    }

    // ==================== Revenue Report Tests ====================

    /** @test */
    public function test_admin_can_get_revenue_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/revenue');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'group_by',
                    'periods',
                    'totals',
                ],
            ]);
    }

    /** @test */
    public function test_revenue_report_with_grouping(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/revenue?group_by=week');

        $response->assertStatus(200)
            ->assertJsonPath('data.group_by', 'week');
    }

    /** @test */
    public function test_revenue_report_by_month(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/revenue?group_by=month');

        $response->assertStatus(200)
            ->assertJsonPath('data.group_by', 'month');
    }

    // ==================== Orders Report Tests ====================

    /** @test */
    public function test_admin_can_get_orders_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'total_orders',
                    'by_status',
                    'recent_orders',
                ],
            ]);
    }

    /** @test */
    public function test_orders_report_shows_status_breakdown(): void
    {
        Sanctum::actingAs($this->admin);

        $zone = DeliveryZone::factory()->create();

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'pending',
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'delivered',
        ]);

        $response = $this->getJson('/api/v1/admin/reports/orders?preset=month');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_orders', 2);
    }

    // ==================== Products Report Tests ====================

    /** @test */
    public function test_admin_can_get_products_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'total_revenue',
                    'products',
                ],
            ]);
    }

    /** @test */
    public function test_products_report_with_sales_data(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'price_aud' => 50,
        ]);
        $zone = DeliveryZone::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'delivered',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 50,
            'total_price' => 100,
        ]);

        $response = $this->getJson('/api/v1/admin/reports/products?preset=month');

        $response->assertStatus(200);
    }

    // ==================== Categories Report Tests ====================

    /** @test */
    public function test_admin_can_get_categories_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'total_revenue',
                    'categories',
                ],
            ]);
    }

    // ==================== Top Products Tests ====================

    /** @test */
    public function test_admin_can_get_top_products_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/top-products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'top_by_revenue',
                    'top_by_quantity',
                ],
            ]);
    }

    /** @test */
    public function test_top_products_with_custom_limit(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/top-products?limit=5');

        $response->assertStatus(200);
    }

    // ==================== Low Performing Products Tests ====================

    /** @test */
    public function test_admin_can_get_low_performing_products(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/low-performing-products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'low_selling',
                    'no_sales',
                ],
            ]);
    }

    /** @test */
    public function test_low_performing_shows_products_without_sales(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/admin/reports/low-performing-products?preset=month');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['no_sales']]);
    }

    // ==================== Customers Report Tests ====================

    /** @test */
    public function test_admin_can_get_customers_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'summary' => [
                        'new_customers',
                        'active_customers',
                        'returning_customers',
                        'new_customer_rate',
                        'avg_spend',
                    ],
                    'top_customers',
                ],
            ]);
    }

    // ==================== Customer Acquisition Tests ====================

    /** @test */
    public function test_admin_can_get_customer_acquisition_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/customer-acquisition');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'group_by',
                    'total_new_customers',
                    'conversion_rate',
                    'customers_with_orders',
                    'by_period',
                ],
            ]);
    }

    /** @test */
    public function test_customer_acquisition_with_grouping(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/customer-acquisition?group_by=week');

        $response->assertStatus(200)
            ->assertJsonPath('data.group_by', 'week');
    }

    // ==================== Staff Report Tests ====================

    /** @test */
    public function test_admin_can_get_staff_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/staff');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'staff',
                ],
            ]);
    }

    /** @test */
    public function test_staff_report_includes_performance_metrics(): void
    {
        Sanctum::actingAs($this->admin);

        $staff = User::factory()->create(['role' => 'staff']);
        $zone = DeliveryZone::factory()->create();

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => $staff->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'delivered',
        ]);

        $response = $this->getJson('/api/v1/admin/reports/staff?preset=month');

        $response->assertStatus(200);

        $staffData = collect($response->json('data.staff'))
            ->firstWhere('id', $staff->id);

        $this->assertNotNull($staffData);
        $this->assertArrayHasKey('orders_processed', $staffData);
    }

    // ==================== Deliveries Report Tests ====================

    /** @test */
    public function test_admin_can_get_deliveries_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/deliveries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'summary' => [
                        'total_deliveries',
                        'completed_deliveries',
                        'on_time_deliveries',
                        'on_time_rate',
                        'avg_delivery_fee',
                    ],
                    'by_zone',
                ],
            ]);
    }

    // ==================== Inventory Report Tests ====================

    /** @test */
    public function test_admin_can_get_inventory_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'stock_levels' => [
                        'total_products',
                        'active_products',
                        'low_stock',
                        'out_of_stock',
                    ],
                    'movements',
                    'waste',
                    'turnover_rate',
                ],
            ]);
    }

    /** @test */
    public function test_inventory_report_with_stock_movements(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 100,
        ]);

        InventoryLog::factory()->create([
            'product_id' => $product->id,
            'type' => 'addition',
            'quantity' => 50,
            'stock_before' => 50,
            'stock_after' => 100,
        ]);

        $response = $this->getJson('/api/v1/admin/reports/inventory?preset=month');

        $response->assertStatus(200);
    }

    // ==================== Financial Report Tests ====================

    /** @test */
    public function test_admin_can_get_financial_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/financial');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'summary' => [
                        'gross_revenue',
                        'product_revenue',
                        'delivery_fees',
                        'discounts',
                        'refunds',
                        'net_revenue',
                    ],
                    'daily_breakdown',
                ],
            ]);
    }

    /** @test */
    public function test_financial_report_calculates_correctly(): void
    {
        Sanctum::actingAs($this->admin);

        $zone = DeliveryZone::factory()->create();

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'total' => 150,
            'subtotal' => 140,
            'delivery_fee' => 10,
            'discount' => 0,
            'status' => 'delivered',
        ]);

        $response = $this->getJson('/api/v1/admin/reports/financial?preset=month');

        $response->assertStatus(200);
        $this->assertEquals(150.0, (float) $response->json('data.summary.gross_revenue'));
    }

    // ==================== Payment Methods Report Tests ====================

    /** @test */
    public function test_admin_can_get_payment_methods_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/payment-methods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'total_orders',
                    'orders_by_status',
                ],
            ]);
    }

    /** @test */
    public function test_payment_methods_shows_order_status_breakdown(): void
    {
        Sanctum::actingAs($this->admin);

        $zone = DeliveryZone::factory()->create();

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'delivered',
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'cancelled',
        ]);

        $response = $this->getJson('/api/v1/admin/reports/payment-methods?preset=month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'date_range',
                    'total_orders',
                    'orders_by_status',
                ],
            ]);
    }

    // ==================== Export Tests ====================

    /** @test */
    public function test_admin_can_export_sales_summary_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/sales-summary/export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'report_type',
                    'action',
                    'date_range',
                    'generated_at',
                    'report_data',
                    'pdf_url',
                ],
            ]);
    }

    /** @test */
    public function test_admin_can_export_with_download_action(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/revenue/export?action=download');

        $response->assertStatus(200)
            ->assertJsonPath('data.action', 'download');
    }

    /** @test */
    public function test_export_invalid_report_type_returns_error(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/invalid-type/export');

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid report type');
    }

    /** @test */
    public function test_can_export_all_valid_report_types(): void
    {
        Sanctum::actingAs($this->admin);

        $validTypes = [
            'sales-summary',
            'revenue',
            'orders',
            'products',
            'categories',
            'customers',
            'staff',
            'deliveries',
            'inventory',
            'financial',
            'payment-methods',
        ];

        foreach ($validTypes as $type) {
            $response = $this->getJson("/api/v1/admin/reports/{$type}/export");

            $response->assertStatus(200)
                ->assertJsonPath('data.report_type', $type);
        }
    }

    // ==================== Date Range Preset Tests ====================

    /** @test */
    public function test_preset_yesterday_works(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=yesterday');

        $response->assertStatus(200)
            ->assertJsonPath('data.date_range.start', now()->subDay()->toDateString());
    }

    /** @test */
    public function test_preset_last_week_works(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=last_week');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_preset_last_month_works(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=last_month');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_preset_year_works(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=year');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_preset_last_year_works(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/reports/dashboard?preset=last_year');

        $response->assertStatus(200);
    }
}
