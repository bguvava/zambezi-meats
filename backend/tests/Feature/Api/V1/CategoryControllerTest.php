<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for CategoryController.
 *
 * @requirement SHOP-004 to SHOP-005 Category functionality
 */
class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all categories.
     */
    public function test_can_fetch_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    /**
     * Test categories include product counts.
     */
    public function test_categories_include_product_counts(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $categoryData = collect($response->json('data'))->firstWhere('id', $category->id);
        $this->assertEquals(5, $categoryData['products_count']);
    }

    /**
     * Test fetching single category.
     */
    public function test_can_fetch_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/v1/categories/' . $category->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }

    /**
     * Test 404 for non-existent category.
     */
    public function test_returns_404_for_non_existent_category(): void
    {
        $response = $this->getJson('/api/v1/categories/non-existent-slug');

        $response->assertStatus(404);
    }

    /**
     * Test fetching products for category.
     */
    public function test_can_fetch_products_for_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);
        Product::factory()->count(3)->create(); // Other category

        $response = $this->getJson('/api/v1/categories/' . $category->slug . '/products');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test inactive categories are not shown.
     */
    public function test_inactive_categories_are_not_shown(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test categories are sorted by sort_order.
     */
    public function test_categories_are_sorted_by_sort_order(): void
    {
        Category::factory()->create(['name' => 'Third', 'sort_order' => 3]);
        Category::factory()->create(['name' => 'First', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Second', 'sort_order' => 2]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $this->assertEquals('First', $response->json('data.0.name'));
        $this->assertEquals('Second', $response->json('data.1.name'));
        $this->assertEquals('Third', $response->json('data.2.name'));
    }

    /**
     * Test category response includes all expected fields.
     *
     * @requirement SHOP-023 Category data serialization
     */
    public function test_category_response_includes_all_expected_fields(): void
    {
        $category = Category::factory()->create([
            'name' => 'Premium Meats',
            'description' => 'The finest quality meats',
        ]);

        $response = $this->getJson('/api/v1/categories/' . $category->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'is_active',
                    'sort_order',
                ],
            ]);
    }

    /**
     * Test categories only count active products.
     *
     * @requirement SHOP-002 Category product counts
     */
    public function test_category_product_count_only_includes_active_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->count(2)->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $categoryData = collect($response->json('data'))->firstWhere('id', $category->id);
        $this->assertEquals(3, $categoryData['products_count']);
    }

    /**
     * Test category products respect filters.
     *
     * @requirement SHOP-005, SHOP-006, SHOP-007 Category products filtering
     */
    public function test_category_products_respect_filters(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'price_aud' => 30, 'stock' => 10]);
        Product::factory()->create(['category_id' => $category->id, 'price_aud' => 60, 'stock' => 10]);
        Product::factory()->create(['category_id' => $category->id, 'price_aud' => 40, 'stock' => 0]);

        $response = $this->getJson('/api/v1/categories/' . $category->slug . '/products?min_price=20&max_price=50&in_stock=true');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test category products are paginated.
     *
     * @requirement SHOP-013 Pagination for category products
     */
    public function test_category_products_are_paginated(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(25)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/categories/' . $category->slug . '/products?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /**
     * Test category products can be sorted.
     *
     * @requirement SHOP-006 Sorting in category
     */
    public function test_category_products_can_be_sorted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Zebra Steak', 'price_aud' => 50]);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Apple Sausage', 'price_aud' => 20]);

        $response = $this->getJson('/api/v1/categories/' . $category->slug . '/products?sort=name&direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('Apple Sausage', $response->json('data.0.name'));
    }

    /**
     * Test multiple categories can be fetched efficiently.
     */
    public function test_can_fetch_multiple_categories_with_product_counts(): void
    {
        $categories = Category::factory()->count(5)->create();
        foreach ($categories as $category) {
            Product::factory()->count(3)->create(['category_id' => $category->id]);
        }

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        foreach ($response->json('data') as $categoryData) {
            $this->assertEquals(3, $categoryData['products_count']);
        }
    }

    /**
     * Test category products returns 404 for non-existent category.
     */
    public function test_category_products_returns_404_for_non_existent_category(): void
    {
        $response = $this->getJson('/api/v1/categories/non-existent/products');

        $response->assertStatus(404);
    }

    /**
     * Test empty category returns empty products list.
     */
    public function test_empty_category_returns_empty_products_list(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/v1/categories/' . $category->slug . '/products');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }
}
