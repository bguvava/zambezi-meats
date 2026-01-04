<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Service categories seeder.
 *
 * @requirement SERV-002 Service categories
 */
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Catering Services',
                'slug' => 'catering',
                'description' => 'Professional catering services for events and special occasions',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Delivery Services',
                'slug' => 'delivery',
                'description' => 'Fresh meat delivery to your doorstep',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Custom Cuts',
                'slug' => 'custom-cuts',
                'description' => 'Tailored meat cutting services to your specifications',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }

        echo "✅ Successfully seeded " . count($categories) . " service categories!\n";
    }
}
