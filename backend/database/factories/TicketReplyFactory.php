<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketReply>
 */
class TicketReplyFactory extends Factory
{
    protected $model = TicketReply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->paragraphs(2, true),
        ];
    }

    /**
     * Set as staff reply.
     */
    public function staffReply(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => User::factory()->state(['role' => User::ROLE_STAFF]),
        ]);
    }

    /**
     * Set as customer reply.
     */
    public function customerReply(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => User::factory()->state(['role' => User::ROLE_CUSTOMER]),
        ]);
    }

    /**
     * Set the ticket for the reply.
     */
    public function forTicket(SupportTicket $ticket): static
    {
        return $this->state(fn(array $attributes) => [
            'ticket_id' => $ticket->id,
        ]);
    }

    /**
     * Set the user for the reply.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
