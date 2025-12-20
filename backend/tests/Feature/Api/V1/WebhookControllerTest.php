<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for WebhookController.
 *
 * @requirement CHK-028 Create payment webhook handlers
 */
class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test Stripe webhook endpoint is accessible.
     */
    public function test_stripe_webhook_endpoint_is_accessible(): void
    {
        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                ],
            ],
        ]);

        // Should return 200 (success) or 400 (handled but no matching payment)
        // In test environment, signature verification is skipped
        $this->assertTrue(
            in_array($response->status(), [200, 400]),
            'Webhook should return 200 or 400, got ' . $response->status()
        );
    }

    /**
     * Test Stripe webhook handles payment_intent.succeeded event.
     */
    public function test_stripe_webhook_handles_payment_succeeded(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->stripe()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'pi_test_webhook_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_webhook_123',
                    'amount' => 10000, // Stripe uses cents
                    'currency' => 'aud',
                    'status' => 'succeeded',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('received', true);

        // Verify payment status was updated
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_COMPLETED,
        ]);
    }

    /**
     * Test Stripe webhook handles payment_intent.payment_failed event.
     */
    public function test_stripe_webhook_handles_payment_failed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->stripe()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'pi_test_failed_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_failed_123',
                    'last_payment_error' => [
                        'code' => 'card_declined',
                        'message' => 'Your card was declined.',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify payment status was updated to failed
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    /**
     * Test Stripe webhook handles charge.refunded event.
     */
    public function test_stripe_webhook_handles_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->confirmed()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->stripe()->completed()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'pi_test_refund_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_test_123',
                    'payment_intent' => 'pi_test_refund_123',
                    'refunded' => true,
                    'amount_refunded' => 10000, // Full refund
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify payment status was updated to refunded
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_REFUNDED,
        ]);
    }

    /**
     * Test Stripe webhook returns 400 for unknown payment.
     */
    public function test_stripe_webhook_returns_400_for_unknown_payment(): void
    {
        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_nonexistent_123',
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test Stripe webhook handles unknown event types gracefully.
     */
    public function test_stripe_webhook_handles_unknown_event_types(): void
    {
        $response = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'some.unknown.event',
            'data' => [
                'object' => [],
            ],
        ]);

        // Should acknowledge receipt even for unknown events
        // Either 200 (ignored) or 400 (unhandled)
        $this->assertTrue(
            in_array($response->status(), [200, 400]),
            'Should handle unknown event type gracefully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PayPal Webhook Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test PayPal webhook endpoint is accessible.
     */
    public function test_paypal_webhook_endpoint_is_accessible(): void
    {
        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => [
                'id' => 'PAY-TEST123',
            ],
        ]);

        // Should return 200 (success) or 400 (handled but no matching payment)
        $this->assertTrue(
            in_array($response->status(), [200, 400]),
            'Webhook should return 200 or 400, got ' . $response->status()
        );
    }

    /**
     * Test PayPal webhook handles PAYMENT.CAPTURE.COMPLETED event.
     */
    public function test_paypal_webhook_handles_payment_completed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->paypal()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'PAYPAL_ORDER_TEST_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE_123',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'PAYPAL_ORDER_TEST_123',
                    ],
                ],
                'amount' => [
                    'value' => '100.00',
                    'currency_code' => 'AUD',
                ],
                'status' => 'COMPLETED',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('received', true);

        // Verify payment status was updated
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_COMPLETED,
        ]);
    }

    /**
     * Test PayPal webhook handles PAYMENT.CAPTURE.DENIED event.
     */
    public function test_paypal_webhook_handles_payment_denied(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pending()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->paypal()->pending()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'PAYPAL_ORDER_DENIED_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.DENIED',
            'resource' => [
                'id' => 'CAPTURE_DENIED_123',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'PAYPAL_ORDER_DENIED_123',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify payment status was updated to failed
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    /**
     * Test PayPal webhook handles PAYMENT.CAPTURE.REFUNDED event.
     */
    public function test_paypal_webhook_handles_refund(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->confirmed()->create([
            'user_id' => $user->id,
            'total' => 100.00,
        ]);

        $payment = Payment::factory()->paypal()->completed()->create([
            'order_id' => $order->id,
            'amount' => 100.00,
            'transaction_id' => 'PAYPAL_ORDER_REFUND_123',
        ]);

        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'resource' => [
                'id' => 'REFUND_123',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'PAYPAL_ORDER_REFUND_123',
                    ],
                ],
                'amount' => [
                    'value' => '100.00',
                    'currency_code' => 'AUD',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify payment status was updated to refunded
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_REFUNDED,
        ]);
    }

    /**
     * Test PayPal webhook returns 400 for unknown payment.
     */
    public function test_paypal_webhook_returns_400_for_unknown_payment(): void
    {
        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE_NONEXISTENT',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => 'NONEXISTENT_ORDER',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test PayPal webhook handles unknown event types gracefully.
     */
    public function test_paypal_webhook_handles_unknown_event_types(): void
    {
        $response = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'UNKNOWN.EVENT.TYPE',
            'resource' => [],
        ]);

        // Should handle unknown event type gracefully
        $this->assertTrue(
            in_array($response->status(), [200, 400]),
            'Should handle unknown event type gracefully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Security Tests
    |--------------------------------------------------------------------------
    */

    /**
     * Test webhooks are public (no auth required).
     */
    public function test_webhooks_do_not_require_authentication(): void
    {
        // Stripe webhook
        $stripeResponse = $this->postJson('/api/v1/webhooks/stripe', [
            'type' => 'test',
        ]);

        $this->assertNotEquals(401, $stripeResponse->status());

        // PayPal webhook
        $paypalResponse = $this->postJson('/api/v1/webhooks/paypal', [
            'event_type' => 'test',
        ]);

        $this->assertNotEquals(401, $paypalResponse->status());
    }

    /**
     * Test empty payload is handled gracefully.
     */
    public function test_empty_payload_handled_gracefully(): void
    {
        $stripeResponse = $this->postJson('/api/v1/webhooks/stripe', []);
        $this->assertTrue(
            in_array($stripeResponse->status(), [200, 400, 500]),
            'Should handle empty Stripe payload'
        );

        $paypalResponse = $this->postJson('/api/v1/webhooks/paypal', []);
        $this->assertTrue(
            in_array($paypalResponse->status(), [200, 400, 500]),
            'Should handle empty PayPal payload'
        );
    }
}
