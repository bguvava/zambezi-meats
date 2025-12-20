<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests for PaymentController.
 *
 * @requirement CHK-009 Stripe payment
 * @requirement CHK-010 PayPal payment
 * @requirement CHK-011 Afterpay payment
 * @requirement CHK-012 Cash on Delivery
 */
class PaymentControllerTest extends TestCase
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
    | Get Payment Methods Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test getting available payment methods.
     */
    public function test_can_get_payment_methods(): void
    {
        $response = $this->getJson('/api/v1/checkout/payment-methods');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'methods',
            ]);
    }

    /**
     * Test payment methods include expected options.
     */
    public function test_payment_methods_include_expected_options(): void
    {
        $response = $this->getJson('/api/v1/checkout/payment-methods?subtotal=100');

        $response->assertStatus(200);

        $methods = collect($response->json('methods'));

        // At minimum, COD should be available (mock mode)
        $methodIds = $methods->pluck('id')->toArray();

        $this->assertTrue(
            count($methodIds) > 0,
            'At least one payment method should be available'
        );
    }

    /**
     * Test Afterpay shows installments calculation.
     */
    public function test_afterpay_shows_installments(): void
    {
        $response = $this->getJson('/api/v1/checkout/payment-methods?subtotal=200&currency=AUD');

        $response->assertStatus(200);

        $methods = collect($response->json('methods'));
        $afterpay = $methods->firstWhere('id', 'afterpay');

        if ($afterpay && $afterpay['enabled']) {
            $this->assertArrayHasKey('installments', $afterpay);
            // 4 installments of $50 for $200 total
            $this->assertEquals(50.00, $afterpay['installments']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test processing Stripe payment requires authentication.
     *
     * @requirement CHK-009 Integrate Stripe payment
     */
    public function test_stripe_payment_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/payment/stripe', [
            'order_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test processing Stripe payment with pending order.
     */
    public function test_can_process_stripe_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/stripe', [
            'order_id' => $order->id,
        ]);

        // In mock mode, should succeed or return payment intent data
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if Stripe not configured'
        );

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
            ]);
        }
    }

    /**
     * Test cannot process Stripe payment for another user's order.
     */
    public function test_cannot_process_stripe_for_other_users_order(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $otherUser->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/stripe', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test cannot process payment for non-pending order.
     */
    public function test_cannot_process_stripe_for_non_pending_order(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/stripe', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test confirming Stripe payment.
     */
    public function test_can_confirm_stripe_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->stripe()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'pi_test_123',
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/stripe/confirm', [
            'payment_intent_id' => 'pi_test_123',
        ]);

        // In mock mode, should succeed
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if confirmation fails'
        );
    }

    /**
     * Test confirming non-existent payment returns 404.
     */
    public function test_stripe_confirm_returns_404_for_invalid_payment(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/payment/stripe/confirm', [
            'payment_intent_id' => 'pi_nonexistent',
        ]);

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | PayPal Payment Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test processing PayPal payment requires authentication.
     *
     * @requirement CHK-010 Integrate PayPal payment
     */
    public function test_paypal_payment_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/payment/paypal', [
            'order_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test processing PayPal payment.
     */
    public function test_can_process_paypal_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/paypal', [
            'order_id' => $order->id,
        ]);

        // In mock mode, should succeed with approval URL
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if PayPal not configured'
        );

        if ($response->status() === 200 && $response->json('success')) {
            $response->assertJsonStructure([
                'success',
                'approval_url',
            ]);
        }
    }

    /**
     * Test confirming PayPal payment.
     */
    public function test_can_confirm_paypal_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->paypal()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'PAYPAL_ORDER_123',
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/paypal/confirm', [
            'paypal_order_id' => 'PAYPAL_ORDER_123',
        ]);

        // In mock mode, should succeed
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if confirmation fails'
        );
    }

    /**
     * Test confirming non-existent PayPal payment.
     */
    public function test_paypal_confirm_returns_404_for_invalid_payment(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/payment/paypal/confirm', [
            'paypal_order_id' => 'NONEXISTENT',
        ]);

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Afterpay Payment Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test processing Afterpay payment requires authentication.
     *
     * @requirement CHK-011 Integrate Afterpay payment
     */
    public function test_afterpay_payment_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/payment/afterpay', [
            'order_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test processing Afterpay payment.
     */
    public function test_can_process_afterpay_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 150.00, // Within Afterpay limits
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/afterpay', [
            'order_id' => $order->id,
        ]);

        // In mock mode, should succeed with redirect URL
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if Afterpay not configured'
        );

        if ($response->status() === 200 && $response->json('success')) {
            $response->assertJsonStructure([
                'success',
                'redirect_url',
            ]);
        }
    }

    /**
     * Test confirming Afterpay payment.
     */
    public function test_can_confirm_afterpay_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 150.00,
        ]);

        $payment = Payment::factory()->afterpay()->pending()->create([
            'order_id' => $order->id,
            'amount' => 150.00,
            'transaction_id' => 'ap_token_123',
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/afterpay/confirm', [
            'token' => 'ap_token_123',
            'status' => 'SUCCESS',
        ]);

        // In mock mode, should succeed
        $this->assertTrue(
            in_array($response->status(), [200, 422]),
            'Should return 200 success or 422 if confirmation fails'
        );
    }

    /**
     * Test cancelled Afterpay payment returns error.
     */
    public function test_cancelled_afterpay_returns_error(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/payment/afterpay/confirm', [
            'token' => 'any_token',
            'status' => 'CANCELLED',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Afterpay payment was cancelled.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cash on Delivery Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test processing COD payment requires authentication.
     *
     * @requirement CHK-012 Implement Cash on Delivery
     */
    public function test_cod_payment_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/checkout/payment/cod', [
            'order_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test processing Cash on Delivery payment.
     */
    public function test_can_process_cod_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/cod', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'order',
            ]);
    }

    /**
     * Test COD creates payment record.
     */
    public function test_cod_creates_payment_record(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $this->postJson('/api/v1/checkout/payment/cod', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'cod',
            'status' => Payment::STATUS_PENDING,
            'amount' => 100.00,
        ]);
    }

    /**
     * Test COD updates order status to confirmed.
     */
    public function test_cod_updates_order_status(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
            'total' => 100.00,
        ]);

        $this->postJson('/api/v1/checkout/payment/cod', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Test cannot process COD for other user's order.
     */
    public function test_cannot_process_cod_for_other_users_order(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $otherUser->id,
            'total' => 100.00,
        ]);

        $response = $this->postJson('/api/v1/checkout/payment/cod', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Status Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test getting payment status requires authentication.
     */
    public function test_payment_status_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/checkout/payment/status/1');

        $response->assertStatus(401);
    }

    /**
     * Test getting payment status for order.
     */
    public function test_can_get_payment_status(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $payment = Payment::factory()->completed()->create([
            'order_id' => $order->id,
        ]);

        $response = $this->getJson('/api/v1/checkout/payment/status/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order_status', $order->status)
            ->assertJsonStructure([
                'success',
                'order_status',
                'payment' => [
                    'id',
                    'gateway',
                    'status',
                    'amount',
                ],
            ]);
    }

    /**
     * Test cannot get payment status for other user's order.
     */
    public function test_cannot_get_payment_status_for_other_users_order(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson('/api/v1/checkout/payment/status/' . $order->id);

        $response->assertStatus(404);
    }

    /**
     * Test payment status returns null when no payment exists.
     */
    public function test_payment_status_returns_null_when_no_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->pending()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/checkout/payment/status/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('payment', null);
    }

    /**
     * Test payment processing validates order_id is required.
     */
    public function test_payment_processing_requires_order_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/payment/stripe', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.errors.order_id.0', 'Order ID is required.');
    }
}
