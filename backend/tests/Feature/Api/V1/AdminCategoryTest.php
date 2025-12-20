<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin Category Management Tests.
 *
 * @requirement PROD-001 Categories listing page
 * @requirement PROD-002 Category CRUD operations
 * @requirement PROD-016 Category slug generation
 * @requirement PROD-019 Delete protection
 */
class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $customer;

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

        // Fake storage
        Storage::fake('public');
    }

    /**
     * Test get all categories.
     *
     * @requirement PROD-001 Categories listing
     */
    public function test_admin_can_get_all_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'categories' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image',
                        'sort_order',
                        'is_active',
                        'products_count',
                    ],
                ],
            ])
            ->assertJsonCount(5, 'categories');
    }

    public function test_categories_are_ordered_by_sort_order(): void
    {
        Category::factory()->create(['name' => 'Category C', 'sort_order' => 3]);
        Category::factory()->create(['name' => 'Category A', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Category B', 'sort_order' => 2]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/categories');

        $response->assertOk();

        $categories = $response->json('categories');

        // Assert ordered by sort_order
        $this->assertEquals('Category A', $categories[0]['name']);
        $this->assertEquals('Category B', $categories[1]['name']);
        $this->assertEquals('Category C', $categories[2]['name']);
    }

    public function test_categories_include_products_count(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/categories');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $category->id,
                'products_count' => 3,
            ]);
    }

    /**
     * Test category creation.
     *
     * @requirement PROD-002 Category CRUD operations
     * @requirement PROD-016 Slug generation
     */
    public function test_admin_can_create_category(): void
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => 'Category description',
            'sort_order' => 1,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'category' => [
                    'id',
                    'name',
                    'slug',
                ],
            ]);

        // Assert category exists in database
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'description' => 'Category description',
        ]);

        // Assert slug is auto-generated
        $category = Category::where('name', 'New Category')->first();
        $this->assertEquals('new-category', $category->slug);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'category_created',
            'model_type' => Category::class,
            'model_id' => $category->id,
        ]);
    }

    public function test_staff_can_create_category(): void
    {
        $categoryData = [
            'name' => 'Staff Category',
            'description' => 'Created by staff',
        ];

        $response = $this->actingAs($this->staff, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertCreated();

        $this->assertDatabaseHas('categories', [
            'name' => 'Staff Category',
        ]);
    }

    public function test_customer_cannot_create_category(): void
    {
        $categoryData = [
            'name' => 'Customer Category',
        ];

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertForbidden();

        $this->assertDatabaseMissing('categories', [
            'name' => 'Customer Category',
        ]);
    }

    public function test_create_category_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.name', fn($value) => !empty($value));
    }

    public function test_create_category_validates_unique_name(): void
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $categoryData = [
            'name' => 'Existing Category', // Duplicate name
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.name', fn($value) => !empty($value));
    }

    public function test_create_category_validates_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'existing-slug']);

        $categoryData = [
            'name' => 'New Category',
            'slug' => 'existing-slug', // Duplicate slug
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.slug', fn($value) => !empty($value));
    }

    public function test_create_category_validates_parent_id_exists(): void
    {
        $categoryData = [
            'name' => 'Child Category',
            'parent_id' => 999, // Non-existent parent
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.parent_id', fn($value) => !empty($value));
    }

    public function test_create_category_validates_description_max_length(): void
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => str_repeat('a', 1001), // 1001 characters
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.description', fn($value) => !empty($value));
    }

    /**
     * Test category image upload.
     */
    public function test_admin_can_create_category_with_image(): void
    {
        $image = UploadedFile::fake()->image('category.jpg', 800, 600)->size(1024);

        $categoryData = [
            'name' => 'Category With Image',
            'image' => $image,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertCreated();

        $category = Category::where('name', 'Category With Image')->first();

        // Assert image path saved
        $this->assertNotNull($category->image);

        // Assert image file exists in storage
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_create_category_validates_image_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $categoryData = [
            'name' => 'Category With Invalid Image',
            'image' => $invalidFile,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.image', fn($value) => !empty($value));
    }

    public function test_create_category_validates_image_size(): void
    {
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3072); // 3MB

        $categoryData = [
            'name' => 'Category With Large Image',
            'image' => $largeImage,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    /**
     * Test category hierarchy.
     */
    public function test_admin_can_create_child_category(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent Category']);

        $childData = [
            'name' => 'Child Category',
            'parent_id' => $parent->id,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', $childData);

        $response->assertCreated();

        // Assert parent relationship
        $child = Category::where('name', 'Child Category')->first();
        $this->assertEquals($parent->id, $child->parent_id);
    }

    /**
     * Test category update.
     *
     * @requirement PROD-002 Update category
     */
    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Category updated successfully.',
            ]);

        // Assert database updated
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'category_updated',
            'model_type' => Category::class,
            'model_id' => $category->id,
        ]);
    }

    public function test_staff_can_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->staff, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", [
                'name' => 'Staff Updated',
            ]);

        $response->assertOk();
    }

    public function test_customer_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->customer, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", [
                'name' => 'Customer Updated',
            ]);

        $response->assertForbidden();
    }

    public function test_update_category_can_change_image(): void
    {
        $oldImage = UploadedFile::fake()->image('old.jpg')->size(1024);

        $category = Category::factory()->create();

        // First, create with image
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Category To Update',
                'image' => $oldImage,
            ]);

        $category = Category::where('name', 'Category To Update')->first();
        $oldImagePath = $category->image;

        // Now update with new image
        $newImage = UploadedFile::fake()->image('new.jpg')->size(1024);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", [
                'image' => $newImage,
            ]);

        $response->assertOk();

        $category->refresh();

        // Assert new image path is different
        $this->assertNotEquals($oldImagePath, $category->image);

        // Assert new image exists
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_update_category_validates_unique_name(): void
    {
        Category::factory()->create(['name' => 'Existing Name']);
        $category = Category::factory()->create(['name' => 'Current Name']);

        $updateData = [
            'name' => 'Existing Name', // Duplicate
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.name', fn($value) => !empty($value));
    }

    public function test_update_category_prevents_self_reference(): void
    {
        $category = Category::factory()->create();

        $updateData = [
            'parent_id' => $category->id, // Self-reference
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/categories/{$category->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('error.errors.parent_id', fn($value) => !empty($value));
    }

    /**
     * Test category deletion.
     *
     * @requirement PROD-019 Delete protection
     */
    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);

        // Assert category deleted from database
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);

        // Assert activity log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'category_deleted',
            'model_type' => Category::class,
        ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $category = Category::factory()->create();

        // Add products to category
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Cannot delete category with products. Move or delete products first.',
            ]);

        // Assert category still exists
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_customer_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->customer, 'sanctum')
            ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertForbidden();

        // Assert category still exists
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test unauthenticated access.
     */
    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_category(): void
    {
        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Test Category',
        ]);

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertUnauthorized();
    }
}
