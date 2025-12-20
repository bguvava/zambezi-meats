<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests for CartController.
 *
 * @requirement CART-001 to CART-023 Cart functionality
 */
class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test unauthenticated users cannot access cart.
     */
    public function test_unauthenticated_users_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(401);
    }

    /**
     * Test fetching user's cart.
     */
    public function test_can_fetch_cart(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/cart');

        // 200 if cart exists, 201 if newly created (firstOrCreate)
        $this->assertTrue(in_array($response->status(), [200, 201]));
        $response->assertJsonStructure([
            'data' => [
                'items',
                'item_count',
                'subtotal',
                'subtotal_formatted',
            ],
        ]);
    }

    /**
     * Test adding item to cart.
     */
    public function test_can_add_item_to_cart(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2.5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.item_count'));
    }

    /**
     * Test adding item increases quantity if already in cart.
     */
    public function test_adding_existing_item_increases_quantity(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);

        // Add item first time
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Add same item again
        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1.5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(3.5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test cannot add more than available stock.
     */
    public function test_cannot_add_more_than_available_stock(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Insufficient stock available.');
    }

    /**
     * Test cannot add inactive product.
     */
    public function test_cannot_add_inactive_product(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test updating cart item quantity.
     */
    public function test_can_update_cart_item_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 50]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->putJson('/api/v1/cart/items/' . $cartItem->id, [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test cannot update to exceed stock.
     */
    public function test_cannot_update_to_exceed_stock(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 5]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->putJson('/api/v1/cart/items/' . $cartItem->id, [
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test removing item from cart.
     */
    public function test_can_remove_item_from_cart(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create();
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->deleteJson('/api/v1/cart/items/' . $cartItem->id);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('data.item_count'));
    }

    /**
     * Test clearing entire cart.
     */
    public function test_can_clear_cart(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => $product1->price_aud,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => $product2->price_aud,
        ]);

        $response = $this->deleteJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cart cleared successfully.');

        $this->assertDatabaseCount('cart_items', 0);
    }

    /**
     * Test validating cart items.
     */
    public function test_can_validate_cart(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->postJson('/api/v1/cart/validate');

        $response->assertStatus(200)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('issues', []);
    }

    /**
     * Test validation detects stock issues.
     */
    public function test_validation_detects_stock_issues(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 15, // More than stock
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->postJson('/api/v1/cart/validate');

        $response->assertStatus(200)
            ->assertJsonPath('valid', false);

        $issues = $response->json('issues');
        $this->assertCount(1, $issues);
        $this->assertEquals('insufficient_stock', $issues[0]['type']);
    }

    /**
     * Test validation detects price changes.
     */
    public function test_validation_detects_price_changes(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 25.00, 'stock' => 50]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 20.00, // Old price
        ]);

        $response = $this->postJson('/api/v1/cart/validate');

        $response->assertStatus(200);
        $issues = $response->json('issues');
        $priceIssue = collect($issues)->firstWhere('type', 'price_changed');
        $this->assertNotNull($priceIssue);
    }

    /**
     * Test syncing cart from localStorage.
     */
    public function test_can_sync_cart_from_localstorage(): void
    {
        Sanctum::actingAs($this->user);

        $product1 = Product::factory()->create(['stock' => 50]);
        $product2 = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.item_count'));
    }

    /**
     * Test sync caps quantity at available stock.
     */
    public function test_sync_caps_quantity_at_available_stock(): void
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 20],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test sync skips inactive products.
     */
    public function test_sync_skips_inactive_products(): void
    {
        Sanctum::actingAs($this->user);

        $activeProduct = Product::factory()->create(['stock' => 50]);
        $inactiveProduct = Product::factory()->create(['is_active' => false, 'stock' => 50]);

        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $activeProduct->id, 'quantity' => 2],
                ['product_id' => $inactiveProduct->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.item_count'));
    }

    /**
     * Test save for later moves item to wishlist.
     * @requirement CART-022 Implement "Save for Later"
     * @group wishlist
     */
    public function test_can_save_item_for_later(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 50]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->postJson("/api/v1/cart/items/{$cartItem->id}/save-for-later");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Item moved to wishlist.');

        // Verify item removed from cart
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    /**
     * Test save for later with non-existent item.
     */
    public function test_save_for_later_not_found(): void
    {
        Sanctum::actingAs($this->user);

        // Ensure user has a cart
        Cart::create(['user_id' => $this->user->id]);

        $response = $this->postJson('/api/v1/cart/items/99999/save-for-later');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cart item not found.');
    }

    /**
     * Test save for later removes item from cart.
     * @group wishlist
     */
    public function test_save_for_later_removes_item_from_cart(): void
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 50]);

        // Add to cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $this->assertDatabaseCount('cart_items', 1);

        $response = $this->postJson("/api/v1/cart/items/{$cartItem->id}/save-for-later");

        $response->assertStatus(200);

        // Item should be removed from cart
        $this->assertDatabaseCount('cart_items', 0);
    }

    /**
     * Test cart subtotal calculation.
     * @requirement CART-005 Calculate and display subtotal
     */
    public function test_cart_calculates_subtotal_correctly(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);

        $product1 = Product::factory()->create(['price_aud' => 10.00, 'stock' => 50]);
        $product2 = Product::factory()->create(['price_aud' => 25.50, 'stock' => 50]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => $product1->price_aud,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1.5,
            'unit_price' => $product2->price_aud,
        ]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(200);

        // Expected: (2 * 10) + (1.5 * 25.50) = 20 + 38.25 = 58.25
        $this->assertEquals(58.25, $response->json('data.subtotal'));
    }

    /**
     * Test update non-existent cart item returns 404.
     */
    public function test_update_nonexistent_item_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        // Ensure user has a cart
        Cart::create(['user_id' => $this->user->id]);

        $response = $this->putJson('/api/v1/cart/items/99999', [
            'quantity' => 5,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cart item not found.');
    }

    /**
     * Test remove non-existent cart item returns 404.
     */
    public function test_remove_nonexistent_item_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        // Ensure user has a cart
        Cart::create(['user_id' => $this->user->id]);

        $response = $this->deleteJson('/api/v1/cart/items/99999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Cart item not found.');
    }

    /**
     * Test validation detects unavailable products.
     * @requirement CART-013 Validate stock on checkout
     */
    public function test_validation_detects_unavailable_products(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        // Deactivate product after adding to cart
        $product->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/cart/validate');

        $response->assertStatus(200)
            ->assertJsonPath('valid', false);

        $issues = $response->json('issues');
        $this->assertCount(1, $issues);
        $this->assertEquals('unavailable', $issues[0]['type']);
    }

    /**
     * Test validation returns valid items list.
     */
    public function test_validation_returns_valid_items_list(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $validProduct = Product::factory()->create(['stock' => 50]);
        $invalidProduct = Product::factory()->create(['stock' => 1]);

        $validItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $validProduct->id,
            'quantity' => 2,
            'unit_price' => $validProduct->price_aud,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $invalidProduct->id,
            'quantity' => 10, // More than stock
            'unit_price' => $invalidProduct->price_aud,
        ]);

        $response = $this->postJson('/api/v1/cart/validate');

        $response->assertStatus(200)
            ->assertJsonPath('total_items', 2);

        $validItems = $response->json('valid_items');
        $this->assertContains($validItem->id, $validItems);
    }

    /**
     * Test adding to cart with zero quantity is rejected.
     */
    public function test_cannot_add_zero_quantity(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test adding to cart with negative quantity is rejected.
     */
    public function test_cannot_add_negative_quantity(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => -1,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test cart item count is correct.
     * @requirement CART-014 Update cart icon badge
     */
    public function test_cart_returns_correct_item_count(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);

        $product1 = Product::factory()->create(['stock' => 50]);
        $product2 = Product::factory()->create(['stock' => 50]);
        $product3 = Product::factory()->create(['stock' => 50]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => $product1->price_aud,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => $product2->price_aud,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product3->id,
            'quantity' => 3,
            'unit_price' => $product3->price_aud,
        ]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonPath('data.item_count', 3);
    }

    /**
     * Test sync merges with existing cart items (keeps higher quantity).
     */
    public function test_sync_merges_existing_items_with_higher_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 50]);

        // Create cart with existing item
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price_aud,
        ]);

        // Sync with lower quantity - should keep higher
        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('data.items.0.quantity'));

        // Sync with higher quantity - should update
        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test sync rejects items with quantity below minimum.
     */
    public function test_sync_rejects_items_below_minimum_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create(['stock' => 50]);

        // Quantity below 0.1 should be rejected by validation
        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 0.05],
            ],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test empty cart returns proper structure.
     * @requirement CART-017 Handle empty cart state
     */
    public function test_empty_cart_returns_proper_structure(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/cart');

        // 200 for existing cart, 201 for newly created
        $this->assertTrue(in_array($response->status(), [200, 201]));
        $response->assertJsonPath('data.item_count', 0)
            ->assertJsonPath('data.subtotal', 0)
            ->assertJsonPath('data.items', []);
    }

    /**
     * Test cart includes product data.
     */
    public function test_cart_includes_product_data(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 50]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(200);
        $this->assertArrayHasKey('product', $response->json('data.items.0'));
        $this->assertEquals($product->name, $response->json('data.items.0.product.name'));
    }

    /**
     * Test adding decimal quantity.
     * @requirement CART-003 Implement quantity adjustment (0.5kg increments)
     */
    public function test_can_add_decimal_quantity(): void
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1.5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1.5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test updating to decimal quantity.
     */
    public function test_can_update_to_decimal_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['stock' => 50]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->putJson('/api/v1/cart/items/' . $cartItem->id, [
            'quantity' => 2.5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(2.5, $response->json('data.items.0.quantity'));
    }

    /**
     * Test cart formatted subtotal.
     */
    public function test_cart_returns_formatted_subtotal(): void
    {
        Sanctum::actingAs($this->user);

        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 45.99, 'stock' => 50]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price_aud,
        ]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(200);
        $this->assertArrayHasKey('subtotal_formatted', $response->json('data'));
    }

    /**
     * Test sync rejects non-existent product IDs via validation.
     */
    public function test_sync_rejects_nonexistent_products(): void
    {
        Sanctum::actingAs($this->user);

        $validProduct = Product::factory()->create(['stock' => 50]);

        // Non-existent product ID should fail validation
        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $validProduct->id, 'quantity' => 2],
                ['product_id' => 99999, 'quantity' => 3], // Non-existent
            ],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test multiple users have separate carts.
     */
    public function test_users_have_separate_carts(): void
    {
        $user2 = User::factory()->create();

        // Add item to first user's cart
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['stock' => 50]);
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Check second user's cart is empty
        Sanctum::actingAs($user2);
        $response = $this->getJson('/api/v1/cart');

        // 200 for existing cart, 201 for newly created
        $this->assertTrue(in_array($response->status(), [200, 201]));
        $response->assertJsonPath('data.item_count', 0);
    }
}
