<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $deliveryFee = fake()->randomElement([0.00, 9.95, 14.95]);
        $discount = fake()->randomFloat(2, 0, 20);
        $total = $subtotal + $deliveryFee - $discount;

        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'address_id' => null,
            'delivery_zone_id' => null,
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => max(0, $total),
            'currency' => 'AUD',
            'exchange_rate' => 1.000000,
            'promotion_code' => null,
            'notes' => null,
            'delivery_instructions' => null,
            'scheduled_date' => null,
            'scheduled_time_slot' => null,
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    /**
     * Indicate that the order is ready for delivery.
     */
    public function ready(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_READY,
        ]);
    }

    /**
     * Indicate that the order is out for delivery.
     */
    public function outForDelivery(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_DELIVERED,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Order::STATUS_CANCELLED,
        ]);
    }

    /**
     * Include address for the order.
     */
    public function withAddress(): static
    {
        return $this->state(function (array $attributes) {
            $userId = $attributes['user_id'] ?? User::factory()->create()->id;

            return [
                'user_id' => $userId,
                'address_id' => Address::factory()->create(['user_id' => $userId])->id,
            ];
        });
    }

    /**
     * Include delivery zone for the order.
     */
    public function withDeliveryZone(): static
    {
        return $this->state(fn(array $attributes) => [
            'delivery_zone_id' => DeliveryZone::factory(),
        ]);
    }

    /**
     * Include a promotion code.
     */
    public function withPromotion(string $code = 'SAVE10'): static
    {
        return $this->state(fn(array $attributes) => [
            'promotion_code' => $code,
            'discount' => 10.00,
        ]);
    }

    /**
     * Include scheduled delivery.
     */
    public function scheduled(): static
    {
        return $this->state(fn(array $attributes) => [
            'scheduled_date' => now()->addDays(2),
            'scheduled_time_slot' => '10:00 AM - 2:00 PM',
        ]);
    }

    /**
     * Set a specific total amount.
     */
    public function withTotal(float $total): static
    {
        return $this->state(fn(array $attributes) => [
            'subtotal' => $total,
            'delivery_fee' => 0.00,
            'discount' => 0.00,
            'total' => $total,
        ]);
    }

    /**
     * Include free delivery.
     */
    public function freeDelivery(): static
    {
        return $this->state(function (array $attributes) {
            $subtotal = $attributes['subtotal'] ?? 150.00;
            $discount = $attributes['discount'] ?? 0.00;

            return [
                'delivery_fee' => 0.00,
                'total' => $subtotal - $discount,
            ];
        });
    }
}
