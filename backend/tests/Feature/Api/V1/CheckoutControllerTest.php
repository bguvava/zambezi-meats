<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests for CheckoutController.
 *
 * @requirement CHK-003 to CHK-007 Address and delivery handling
 * @requirement CHK-015 Promo code validation
 * @requirement CHK-025 to CHK-027 Order creation
 */
class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Address Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test validating an address in a deliverable zone.
     *
     * @requirement CHK-006 Validate delivery zone
     */
    public function test_validates_address_in_delivery_zone(): void
    {
        DeliveryZone::factory()->melbourneCbd()->create();

        $response = $this->postJson('/api/v1/checkout/validate-address', [
            'postcode' => '3000',
            'suburb' => 'Melbourne',
            'state' => 'VIC',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('delivers', true)
            ->assertJsonStructure([
                'success',
                'delivers',
                'message',
                'zone' => [
                    'id',
                    'name',
                    'delivery_fee',
                    'estimated_days',
                ],
            ]);
    }

    /**
     * Test validating address that doesn't deliver.
     */
    public function test_validates_address_outside_delivery_zone(): void
    {
        // No delivery zones created, so nothing will match

        $response = $this->postJson('/api/v1/checkout/validate-address', [
            'postcode' => '9999',
            'suburb' => 'Remote Town',
            'state' => 'NT',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('delivers', false);
    }

    /**
     * Test address validation requires postcode.
     */
    public function test_address_validation_requires_postcode(): void
    {
        $response = $this->postJson('/api/v1/checkout/validate-address', [
            'suburb' => 'Melbourne',
            'state' => 'VIC',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.errors.postcode.0', 'Please enter your postcode.');
    }

    /**
     * Test address validation requires valid Australian postcode format.
     */
    public function test_address_validation_requires_valid_postcode_format(): void
    {
        $response = $this->postJson('/api/v1/checkout/validate-address', [
            'postcode' => '123', // Must be 4 digits
            'suburb' => 'Melbourne',
            'state' => 'VIC',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Delivery Fee Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test calculating delivery fee.
     *
     * @requirement CHK-007 Calculate and display delivery fee
     */
    public function test_calculates_delivery_fee(): void
    {
        DeliveryZone::factory()->melbourneCbd()->create();

        $response = $this->postJson('/api/v1/checkout/calculate-fee', [
            'postcode' => '3000',
            'suburb' => 'Melbourne',
            'subtotal' => 50.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'fee',
                'fee_formatted',
                'is_free',
                'zone_name',
                'estimated_days',
            ]);
    }

    /**
     * Test free delivery when over threshold.
     */
    public function test_calculates_free_delivery_over_threshold(): void
    {
        DeliveryZone::factory()->melbourneCbd()->create(); // $100 threshold

        $response = $this->postJson('/api/v1/checkout/calculate-fee', [
            'postcode' => '3000',
            'suburb' => 'Melbourne',
            'subtotal' => 150.00, // Over threshold
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('fee', 0)
            ->assertJsonPath('is_free', true);
    }

    /**
     * Test fee calculation shows amount needed for free delivery.
     */
    public function test_shows_amount_needed_for_free_delivery(): void
    {
        DeliveryZone::factory()->melbourneCbd()->create(); // $100 threshold

        $response = $this->postJson('/api/v1/checkout/calculate-fee', [
            'postcode' => '3000',
            'suburb' => 'Melbourne',
            'subtotal' => 80.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('is_free', false);

        // Check values with loose comparison due to JSON numeric handling
        $this->assertEquals(100, $response->json('free_delivery_threshold'));
        $this->assertEquals(20, $response->json('amount_to_free_delivery'));
    }

    /**
     * Test fee calculation for non-deliverable area.
     */
    public function test_fee_calculation_for_non_deliverable_area(): void
    {
        // No delivery zones

        $response = $this->postJson('/api/v1/checkout/calculate-fee', [
            'postcode' => '9999',
            'suburb' => 'Remote',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('fee', null);
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Promo Code Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test validating a valid promo code.
     *
     * @requirement CHK-015 Promo code validation
     */
    public function test_validates_valid_promo_code(): void
    {
        Promotion::factory()->active()->percentage(10)->code('SAVE10')->minOrder(50)->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'SAVE10',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('code', 'SAVE10');

        // JSON numeric handling returns integers for whole numbers
        $this->assertEquals(10, $response->json('discount'));
    }

    /**
     * Test case-insensitive promo code validation.
     */
    public function test_promo_code_validation_is_case_insensitive(): void
    {
        Promotion::factory()->active()->percentage(10)->code('SAVE10')->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'save10', // lowercase
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('valid', true);
    }

    /**
     * Test validating an invalid promo code.
     */
    public function test_rejects_invalid_promo_code(): void
    {
        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'NOTREAL',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('valid', false);
    }

    /**
     * Test promo code with minimum order not met.
     */
    public function test_rejects_promo_below_minimum_order(): void
    {
        Promotion::factory()->active()->percentage(10)->code('MIN100')->minOrder(100)->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'MIN100',
            'subtotal' => 50.00, // Below minimum
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('valid', false);

        $this->assertEquals(100, $response->json('min_order'));
    }

    /**
     * Test expired promo code.
     */
    public function test_rejects_expired_promo_code(): void
    {
        Promotion::factory()->expired()->code('EXPIRED')->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'EXPIRED',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('valid', false);
    }

    /**
     * Test exhausted promo code (max uses reached).
     */
    public function test_rejects_exhausted_promo_code(): void
    {
        Promotion::factory()->active()->exhausted()->code('MAXED')->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'MAXED',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('valid', false);
    }

    /**
     * Test fixed amount promo code.
     */
    public function test_calculates_fixed_amount_discount(): void
    {
        Promotion::factory()->active()->fixed(15.00)->code('FLAT15')->create();

        $response = $this->postJson('/api/v1/checkout/validate-promo', [
            'code' => 'FLAT15',
            'subtotal' => 100.00,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('type', 'fixed');

        $this->assertEquals(15, $response->json('discount'));
    }

    /*
    |--------------------------------------------------------------------------
    | Get Session Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test getting checkout session requires authentication.
     */
    public function test_checkout_session_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/checkout/session');

        $response->assertStatus(401);
    }

    /**
     * Test getting checkout session data.
     */
    public function test_can_get_checkout_session(): void
    {
        Sanctum::actingAs($this->user);

        // Create cart with items
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 25.00]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25.00,
        ]);

        // Create saved address
        Address::factory()->forUser($this->user)->default()->create();

        $response = $this->getJson('/api/v1/checkout/session');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'cart' => [
                    'item_count',
                    'subtotal',
                    'subtotal_formatted',
                ],
                'addresses',
                'default_address',
                'user' => [
                    'name',
                    'email',
                ],
            ]);
    }

    /**
     * Test session returns null cart when empty.
     */
    public function test_session_returns_null_cart_when_empty(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/checkout/session');

        $response->assertStatus(200)
            ->assertJsonPath('cart', null);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Order Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test creating an order requires authentication.
     *
     * @requirement CHK-025 Create order in database
     */
    public function test_create_order_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/create-order', []);

        $response->assertStatus(401);
    }

    /**
     * Test creating an order with new address.
     */
    public function test_can_create_order_with_new_address(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create cart with items
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'order' => [
                    'id',
                    'order_number',
                    'status',
                    'subtotal',
                    'delivery_fee',
                    'total',
                ],
            ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => Order::STATUS_PENDING,
        ]);

        // Verify cart was cleared
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    /**
     * Test creating order with saved address.
     */
    public function test_can_create_order_with_saved_address(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->innerSuburbs()->create();

        // Create saved address
        $address = Address::factory()->forUser($this->user)->inSuburb('Richmond', 'Melbourne', 'VIC', '3121')->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'address_id' => $address->id,
            'suburb' => 'Richmond',
            'city' => 'Melbourne',
            'postcode' => '3121',
            'state' => 'VIC',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        // Verify address was linked
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'address_id' => $address->id,
        ]);
    }

    /**
     * Test creating order with promo code.
     */
    public function test_can_create_order_with_promo_code(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create promo
        Promotion::factory()->active()->percentage(10)->code('SAVE10')->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 100.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'promo_code' => 'SAVE10',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        // Verify discount was applied
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(10.00, $order->discount);
        $this->assertEquals('SAVE10', $order->promotion_code);
    }

    /**
     * Test order creation fails with empty cart.
     */
    public function test_cannot_create_order_with_empty_cart(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Your cart is empty.');
    }

    /**
     * Test order creation fails when stock is insufficient.
     */
    public function test_cannot_create_order_with_insufficient_stock(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create cart with more items than stock
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create([
            'name' => 'Limited Item',
            'price_aud' => 50.00,
            'stock' => 2, // Only 2 in stock
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 5, // Trying to order 5
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test order generates unique order number.
     *
     * @requirement CHK-026 Generate unique order number
     */
    public function test_order_generates_unique_order_number(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);

        $orderNumber = $response->json('order.order_number');
        $this->assertNotNull($orderNumber);
        $this->assertStringStartsWith('ZM-', $orderNumber);
    }

    /**
     * Test order includes delivery instructions.
     */
    public function test_order_includes_delivery_instructions(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'delivery_instructions' => 'Leave at front door',
            'notes' => 'Please call before delivery',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'delivery_instructions' => 'Leave at front door',
            'notes' => 'Please call before delivery',
        ]);
    }

    /**
     * Test order with scheduled delivery.
     */
    public function test_order_with_scheduled_delivery(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $scheduledDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'scheduled_date' => $scheduledDate,
            'scheduled_time_slot' => '10:00 AM - 2:00 PM',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);

        // Check scheduled_date using carbon format matching
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals($scheduledDate, $order->scheduled_date->format('Y-m-d'));
        $this->assertEquals('10:00 AM - 2:00 PM', $order->scheduled_time_slot);
    }

    /**
     * Test order calculates correct totals.
     */
    public function test_order_calculates_correct_totals(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone with $10 fee, free over $100
        $zone = DeliveryZone::factory()->create([
            'name' => 'Test Zone',
            'suburbs' => ['Melbourne'],
            'delivery_fee' => 10.00,
            'free_delivery_threshold' => 100.00,
            'is_active' => true,
        ]);

        // Create promo (10% off, no minimum)
        Promotion::factory()->active()->percentage(10)->code('SAVE10')->minOrder(0)->create();

        // Create cart ($80 subtotal)
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 40.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 40.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'promo_code' => 'SAVE10',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);

        $order = Order::where('user_id', $this->user->id)->first();

        // Subtotal: $80 (2 x $40)
        // Delivery: $10 (below $100 threshold)
        // Discount: $8 (10% of $80)
        // Total: $80 + $10 - $8 = $82

        $this->assertEquals(80.00, (float) $order->subtotal);
        $this->assertEquals(10.00, (float) $order->delivery_fee);
        $this->assertEquals(8.00, (float) $order->discount);
        $this->assertEquals(82.00, (float) $order->total);
    }

    /**
     * Test creating order increments promo usage.
     */
    public function test_order_increments_promo_usage_count(): void
    {
        Sanctum::actingAs($this->user);

        // Create delivery zone
        DeliveryZone::factory()->melbourneCbd()->create();

        // Create promo with usage tracking
        $promo = Promotion::factory()->active()->percentage(10)->code('TRACK')->maxUses(100, 5)->create();

        // Create cart
        $cart = Cart::create(['user_id' => $this->user->id]);
        $product = Product::factory()->create(['price_aud' => 50.00, 'stock' => 10]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/create-order', [
            'street' => '123 Test Street',
            'suburb' => 'Melbourne CBD',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'promo_code' => 'TRACK',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);

        // Verify usage was incremented
        $this->assertDatabaseHas('promotions', [
            'id' => $promo->id,
            'uses_count' => 6, // Was 5, now 6
        ]);
    }
}
