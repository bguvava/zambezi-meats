<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([Promotion::TYPE_PERCENTAGE, Promotion::TYPE_FIXED]);

        return [
            'name' => fake()->words(3, true) . ' Sale',
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $type === Promotion::TYPE_PERCENTAGE
                ? fake()->numberBetween(5, 30)
                : fake()->randomFloat(2, 5, 50),
            'min_order' => fake()->randomFloat(2, 0, 100),
            'max_uses' => fake()->optional(0.5)->numberBetween(10, 1000),
            'uses_count' => 0,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ];
    }

    /**
     * Indicate promotion is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate promotion is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate promotion is expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(30),
        ]);
    }

    /**
     * Indicate promotion hasn't started yet.
     */
    public function future(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(60),
        ]);
    }

    /**
     * Create a percentage discount.
     */
    public function percentage(float $percent = 10): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => $percent,
        ]);
    }

    /**
     * Create a fixed amount discount.
     */
    public function fixed(float $amount = 10): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Promotion::TYPE_FIXED,
            'value' => $amount,
        ]);
    }

    /**
     * Set a specific code.
     */
    public function code(string $code): static
    {
        return $this->state(fn(array $attributes) => [
            'code' => strtoupper($code),
        ]);
    }

    /**
     * Set minimum order requirement.
     */
    public function minOrder(float $minOrder): static
    {
        return $this->state(fn(array $attributes) => [
            'min_order' => $minOrder,
        ]);
    }

    /**
     * Set unlimited uses.
     */
    public function unlimited(): static
    {
        return $this->state(fn(array $attributes) => [
            'max_uses' => null,
        ]);
    }

    /**
     * Set maximum uses.
     */
    public function maxUses(int $maxUses, int $currentUses = 0): static
    {
        return $this->state(fn(array $attributes) => [
            'max_uses' => $maxUses,
            'uses_count' => $currentUses,
        ]);
    }

    /**
     * Indicate promotion has been fully used.
     */
    public function exhausted(): static
    {
        return $this->state(fn(array $attributes) => [
            'max_uses' => 100,
            'uses_count' => 100,
        ]);
    }

    /**
     * Create a welcome discount code.
     */
    public function welcome(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Welcome Discount',
            'code' => 'WELCOME10',
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 10,
            'min_order' => 50.00,
            'is_active' => true,
        ]);
    }

    /**
     * Create a first order discount.
     */
    public function firstOrder(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'First Order Discount',
            'code' => 'FIRST15',
            'type' => Promotion::TYPE_PERCENTAGE,
            'value' => 15,
            'min_order' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Create a free delivery code.
     */
    public function freeDelivery(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Free Delivery',
            'code' => 'FREESHIP',
            'type' => Promotion::TYPE_FIXED,
            'value' => 15.00,
            'min_order' => 75.00,
            'is_active' => true,
        ]);
    }
}
