<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ProductController.
 *
 * @requirement SHOP-001 to SHOP-028 Product catalog functionality
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all products.
     */
    public function test_can_fetch_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'in_stock'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /**
     * Test filtering products by category.
     */
    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(2)->create(); // Other category

        $response = $this->getJson('/api/v1/products?category=' . $category->slug);

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test filtering products by price range.
     */
    public function test_can_filter_products_by_price_range(): void
    {
        Product::factory()->create(['price_aud' => 15]);
        Product::factory()->create(['price_aud' => 25]);
        Product::factory()->create(['price_aud' => 50]);

        $response = $this->getJson('/api/v1/products?min_price=20&max_price=60');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test filtering products by stock availability.
     */
    public function test_can_filter_products_by_stock(): void
    {
        Product::factory()->count(3)->create(['stock' => 10]);
        Product::factory()->count(2)->create(['stock' => 0]);

        $response = $this->getJson('/api/v1/products?in_stock=true');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test sorting products.
     */
    public function test_can_sort_products(): void
    {
        Product::factory()->create(['name' => 'Zebra Steak', 'price_aud' => 50]);
        Product::factory()->create(['name' => 'Apple Sausage', 'price_aud' => 20]);

        // Sort by name ascending
        $response = $this->getJson('/api/v1/products?sort=name&direction=asc');
        $response->assertStatus(200);
        $this->assertEquals('Apple Sausage', $response->json('data.0.name'));

        // Sort by price descending
        $response = $this->getJson('/api/v1/products?sort=price_aud&direction=desc');
        $response->assertStatus(200);
        $this->assertEquals(50, $response->json('data.0.price'));
    }

    /**
     * Test fetching featured products.
     */
    public function test_can_fetch_featured_products(): void
    {
        Product::factory()->count(3)->create(['is_featured' => true]);
        Product::factory()->count(2)->create(['is_featured' => false]);

        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test fetching single product by slug.
     */
    public function test_can_fetch_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', $product->name);
    }

    /**
     * Test 404 for non-existent product.
     */
    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/v1/products/non-existent-slug');

        $response->assertStatus(404);
    }

    /**
     * Test fetching related products.
     */
    public function test_can_fetch_related_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/products/' . $product->slug . '/related');

        $response->assertStatus(200);
        // Should not include the main product
        foreach ($response->json('data') as $relatedProduct) {
            $this->assertNotEquals($product->id, $relatedProduct['id']);
        }
    }

    /**
     * Test search products.
     */
    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Beef Ribeye Steak']);
        Product::factory()->create(['name' => 'Lamb Chops']);
        Product::factory()->create(['name' => 'Beef Mince']);

        $response = $this->getJson('/api/v1/products/search?q=beef');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test pagination.
     */
    public function test_products_are_paginated(): void
    {
        Product::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/products?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /**
     * Test inactive products are not shown.
     */
    public function test_inactive_products_are_not_shown(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);
        Product::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test product response includes all expected fields.
     *
     * @requirement SHOP-022 Product detail API returns full product data
     */
    public function test_product_response_includes_all_expected_fields(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_featured' => true,
            'sale_price_aud' => 25.00,
            'price_aud' => 30.00,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'short_description',
                    'price',
                    'price_formatted',
                    'original_price',
                    'original_price_formatted',
                    'discount_percentage',
                    'is_on_sale',
                    'stock',
                    'in_stock',
                    'unit',
                    'min_order_quantity',
                    'max_order_quantity',
                    'is_featured',
                    'is_active',
                    'sku',
                    'category',
                ],
            ]);
    }

    /**
     * Test product sale price calculation.
     *
     * @requirement SHOP-003 Product shows current price with sale info
     */
    public function test_product_sale_price_calculation(): void
    {
        $product = Product::factory()->create([
            'price_aud' => 50.00,
            'sale_price_aud' => 40.00,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_on_sale', true)
            ->assertJsonPath('data.discount_percentage', 20);

        // Check price values using assertions that handle type coercion
        $this->assertEquals(40, $response->json('data.price'));
        $this->assertEquals(50, $response->json('data.original_price'));
    }

    /**
     * Test products can be searched by description.
     *
     * @requirement SHOP-004 Search includes name and description
     */
    public function test_can_search_products_by_description(): void
    {
        Product::factory()->create([
            'name' => 'Regular Steak',
            'description' => 'Premium wagyu beef from Australia',
        ]);
        Product::factory()->create([
            'name' => 'Chicken Wings',
            'description' => 'Fresh free-range chicken',
        ]);

        $response = $this->getJson('/api/v1/products?search=wagyu');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Regular Steak', $response->json('data.0.name'));
    }

    /**
     * Test search minimum length requirement.
     *
     * @requirement SHOP-004 Search autocomplete requirements
     */
    public function test_search_requires_minimum_query_length(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products/search?q=a');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    /**
     * Test search returns formatted response.
     *
     * @requirement SHOP-004 Search autocomplete response format
     */
    public function test_search_returns_formatted_response(): void
    {
        Product::factory()->create([
            'name' => 'Premium Beef Steak',
            'price_aud' => 45.99,
            'stock' => 10,
        ]);

        $response = $this->getJson('/api/v1/products/search?q=beef');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'price',
                        'formatted_price',
                        'in_stock',
                    ],
                ],
            ]);
    }

    /**
     * Test featured products are limited.
     *
     * @requirement SHOP-024 Featured products with limit parameter
     */
    public function test_featured_products_respect_limit(): void
    {
        Product::factory()->count(10)->create([
            'is_featured' => true,
            'stock' => 10,
        ]);

        $response = $this->getJson('/api/v1/products/featured?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test featured products exclude out of stock items.
     *
     * @requirement SHOP-024 Featured products must be in stock
     */
    public function test_featured_products_exclude_out_of_stock(): void
    {
        Product::factory()->count(3)->create([
            'is_featured' => true,
            'stock' => 10,
        ]);
        Product::factory()->count(2)->create([
            'is_featured' => true,
            'stock' => 0,
        ]);

        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test related products exclude main product.
     *
     * @requirement SHOP-009 Related products functionality
     */
    public function test_related_products_exclude_main_product(): void
    {
        $category = Category::factory()->create();
        $mainProduct = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        Product::factory()->count(5)->create(['category_id' => $category->id, 'stock' => 10]);

        $response = $this->getJson('/api/v1/products/' . $mainProduct->slug . '/related');

        $response->assertStatus(200);
        $relatedIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($mainProduct->id, $relatedIds);
    }

    /**
     * Test related products respect limit.
     *
     * @requirement SHOP-009 Related products limit parameter
     */
    public function test_related_products_respect_limit(): void
    {
        $category = Category::factory()->create();
        $mainProduct = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        Product::factory()->count(10)->create(['category_id' => $category->id, 'stock' => 10]);

        $response = $this->getJson('/api/v1/products/' . $mainProduct->slug . '/related?limit=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test related products returns empty for non-existent product.
     */
    public function test_related_products_returns_empty_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/v1/products/non-existent-slug/related');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    /**
     * Test products can be filtered with multiple filters combined.
     *
     * @requirement SHOP-005, SHOP-006, SHOP-007 Combined filtering
     */
    public function test_can_combine_multiple_filters(): void
    {
        $category = Category::factory()->create();

        // Matches all criteria
        Product::factory()->create([
            'category_id' => $category->id,
            'price_aud' => 35,
            'stock' => 10,
        ]);

        // Wrong category
        Product::factory()->create([
            'price_aud' => 35,
            'stock' => 10,
        ]);

        // Wrong price
        Product::factory()->create([
            'category_id' => $category->id,
            'price_aud' => 100,
            'stock' => 10,
        ]);

        // Out of stock
        Product::factory()->create([
            'category_id' => $category->id,
            'price_aud' => 35,
            'stock' => 0,
        ]);

        $response = $this->getJson('/api/v1/products?category=' . $category->slug . '&min_price=20&max_price=50&in_stock=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test per_page maximum is enforced.
     *
     * @requirement SHOP-013 Pagination limits
     */
    public function test_per_page_maximum_is_enforced(): void
    {
        Product::factory()->count(60)->create();

        $response = $this->getJson('/api/v1/products?per_page=100');

        $response->assertStatus(200);
        // Max should be 48
        $this->assertLessThanOrEqual(48, count($response->json('data')));
    }

    /**
     * Test product includes category information.
     *
     * @requirement SHOP-003 Product card shows category
     */
    public function test_product_includes_category_information(): void
    {
        $category = Category::factory()->create(['name' => 'Premium Beef']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.category.name', 'Premium Beef')
            ->assertJsonPath('data.category.slug', $category->slug);
    }

    /**
     * Test products list includes category data.
     */
    public function test_products_list_includes_category_data(): void
    {
        $category = Category::factory()->create(['name' => 'Lamb']);
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.category.name', 'Lamb');
    }

    /**
     * Test deleted products are not shown.
     */
    public function test_soft_deleted_products_are_not_shown(): void
    {
        Product::factory()->count(3)->create();
        $deletedProduct = Product::factory()->create();
        $deletedProduct->delete();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
