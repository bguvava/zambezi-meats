<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryLog>
 */
class InventoryLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = InventoryLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['addition', 'deduction', 'adjustment', 'waste']);
        $stockBefore = $this->faker->numberBetween(10, 100);
        $quantity = $this->faker->numberBetween(1, 20);

        $stockAfter = match ($type) {
            'addition' => $stockBefore + $quantity,
            'deduction', 'waste' => max(0, $stockBefore - $quantity),
            'adjustment' => $stockBefore + $quantity,
            default => $stockBefore,
        };

        return [
            'product_id' => Product::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reason' => $this->faker->sentence(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Set the type to addition.
     */
    public function addition(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $attributes['stock_before'] ?? 50;
            $qty = $attributes['quantity'] ?? 10;
            return [
                'type' => 'addition',
                'stock_before' => $stockBefore,
                'quantity' => $qty,
                'stock_after' => $stockBefore + $qty,
            ];
        });
    }

    /**
     * Set the type to deduction.
     */
    public function deduction(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $attributes['stock_before'] ?? 50;
            $qty = $attributes['quantity'] ?? 10;
            return [
                'type' => 'deduction',
                'stock_before' => $stockBefore,
                'quantity' => $qty,
                'stock_after' => max(0, $stockBefore - $qty),
            ];
        });
    }

    /**
     * Set the type to adjustment.
     */
    public function adjustment(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'adjustment',
        ]);
    }

    /**
     * Set the type to waste.
     */
    public function waste(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $attributes['stock_before'] ?? 50;
            $qty = $attributes['quantity'] ?? 10;
            return [
                'type' => 'waste',
                'stock_before' => $stockBefore,
                'quantity' => $qty,
                'stock_after' => max(0, $stockBefore - $qty),
            ];
        });
    }
}
