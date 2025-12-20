<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'subject' => $this->faker->sentence(5),
            'message' => $this->faker->paragraphs(2, true),
            'status' => SupportTicket::STATUS_OPEN,
            'priority' => SupportTicket::PRIORITY_MEDIUM,
        ];
    }

    /**
     * Set ticket status to open.
     */
    public function open(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupportTicket::STATUS_OPEN,
        ]);
    }

    /**
     * Set ticket status to in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupportTicket::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Set ticket status to resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupportTicket::STATUS_RESOLVED,
        ]);
    }

    /**
     * Set ticket status to closed.
     */
    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SupportTicket::STATUS_CLOSED,
        ]);
    }

    /**
     * Set ticket priority to low.
     */
    public function lowPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => SupportTicket::PRIORITY_LOW,
        ]);
    }

    /**
     * Set ticket priority to high.
     */
    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => SupportTicket::PRIORITY_HIGH,
        ]);
    }

    /**
     * Set ticket priority to urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => SupportTicket::PRIORITY_URGENT,
        ]);
    }

    /**
     * Associate with an order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn(array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Set the user for the ticket.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
