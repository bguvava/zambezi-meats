<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * InventoryController tests.
 *
 * @requirement INV-018 Write inventory module tests
 */
class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
            'meta' => ['min_stock' => 10],
        ]);
    }

    // =========================================================================
    // AUTHORIZATION TESTS
    // =========================================================================

    /** @test */
    public function admin_can_access_inventory_dashboard(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_products',
                    'low_stock_count',
                    'out_of_stock_count',
                    'waste_this_month',
                    'recent_movements',
                ],
            ]);
    }

    /** @test */
    public function staff_cannot_access_inventory_routes(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertForbidden();
    }

    /** @test */
    public function customer_cannot_access_inventory_routes(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_inventory_routes(): void
    {
        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertUnauthorized();
    }

    // =========================================================================
    // DASHBOARD TESTS
    // =========================================================================

    /** @test */
    public function dashboard_shows_correct_stock_counts(): void
    {
        Sanctum::actingAs($this->admin);

        // Create products with different stock levels
        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'meta' => ['min_stock' => 10],
        ]); // Low stock

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 0,
        ]); // Out of stock

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals(3, $data['total_products']);
        $this->assertEquals(1, $data['low_stock_count']);
        $this->assertEquals(1, $data['out_of_stock_count']);
    }

    /** @test */
    public function dashboard_shows_waste_this_month(): void
    {
        Sanctum::actingAs($this->admin);

        // Create waste logs for this month
        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'quantity' => 5,
            'total_cost' => 50.00,
        ]);

        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'quantity' => 3,
            'total_cost' => 30.00,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertOk();
        $data = $response->json('data.waste_this_month');

        $this->assertEquals(8, $data['quantity']);
        $this->assertEquals(80, $data['value']);
    }

    /** @test */
    public function dashboard_shows_recent_movements(): void
    {
        Sanctum::actingAs($this->admin);

        // Create inventory logs
        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 10,
            'stock_before' => 40,
            'stock_after' => 50,
            'reason' => 'Stock received',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/dashboard');

        $response->assertOk();
        $movements = $response->json('data.recent_movements');

        $this->assertCount(1, $movements);
        $this->assertEquals('addition', $movements[0]['type']);
        $this->assertEquals(10, $movements[0]['quantity']);
    }

    // =========================================================================
    // INVENTORY LIST TESTS
    // =========================================================================

    /** @test */
    public function can_get_inventory_list(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory');

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
    public function can_filter_inventory_by_category(): void
    {
        Sanctum::actingAs($this->admin);

        $otherCategory = Category::factory()->create();
        Product::factory()->create(['category_id' => $otherCategory->id]);

        $response = $this->getJson("/api/v1/admin/inventory?category_id={$this->category->id}");

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $item) {
            $this->assertEquals($this->category->id, $item['category_id']);
        }
    }

    /** @test */
    public function can_filter_inventory_by_low_stock_status(): void
    {
        Sanctum::actingAs($this->admin);

        // Create low stock product
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'meta' => ['min_stock' => 10],
        ]);

        $response = $this->getJson('/api/v1/admin/inventory?status=low');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertLessThanOrEqual($item['meta']['min_stock'] ?? 10, $item['stock']);
            $this->assertGreaterThan(0, $item['stock']);
        }
    }

    /** @test */
    public function can_filter_inventory_by_out_of_stock(): void
    {
        Sanctum::actingAs($this->admin);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 0,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory?status=out');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $item) {
            $this->assertEquals(0, $item['stock']);
        }
    }

    /** @test */
    public function can_search_inventory(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory?search=' . substr($this->product->name, 0, 5));

        $response->assertOk();
        $data = $response->json('data');

        $this->assertNotEmpty($data);
    }

    // =========================================================================
    // PRODUCT INVENTORY DETAIL TESTS
    // =========================================================================

    /** @test */
    public function can_get_product_inventory_detail(): void
    {
        Sanctum::actingAs($this->admin);

        // Create some inventory logs for this product
        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 20,
            'stock_before' => 30,
            'stock_after' => 50,
            'reason' => 'Stock receive',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/v1/admin/inventory/{$this->product->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'product',
                    'history',
                    'waste_logs',
                ],
            ]);

        $this->assertEquals($this->product->id, $response->json('data.product.id'));
    }

    /** @test */
    public function returns_404_for_nonexistent_product_inventory(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory/999999');

        $response->assertNotFound();
    }

    // =========================================================================
    // RECEIVE STOCK TESTS
    // =========================================================================

    /** @test */
    public function can_receive_stock(): void
    {
        Sanctum::actingAs($this->admin);

        $initialStock = $this->product->stock;

        $response = $this->postJson('/api/v1/admin/inventory/receive', [
            'product_id' => $this->product->id,
            'quantity' => 25,
            'supplier' => 'Test Supplier',
            'notes' => 'Weekly delivery',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Stock received successfully',
            ]);

        $this->product->refresh();
        $this->assertEquals($initialStock + 25, $this->product->stock);

        // Check inventory log was created
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 25,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock + 25,
        ]);
    }

    /** @test */
    public function receive_stock_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/inventory/receive', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'product_id',
                        'quantity',
                    ],
                ],
            ]);
    }

    /** @test */
    public function receive_stock_requires_positive_quantity(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/inventory/receive', [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'quantity',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // ADJUST STOCK TESTS
    // =========================================================================

    /** @test */
    public function can_adjust_stock(): void
    {
        Sanctum::actingAs($this->admin);

        $initialStock = $this->product->stock;
        $newQuantity = 35;

        $response = $this->postJson('/api/v1/admin/inventory/adjust', [
            'product_id' => $this->product->id,
            'new_quantity' => $newQuantity,
            'reason' => 'Stock count correction',
            'notes' => 'After physical inventory',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Stock adjusted successfully',
            ]);

        $this->product->refresh();
        $this->assertEquals($newQuantity, $this->product->stock);

        // Check inventory log was created
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'stock_before' => $initialStock,
            'stock_after' => $newQuantity,
        ]);
    }

    /** @test */
    public function adjust_stock_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/inventory/adjust', []);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'product_id',
                        'new_quantity',
                        'reason',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // MIN STOCK THRESHOLD TESTS
    // =========================================================================

    /** @test */
    public function can_update_min_stock_threshold(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/inventory/{$this->product->id}/min-stock", [
            'min_stock' => 15,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'min_stock' => 15,
                ],
            ]);

        $this->product->refresh();
        $this->assertEquals(15, $this->product->meta['min_stock']);
    }

    /** @test */
    public function min_stock_requires_valid_value(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/v1/admin/inventory/{$this->product->id}/min-stock", [
            'min_stock' => -5,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'errors' => [
                        'min_stock',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // INVENTORY HISTORY TESTS
    // =========================================================================

    /** @test */
    public function can_get_inventory_history(): void
    {
        Sanctum::actingAs($this->admin);

        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 10,
            'stock_before' => 40,
            'stock_after' => 50,
            'reason' => 'Stock received',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/history');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
                'meta',
            ]);
    }

    /** @test */
    public function can_filter_history_by_product(): void
    {
        Sanctum::actingAs($this->admin);

        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 10,
            'stock_before' => 40,
            'stock_after' => 50,
            'reason' => 'Stock received',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/v1/admin/inventory/history?product_id={$this->product->id}");

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $log) {
            $this->assertEquals($this->product->id, $log['product']['id']);
        }
    }

    /** @test */
    public function can_filter_history_by_type(): void
    {
        Sanctum::actingAs($this->admin);

        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'addition',
            'quantity' => 10,
            'stock_before' => 40,
            'stock_after' => 50,
            'reason' => 'Stock received',
            'user_id' => $this->admin->id,
        ]);

        InventoryLog::create([
            'product_id' => $this->product->id,
            'type' => 'deduction',
            'quantity' => 5,
            'stock_before' => 50,
            'stock_after' => 45,
            'reason' => 'Order',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/history?type=addition');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $log) {
            $this->assertEquals('addition', $log['type']);
        }
    }

    /** @test */
    public function can_filter_history_by_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory/history?start_date=' . now()->subDays(7)->toDateString() . '&end_date=' . now()->toDateString());

        $response->assertOk();
    }

    // =========================================================================
    // LOW STOCK TESTS
    // =========================================================================

    /** @test */
    public function can_get_low_stock_items(): void
    {
        Sanctum::actingAs($this->admin);

        // Create low stock product
        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'meta' => ['min_stock' => 10],
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/low-stock');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
                'count',
            ]);

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('low', $item['status']);
        }
    }

    // =========================================================================
    // ALERTS TESTS
    // =========================================================================

    /** @test */
    public function can_get_inventory_alerts(): void
    {
        Sanctum::actingAs($this->admin);

        // Create low stock and out of stock products
        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'meta' => ['min_stock' => 10],
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 0,
        ]);

        $response = $this->getJson('/api/v1/admin/inventory/alerts');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'low_stock',
                    'out_of_stock',
                ],
                'summary' => [
                    'low_stock_count',
                    'out_of_stock_count',
                    'total_alerts',
                ],
            ]);
    }

    // =========================================================================
    // WASTE MANAGEMENT TESTS
    // =========================================================================

    /** @test */
    public function can_get_waste_entries(): void
    {
        Sanctum::actingAs($this->admin);

        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->getJson('/api/v1/admin/waste');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
                'meta',
                'summary',
            ]);
    }

    /** @test */
    public function can_filter_waste_by_reason(): void
    {
        Sanctum::actingAs($this->admin);

        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'reason' => 'expired',
        ]);

        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'reason' => 'damaged',
        ]);

        $response = $this->getJson('/api/v1/admin/waste?reason=expired');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $log) {
            $this->assertEquals('expired', $log['reason']);
        }
    }

    /** @test */
    public function can_filter_waste_by_approval_status(): void
    {
        Sanctum::actingAs($this->admin);

        // Approved waste log
        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
        ]);

        // Pending waste log
        WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->getJson('/api/v1/admin/waste?approved=false');

        $response->assertOk();
        $data = $response->json('data');

        foreach ($data as $log) {
            $this->assertFalse($log['approved']);
        }
    }

    /** @test */
    public function can_approve_waste_entry(): void
    {
        Sanctum::actingAs($this->admin);

        $wasteLog = WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->putJson("/api/v1/admin/waste/{$wasteLog->id}/approve", [
            'approved' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Waste log approved',
            ]);

        $wasteLog->refresh();
        $this->assertNotNull($wasteLog->approved_at);
    }

    /** @test */
    public function can_reject_waste_entry(): void
    {
        Sanctum::actingAs($this->admin);

        $wasteLog = WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
        ]);

        $response = $this->putJson("/api/v1/admin/waste/{$wasteLog->id}/approve", [
            'approved' => false,
            'notes' => 'Incorrect quantity reported',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Waste log rejected',
            ]);

        $wasteLog->refresh();
        $this->assertNotNull($wasteLog->rejected_at);
    }

    /** @test */
    public function cannot_approve_already_reviewed_waste(): void
    {
        Sanctum::actingAs($this->admin);

        $wasteLog = WasteLog::factory()->create([
            'product_id' => $this->product->id,
            'logged_by' => $this->staff->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->putJson("/api/v1/admin/waste/{$wasteLog->id}/approve", [
            'approved' => true,
        ]);

        $response->assertStatus(400);
    }

    // =========================================================================
    // REPORT TESTS
    // =========================================================================

    /** @test */
    public function can_get_inventory_report(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory/report');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'current_stock',
                    'movements',
                    'waste',
                    'top_movements',
                ],
            ]);
    }

    /** @test */
    public function can_get_report_with_date_range(): void
    {
        Sanctum::actingAs($this->admin);

        $startDate = now()->subMonth()->toDateString();
        $endDate = now()->toDateString();

        $response = $this->getJson("/api/v1/admin/inventory/report?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk();
        $this->assertEquals($startDate, $response->json('data.period.start_date'));
        $this->assertEquals($endDate, $response->json('data.period.end_date'));
    }

    // =========================================================================
    // EXPORT TESTS
    // =========================================================================

    /** @test */
    public function can_export_inventory(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/inventory/export');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'summary',
                    'products',
                ],
            ]);
    }

    // =========================================================================
    // SECURITY TESTS
    // =========================================================================

    /** @test */
    public function all_inventory_endpoints_require_admin_role(): void
    {
        Sanctum::actingAs($this->staff);

        $endpoints = [
            ['GET', '/api/v1/admin/inventory/dashboard'],
            ['GET', '/api/v1/admin/inventory'],
            ['GET', '/api/v1/admin/inventory/history'],
            ['GET', '/api/v1/admin/inventory/low-stock'],
            ['GET', '/api/v1/admin/inventory/alerts'],
            ['GET', '/api/v1/admin/inventory/report'],
            ['GET', '/api/v1/admin/inventory/export'],
            ['POST', '/api/v1/admin/inventory/receive'],
            ['POST', '/api/v1/admin/inventory/adjust'],
            ['GET', '/api/v1/admin/waste'],
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
    public function all_inventory_endpoints_require_authentication(): void
    {
        $endpoints = [
            ['GET', '/api/v1/admin/inventory/dashboard'],
            ['GET', '/api/v1/admin/inventory'],
            ['GET', '/api/v1/admin/inventory/history'],
            ['GET', '/api/v1/admin/inventory/low-stock'],
            ['GET', '/api/v1/admin/inventory/alerts'],
            ['GET', '/api/v1/admin/waste'],
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
