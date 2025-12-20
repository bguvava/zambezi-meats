<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin Product Management Tests.
 *
 * @requirement PROD-003 Products listing page
 * @requirement PROD-004 Product search
 * @requirement PROD-005 Product filters
 * @requirement PROD-006 Product form validation
 * @requirement PROD-007 Image upload
 * @requirement PROD-008 Product edit
 * @requirement PROD-009 Product delete
 * @requirement PROD-010 Stock management
 * @requirement PROD-011 Low stock alerts
 * @requirement PROD-017 Product view
 */
class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@test.com',
        ]);

        $this->staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff@test.com',
        ]);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'email' => 'customer@test.com',
        ]);

        // Create category
        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        // Fake storage
        Storage::fake('public');
    }

    /**
     * Test get all products endpoint.
     *
     * @requirement PROD-003 Products listing
     */
    public function test_admin_can_get_all_products(): void
    {
        Product::factory()->count(5)->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'products' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'sku',
                        'price_aud',
                        'stock',
                        'category',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals(5, count($response->json('products')));
    }

    /**
     * Test product search functionality.
     *
     * @requirement PROD-004 Product search
     */
    public function test_admin_can_search_products(): void
    {
        Product::factory()->create([
            'name' => 'Premium Beef Steak',
            'sku' => 'SKU-BEEF001',
            'category_id' => $this->category->id,
        ]);

        Product::factory()->create([
            'name' => 'Chicken Breast',
            'sku' => 'SKU-CHIC001',
            'category_id' => $this->category->id,
        ]);

        // Search by name
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?search=beef');

        $response->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonFragment(['name' => 'Premium Beef Steak']);

        // Search by SKU
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?search=CHIC001');

        $response->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonFragment(['sku' => 'SKU-CHIC001']);
    }

    /**
     * Test product filters.
     *
     * @requirement PROD-005 Product filters (category, status, stock)
     */
    public function test_admin_can_filter_products_by_category(): void
    {
        $category2 = Category::factory()->create(['name' => 'Category 2']);

        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?category_id=' . $this->category->id);

        $response->assertOk()
            ->assertJsonCount(3, 'products');
    }

    public function test_admin_can_filter_products_by_status(): void
    {
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        Product::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'is_active' => false,
        ]);

        // Filter active
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?status=active');

        $response->assertOk()
            ->assertJsonCount(3, 'products');

        // Filter inactive
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?status=inactive');

        $response->assertOk()
            ->assertJsonCount(2, 'products');
    }

    public function test_admin_can_filter_products_by_stock_status(): void
    {
        // Out of stock (0)
        Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 0,
        ]);

        // Low stock (1-10)
        Product::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'stock' => 5,
        ]);

        // In stock (>10)
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        // Filter out of stock
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?stock_status=out_of_stock');

        $response->assertOk()
            ->assertJsonCount(1, 'products');

        // Filter low stock
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?stock_status=low_stock');

        $response->assertOk()
            ->assertJsonCount(2, 'products');

        // Filter in stock
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?stock_status=in_stock');

        $response->assertOk()
            ->assertJsonCount(3, 'products');
    }

    /**
     * Test product creation.
     *
     * @requirement PROD-006 Product form validation
     */
    public function test_admin_can_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Product description',
            'short_description' => 'Short desc',
            'category_id' => $this->category->id,
            'price_aud' => 29.99,
            'sale_price_aud' => 24.99,
            'stock' => 100,
            'unit' => 'kg',
            'weight_kg' => 1.5,
            'is_active' => true,
            'is_featured' => false,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'product' => [
                    'id',
                    'name',
                    'slug',
                    'sku',
                    'category',
                ],
            ]);

        // Assert product exists in database
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price_aud' => 29.99,
        ]);

        // Assert slug is auto-generated
        $product = Product::where('name', 'New Product')->first();
        $this->assertEquals('new-product', $product->slug);

        // Assert SKU is auto-generated
        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-', $product->sku);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product_created',
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);
    }

    public function test_staff_can_create_product(): void
    {
        $productData = [
            'name' => 'Staff Product',
            'category_id' => $this->category->id,
            'price_aud' => 19.99,
            'stock' => 50,
            'unit' => 'piece',
        ];

        $response = $this->actingAs($this->staff, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertCreated();

        $this->assertDatabaseHas('products', [
            'name' => 'Staff Product',
        ]);
    }

    public function test_customer_cannot_create_product(): void
    {
        $productData = [
            'name' => 'Customer Product',
            'category_id' => $this->category->id,
            'price_aud' => 19.99,
            'stock' => 50,
            'unit' => 'kg',
        ];

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertForbidden();

        $this->assertDatabaseMissing('products', [
            'name' => 'Customer Product',
        ]);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ]
            ])
            ->assertJsonPath('error.errors.name', fn($value) => !empty($value))
            ->assertJsonPath('error.errors.category_id', fn($value) => !empty($value))
            ->assertJsonPath('error.errors.price_aud', fn($value) => !empty($value))
            ->assertJsonPath('error.errors.stock', fn($value) => !empty($value));
    }

    public function test_create_product_validates_price_range(): void
    {
        $productData = [
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'price_aud' => -10.00,
            'stock' => 50,
            'unit' => 'kg',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.price_aud', fn($value) => !empty($value));
    }

    public function test_create_product_validates_sale_price_less_than_regular_price(): void
    {
        $productData = [
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'price_aud' => 20.00,
            'sale_price_aud' => 25.00, // Sale price higher than regular price
            'stock' => 50,
            'unit' => 'kg',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.sale_price_aud', fn($value) => !empty($value));
    }

    public function test_create_product_validates_unique_slug(): void
    {
        Product::factory()->create([
            'name' => 'Existing Product',
            'slug' => 'existing-product',
            'category_id' => $this->category->id,
        ]);

        $productData = [
            'name' => 'New Product',
            'slug' => 'existing-product', // Duplicate slug
            'category_id' => $this->category->id,
            'price_aud' => 20.00,
            'stock' => 50,
            'unit' => 'kg',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.slug', fn($value) => !empty($value));
    }

    /**
     * Test product image upload.
     *
     * @requirement PROD-007 Image upload
     */
    public function test_admin_can_create_product_with_images(): void
    {
        $image1 = UploadedFile::fake()->image('product1.jpg', 800, 600)->size(1024);
        $image2 = UploadedFile::fake()->image('product2.jpg', 800, 600)->size(1024);

        $productData = [
            'name' => 'Product With Images',
            'category_id' => $this->category->id,
            'price_aud' => 29.99,
            'stock' => 100,
            'unit' => 'kg',
            'images' => [$image1, $image2],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertCreated();

        $product = Product::where('name', 'Product With Images')->first();

        // Assert images saved to database
        $this->assertEquals(2, $product->images()->count());

        // Assert first image is primary
        $primaryImage = $product->images()->where('is_primary', true)->first();
        $this->assertNotNull($primaryImage);
        $this->assertEquals(0, $primaryImage->sort_order);

        // Assert image files exist in storage
        $images = $product->images;
        foreach ($images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_create_product_validates_max_5_images(): void
    {
        $images = [];
        for ($i = 0; $i < 6; $i++) {
            $images[] = UploadedFile::fake()->image("product{$i}.jpg")->size(1024);
        }

        $productData = [
            'name' => 'Product With Too Many Images',
            'category_id' => $this->category->id,
            'price_aud' => 29.99,
            'stock' => 100,
            'unit' => 'kg',
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.images', fn($value) => !empty($value));
    }

    public function test_create_product_validates_image_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $productData = [
            'name' => 'Product With Invalid Image',
            'category_id' => $this->category->id,
            'price_aud' => 29.99,
            'stock' => 100,
            'unit' => 'kg',
            'images' => [$invalidFile],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors', function ($errors) {
                return isset($errors['images.0']) && is_array($errors['images.0']) && count($errors['images.0']) > 0;
            });
    }

    public function test_create_product_validates_image_size(): void
    {
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3072); // 3MB

        $productData = [
            'name' => 'Product With Large Image',
            'category_id' => $this->category->id,
            'price_aud' => 29.99,
            'stock' => 100,
            'unit' => 'kg',
            'images' => [$largeImage],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.images.0', fn($value) => !empty($value));
    }

    /**
     * Test product update.
     *
     * @requirement PROD-008 Product edit
     */
    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price_aud' => 20.00,
            'category_id' => $this->category->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price_aud' => 25.00,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/products/{$product->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Product updated successfully.',
            ]);

        // Assert database updated
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price_aud' => 25.00,
        ]);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product_updated',
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);
    }

    public function test_staff_can_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->staff, 'sanctum')
            ->putJson("/api/v1/admin/products/{$product->id}", [
                'name' => 'Staff Updated',
            ]);

        $response->assertOk();
    }

    public function test_customer_cannot_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/admin/products/{$product->id}", [
                'name' => 'Customer Updated',
            ]);

        $response->assertForbidden();
    }

    public function test_update_product_can_add_images(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $newImage = UploadedFile::fake()->image('new-image.jpg')->size(1024);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/products/{$product->id}", [
                'images' => [$newImage],
            ]);

        $response->assertOk();

        $this->assertGreaterThan(0, $product->images()->count());
    }

    /**
     * Test product view.
     *
     * @requirement PROD-017 View product details
     */
    public function test_admin_can_view_single_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        ProductImage::factory()->count(2)->create(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/products/{$product->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'product' => [
                    'id',
                    'name',
                    'slug',
                    'sku',
                    'category',
                    'images',
                ],
            ])
            ->assertJsonCount(2, 'product.images');
    }

    /**
     * Test product deletion.
     *
     * @requirement PROD-009 Product delete (soft delete)
     */
    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);

        // Assert soft delete (is_active = false)
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'product_deleted',
            'model_type' => Product::class,
            'model_id' => $product->id,
        ]);
    }

    public function test_customer_cannot_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertForbidden();

        // Assert product still active
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test low stock alerts.
     *
     * @requirement PROD-011 Low stock alerts
     */
    public function test_admin_can_get_low_stock_products(): void
    {
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'stock' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products/low-stock?threshold=10');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'products',
                'count',
                'threshold',
            ])
            ->assertJsonCount(3, 'products')
            ->assertJsonFragment(['threshold' => 10]);
    }

    /**
     * Test stock management.
     *
     * @requirement PROD-010 Stock management
     */
    public function test_admin_can_adjust_stock_increase(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        $adjustData = [
            'quantity' => 20,
            'type' => 'increase',
            'reason' => 'New stock arrived',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/products/{$product->id}/adjust-stock", $adjustData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'stock_before' => 50,
                'stock_after' => 70,
            ]);

        // Assert database updated
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 70,
        ]);

        // Assert inventory log created
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'type' => 'addition',
            'quantity' => 20,
            'stock_before' => 50,
            'stock_after' => 70,
            'reason' => 'New stock arrived',
        ]);
    }

    public function test_admin_can_adjust_stock_decrease(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        $adjustData = [
            'quantity' => -15,
            'type' => 'decrease',
            'reason' => 'Damaged items',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/products/{$product->id}/adjust-stock", $adjustData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'stock_before' => 50,
                'stock_after' => 35,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 35,
        ]);

        // Assert inventory log created
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'type' => 'deduction',
            'quantity' => -15,
            'stock_before' => 50,
            'stock_after' => 35,
            'reason' => 'Damaged items',
        ]);
    }

    public function test_admin_can_adjust_stock_manual_adjustment(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50,
        ]);

        $adjustData = [
            'quantity' => 100,
            'type' => 'adjustment',
            'reason' => 'Stock count correction',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/products/{$product->id}/adjust-stock", $adjustData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'stock_before' => 50,
                'stock_after' => 100,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 100,
        ]);

        // Assert inventory log created
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 100,
            'stock_before' => 50,
            'stock_after' => 100,
            'reason' => 'Stock count correction',
        ]);
    }

    public function test_adjust_stock_validates_required_fields(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/products/{$product->id}/adjust-stock", []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.quantity', fn($value) => !empty($value))
            ->assertJsonPath('error.errors.type', fn($value) => !empty($value))
            ->assertJsonPath('error.errors.reason', fn($value) => !empty($value));
    }

    public function test_admin_can_get_stock_history(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        // Create some inventory logs
        InventoryLog::factory()->count(5)->create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/products/{$product->id}/stock-history");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'product',
                'history' => [
                    '*' => [
                        'id',
                        'type',
                        'quantity',
                        'stock_before',
                        'stock_after',
                        'reason',
                        'user',
                        'created_at',
                    ],
                ],
                'pagination',
            ])
            ->assertJsonCount(5, 'history');
    }

    /**
     * Test product image management.
     *
     * @requirement PROD-007 Manage product images
     */
    public function test_admin_can_delete_product_image(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}/images/{$image->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Image deleted successfully.',
            ]);

        $this->assertDatabaseMissing('product_images', [
            'id' => $image->id,
        ]);
    }

    public function test_delete_primary_image_sets_new_primary(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $image1 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $image2 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        // Delete primary image
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}/images/{$image1->id}");

        $response->assertOk();

        // Assert image2 became primary
        $this->assertDatabaseHas('product_images', [
            'id' => $image2->id,
            'is_primary' => true,
        ]);
    }

    public function test_admin_can_reorder_product_images(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $image1 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $image2 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'sort_order' => 1,
            'is_primary' => false,
        ]);

        $image3 = ProductImage::factory()->create([
            'product_id' => $product->id,
            'sort_order' => 2,
            'is_primary' => false,
        ]);

        // Reorder: image2 first, image1 second, image3 third
        $reorderData = [
            'image_ids' => [$image2->id, $image1->id, $image3->id],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/products/{$product->id}/images/reorder", $reorderData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Images reordered successfully.',
            ]);

        // Assert new sort order
        $this->assertDatabaseHas('product_images', [
            'id' => $image2->id,
            'sort_order' => 0,
            'is_primary' => true, // First image becomes primary
        ]);

        $this->assertDatabaseHas('product_images', [
            'id' => $image1->id,
            'sort_order' => 1,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('product_images', [
            'id' => $image3->id,
            'sort_order' => 2,
            'is_primary' => false,
        ]);
    }

    /**
     * Test pagination.
     */
    public function test_products_listing_is_paginated(): void
    {
        Product::factory()->count(20)->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/products?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'products')
            ->assertJsonFragment([
                'per_page' => 10,
                'total' => 20,
                'current_page' => 1,
                'last_page' => 2,
            ]);
    }

    /**
     * Test unauthenticated access.
     */
    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->getJson('/api/v1/admin/products');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_product(): void
    {
        $response = $this->postJson('/api/v1/admin/products', [
            'name' => 'Test Product',
        ]);

        $response->assertUnauthorized();
    }
}
