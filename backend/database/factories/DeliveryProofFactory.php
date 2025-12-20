<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\DeliveryProof;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryProof>
 */
class DeliveryProofFactory extends Factory
{
    protected $model = DeliveryProof::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'photo_path' => null,
            'signature_path' => null,
            'signature_data' => null,
            'recipient_name' => $this->faker->name(),
            'notes' => $this->faker->sentence(),
            'left_at_door' => false,
            'captured_by' => User::factory()->state(['role' => User::ROLE_STAFF]),
            'captured_at' => now(),
        ];
    }

    /**
     * Include photo path.
     */
    public function withPhoto(): static
    {
        return $this->state(fn(array $attributes) => [
            'photo_path' => 'deliveries/' . $this->faker->uuid() . '.jpg',
        ]);
    }

    /**
     * Include signature path.
     */
    public function withSignature(): static
    {
        return $this->state(fn(array $attributes) => [
            'signature_path' => 'signatures/' . $this->faker->uuid() . '.png',
        ]);
    }

    /**
     * Include both photo and signature.
     */
    public function complete(): static
    {
        return $this->withPhoto()->withSignature();
    }

    /**
     * Set the order for the proof.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn(array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Set the staff member who captured the proof.
     */
    public function capturedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'captured_by' => $user->id,
        ]);
    }

    /**
     * Mark as left at door.
     */
    public function leftAtDoor(): static
    {
        return $this->state(fn(array $attributes) => [
            'left_at_door' => true,
            'recipient_name' => null,
        ]);
    }
}
