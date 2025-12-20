<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => fake()->randomElement([
                Payment::GATEWAY_STRIPE,
                Payment::GATEWAY_PAYPAL,
                'afterpay',
                'cod',
            ]),
            'transaction_id' => 'txn_' . Str::random(24),
            'status' => Payment::STATUS_PENDING,
            'amount' => fake()->randomFloat(2, 50, 500),
            'currency' => 'AUD',
            'gateway_response' => null,
        ];
    }

    /**
     * Indicate payment is via Stripe.
     */
    public function stripe(): static
    {
        return $this->state(fn(array $attributes) => [
            'gateway' => Payment::GATEWAY_STRIPE,
            'transaction_id' => 'pi_' . Str::random(24),
            'gateway_response' => [
                'payment_intent_id' => 'pi_' . Str::random(24),
                'client_secret' => 'pi_' . Str::random(24) . '_secret_' . Str::random(16),
            ],
        ]);
    }

    /**
     * Indicate payment is via PayPal.
     */
    public function paypal(): static
    {
        return $this->state(fn(array $attributes) => [
            'gateway' => Payment::GATEWAY_PAYPAL,
            'transaction_id' => strtoupper(Str::random(17)),
            'gateway_response' => [
                'paypal_order_id' => strtoupper(Str::random(17)),
                'payer_id' => strtoupper(Str::random(13)),
            ],
        ]);
    }

    /**
     * Indicate payment is via Afterpay.
     */
    public function afterpay(): static
    {
        return $this->state(fn(array $attributes) => [
            'gateway' => 'afterpay',
            'transaction_id' => 'ap_' . Str::random(24),
            'gateway_response' => [
                'afterpay_token' => 'tok_' . Str::random(24),
            ],
        ]);
    }

    /**
     * Indicate payment is Cash on Delivery.
     */
    public function cashOnDelivery(): static
    {
        return $this->state(fn(array $attributes) => [
            'gateway' => 'cod',
            'transaction_id' => 'cod_' . Str::random(24),
            'gateway_response' => [
                'method' => 'cash_on_delivery',
                'confirmed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Indicate payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Payment::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate payment has failed.
     */
    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Payment::STATUS_FAILED,
            'gateway_response' => [
                'error' => 'Payment declined',
                'error_code' => 'card_declined',
            ],
        ]);
    }

    /**
     * Indicate payment was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Payment::STATUS_REFUNDED,
            'gateway_response' => [
                'refund_id' => 're_' . Str::random(24),
                'refunded_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Set a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn(array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Create payment for a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn(array $attributes) => [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);
    }
}
