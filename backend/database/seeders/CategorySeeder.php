<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder for creating categories.
 */
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Beef',
                'description' => 'Premium Australian grass-fed and grain-fed beef cuts',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Steaks', 'description' => 'Premium beef steaks including ribeye, sirloin, and scotch fillet', 'sort_order' => 1],
                    ['name' => 'Roasts', 'description' => 'Beef roasting cuts perfect for Sunday dinners', 'sort_order' => 2],
                    ['name' => 'Mince & Burgers', 'description' => 'Ground beef and premium burger patties', 'sort_order' => 3],
                    ['name' => 'Specialty Cuts', 'description' => 'Brisket, short ribs, and specialty beef cuts', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Lamb',
                'description' => 'Succulent Australian lamb, sourced from local farms',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Chops & Cutlets', 'description' => 'Lamb chops and cutlets', 'sort_order' => 1],
                    ['name' => 'Roasts', 'description' => 'Leg of lamb and lamb roasts', 'sort_order' => 2],
                    ['name' => 'Mince', 'description' => 'Lamb mince and kofta', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Pork',
                'description' => 'Quality Australian pork from trusted suppliers',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Chops & Steaks', 'description' => 'Pork chops and steaks', 'sort_order' => 1],
                    ['name' => 'Roasts', 'description' => 'Pork roasts including crackling roast', 'sort_order' => 2],
                    ['name' => 'Ribs', 'description' => 'Pork ribs and spare ribs', 'sort_order' => 3],
                    ['name' => 'Bacon & Ham', 'description' => 'Bacon rashers and ham', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken',
                'description' => 'Fresh free-range and organic chicken',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Whole Chicken', 'description' => 'Whole chickens for roasting', 'sort_order' => 1],
                    ['name' => 'Breast', 'description' => 'Chicken breast fillets', 'sort_order' => 2],
                    ['name' => 'Thigh & Drumsticks', 'description' => 'Chicken thighs and drumsticks', 'sort_order' => 3],
                    ['name' => 'Wings', 'description' => 'Chicken wings', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Sausages',
                'description' => 'Handcrafted sausages made fresh daily',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Beef Sausages', 'description' => 'Premium beef sausages', 'sort_order' => 1],
                    ['name' => 'Pork Sausages', 'description' => 'Traditional pork sausages', 'sort_order' => 2],
                    ['name' => 'Gourmet Sausages', 'description' => 'Specialty and gourmet sausages', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Seafood',
                'description' => 'Fresh Australian seafood',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Fish', 'description' => 'Fresh fish fillets and whole fish', 'sort_order' => 1],
                    ['name' => 'Prawns', 'description' => 'Australian prawns', 'sort_order' => 2],
                    ['name' => 'Shellfish', 'description' => 'Oysters, mussels, and more', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Deli',
                'description' => 'Premium deli meats and smallgoods',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Sliced Meats', 'description' => 'Ham, salami, and sliced meats', 'sort_order' => 1],
                    ['name' => 'Pâté & Terrine', 'description' => 'Gourmet pâté and terrines', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'BBQ Packs',
                'description' => 'Ready-to-cook BBQ and value packs',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $categoryData['slug'] = Str::slug($categoryData['name']);
            $categoryData['is_active'] = true;

            $parent = Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );

            foreach ($children as $childData) {
                $childData['slug'] = Str::slug($parent->name . '-' . $childData['name']);
                $childData['parent_id'] = $parent->id;
                $childData['is_active'] = true;

                Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    $childData
                );
            }
        }
    }
}
