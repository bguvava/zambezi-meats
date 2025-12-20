<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating delivery zones.
 */
class DeliveryZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Sydney CBD',
                'suburbs' => [
                    'Sydney',
                    'The Rocks',
                    'Haymarket',
                    'Pyrmont',
                    'Ultimo',
                    'Surry Hills',
                    'Darlinghurst',
                    'Potts Point',
                    'Woolloomooloo',
                    'Millers Point',
                ],
                'delivery_fee' => 9.95,
                'free_delivery_threshold' => 150.00,
                'estimated_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Inner Sydney',
                'suburbs' => [
                    'Newtown',
                    'Marrickville',
                    'Erskineville',
                    'Alexandria',
                    'Waterloo',
                    'Redfern',
                    'Paddington',
                    'Bondi',
                    'Bondi Junction',
                    'Coogee',
                    'Randwick',
                    'Kensington',
                    'Mascot',
                    'Rosebery',
                    'Zetland',
                    'Glebe',
                    'Camperdown',
                    'Leichhardt',
                    'Balmain',
                    'Rozelle',
                ],
                'delivery_fee' => 12.95,
                'free_delivery_threshold' => 200.00,
                'estimated_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Northern Sydney',
                'suburbs' => [
                    'Chatswood',
                    'North Sydney',
                    'Lane Cove',
                    'Artarmon',
                    'Crows Nest',
                    'Neutral Bay',
                    'Mosman',
                    'Manly',
                    'Dee Why',
                    'Brookvale',
                    'Ryde',
                    'Macquarie Park',
                    'Epping',
                    'Eastwood',
                    'Hornsby',
                    'Gordon',
                    'Pymble',
                    'Wahroonga',
                ],
                'delivery_fee' => 14.95,
                'free_delivery_threshold' => 200.00,
                'estimated_days' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Western Sydney',
                'suburbs' => [
                    'Parramatta',
                    'Blacktown',
                    'Penrith',
                    'Liverpool',
                    'Campbelltown',
                    'Bankstown',
                    'Auburn',
                    'Strathfield',
                    'Burwood',
                    'Ashfield',
                    'Homebush',
                    'Olympic Park',
                    'Granville',
                    'Merrylands',
                    'Fairfield',
                    'Cabramatta',
                ],
                'delivery_fee' => 16.95,
                'free_delivery_threshold' => 250.00,
                'estimated_days' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Southern Sydney',
                'suburbs' => [
                    'Hurstville',
                    'Kogarah',
                    'Rockdale',
                    'Brighton-Le-Sands',
                    'Sans Souci',
                    'Cronulla',
                    'Miranda',
                    'Sutherland',
                    'Engadine',
                    'Caringbah',
                    'Gymea',
                    'Kirrawee',
                ],
                'delivery_fee' => 16.95,
                'free_delivery_threshold' => 250.00,
                'estimated_days' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zoneData) {
            DeliveryZone::updateOrCreate(
                ['name' => $zoneData['name']],
                $zoneData
            );
        }
    }
}
