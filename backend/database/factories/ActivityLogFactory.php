<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement([
                'User created',
                'User updated',
                'User status changed',
                'Password reset requested',
                'Login successful',
                'Logout',
            ]),
            'model_type' => fake()->optional()->randomElement([
                User::class,
                'App\Models\Order',
                'App\Models\Product',
            ]),
            'model_id' => fake()->optional()->numberBetween(1, 100),
            'old_values' => fake()->optional()->passthrough(['status' => 'active']),
            'new_values' => fake()->optional()->passthrough(['status' => 'suspended']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
