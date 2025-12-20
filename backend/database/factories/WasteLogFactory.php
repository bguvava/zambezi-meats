<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WasteLog>
 */
class WasteLogFactory extends Factory
{
    protected $model = WasteLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'logged_by' => User::factory()->state(['role' => User::ROLE_STAFF]),
            'quantity' => $this->faker->randomFloat(2, 0.5, 10.0),
            'reason' => $this->faker->randomElement(WasteLog::getReasons()),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Set reason to damaged.
     */
    public function damaged(): static
    {
        return $this->state(fn(array $attributes) => [
            'reason' => WasteLog::REASON_DAMAGED,
        ]);
    }

    /**
     * Set reason to expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'reason' => WasteLog::REASON_EXPIRED,
        ]);
    }

    /**
     * Set reason to quality.
     */
    public function quality(): static
    {
        return $this->state(fn(array $attributes) => [
            'reason' => WasteLog::REASON_QUALITY,
        ]);
    }

    /**
     * Set the product for the log.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Set the staff member who logged.
     */
    public function loggedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'logged_by' => $user->id,
        ]);
    }
}
