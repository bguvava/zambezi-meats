<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement([
                Notification::TYPE_ORDER,
                Notification::TYPE_DELIVERY,
                Notification::TYPE_PROMOTION,
                Notification::TYPE_SYSTEM,
            ]),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'data' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    /**
     * Set notification as read.
     */
    public function read(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Set notification as unread.
     */
    public function unread(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Set notification type to order.
     */
    public function orderType(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Notification::TYPE_ORDER,
            'title' => 'Order Update',
            'message' => 'Your order status has been updated.',
        ]);
    }

    /**
     * Set notification type to delivery.
     */
    public function deliveryType(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Notification::TYPE_DELIVERY,
            'title' => 'Delivery Update',
            'message' => 'Your delivery is on its way.',
        ]);
    }

    /**
     * Set notification type to promotion.
     */
    public function promotionType(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Notification::TYPE_PROMOTION,
            'title' => 'Special Offer',
            'message' => 'Check out our latest promotions!',
        ]);
    }

    /**
     * Set notification type to system.
     */
    public function systemType(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Notification::TYPE_SYSTEM,
            'title' => 'System Notice',
            'message' => 'Important system notification.',
        ]);
    }

    /**
     * Set the user for the notification.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
