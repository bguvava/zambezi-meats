<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = DeliveryZone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Melbourne CBD', 'Inner Suburbs', 'Outer Suburbs', 'Regional']),
            'suburbs' => fake()->randomElements(
                ['Melbourne', 'South Yarra', 'Richmond', 'Fitzroy', 'Carlton', 'Brunswick', 'St Kilda'],
                fake()->numberBetween(2, 5)
            ),
            'delivery_fee' => fake()->randomFloat(2, 5, 20),
            'free_delivery_threshold' => fake()->optional(0.7)->randomFloat(2, 80, 150),
            'estimated_days' => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }

    /**
     * Indicate zone is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate zone is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set specific delivery fee.
     */
    public function fee(float $fee): static
    {
        return $this->state(fn(array $attributes) => [
            'delivery_fee' => $fee,
        ]);
    }

    /**
     * Set free delivery threshold.
     */
    public function freeAbove(float $threshold): static
    {
        return $this->state(fn(array $attributes) => [
            'free_delivery_threshold' => $threshold,
        ]);
    }

    /**
     * No free delivery threshold.
     */
    public function noFreeDelivery(): static
    {
        return $this->state(fn(array $attributes) => [
            'free_delivery_threshold' => null,
        ]);
    }

    /**
     * Create Melbourne CBD zone.
     */
    public function melbourneCbd(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Melbourne CBD',
            'suburbs' => ['Melbourne', 'South Melbourne', 'Docklands', 'Southbank', 'East Melbourne'],
            'delivery_fee' => 9.95,
            'free_delivery_threshold' => 100.00,
            'estimated_days' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * Create inner suburbs zone.
     */
    public function innerSuburbs(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Inner Suburbs',
            'suburbs' => ['South Yarra', 'Richmond', 'Fitzroy', 'Carlton', 'Collingwood', 'Prahran'],
            'delivery_fee' => 12.95,
            'free_delivery_threshold' => 120.00,
            'estimated_days' => 2,
            'is_active' => true,
        ]);
    }

    /**
     * Create outer suburbs zone.
     */
    public function outerSuburbs(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Outer Suburbs',
            'suburbs' => ['Werribee', 'Dandenong', 'Frankston', 'Ringwood', 'Glen Waverley'],
            'delivery_fee' => 19.95,
            'free_delivery_threshold' => 150.00,
            'estimated_days' => 3,
            'is_active' => true,
        ]);
    }

    /**
     * Set specific suburbs.
     */
    public function suburbs(array $suburbs): static
    {
        return $this->state(fn(array $attributes) => [
            'suburbs' => $suburbs,
        ]);
    }

    /**
     * Set estimated days.
     */
    public function estimatedDays(int $days): static
    {
        return $this->state(fn(array $attributes) => [
            'estimated_days' => $days,
        ]);
    }
}
