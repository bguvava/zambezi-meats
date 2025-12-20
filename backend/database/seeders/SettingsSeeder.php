<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating default settings.
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'Zambezi Meats',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_GENERAL,
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Premium Quality Meats, Delivered Fresh',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_GENERAL,
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@zambezimeats.com.au',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_GENERAL,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+61 2 9000 0000',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_GENERAL,
            ],
            [
                'key' => 'business_address',
                'value' => json_encode([
                    'street' => '123 Butcher Lane',
                    'suburb' => 'Sydney',
                    'state' => 'NSW',
                    'postcode' => '2000',
                    'country' => 'Australia',
                ]),
                'type' => Setting::TYPE_JSON,
                'group' => Setting::GROUP_GENERAL,
            ],
            [
                'key' => 'abn',
                'value' => '12 345 678 901',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_GENERAL,
            ],

            // Store Settings
            [
                'key' => 'minimum_order_amount',
                'value' => '100',
                'type' => Setting::TYPE_FLOAT,
                'group' => Setting::GROUP_STORE,
            ],
            [
                'key' => 'default_currency',
                'value' => 'AUD',
                'type' => Setting::TYPE_STRING,
                'group' => Setting::GROUP_STORE,
            ],
            [
                'key' => 'allow_guest_checkout',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_STORE,
            ],
            [
                'key' => 'products_per_page',
                'value' => '12',
                'type' => Setting::TYPE_INTEGER,
                'group' => Setting::GROUP_STORE,
            ],
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'type' => Setting::TYPE_INTEGER,
                'group' => Setting::GROUP_STORE,
            ],

            // Delivery Settings
            [
                'key' => 'delivery_days',
                'value' => json_encode(['Tuesday', 'Thursday', 'Saturday']),
                'type' => Setting::TYPE_JSON,
                'group' => Setting::GROUP_DELIVERY,
            ],
            [
                'key' => 'delivery_time_slots',
                'value' => json_encode([
                    '8:00 AM - 12:00 PM',
                    '12:00 PM - 4:00 PM',
                    '4:00 PM - 8:00 PM',
                ]),
                'type' => Setting::TYPE_JSON,
                'group' => Setting::GROUP_DELIVERY,
            ],
            [
                'key' => 'delivery_cutoff_hours',
                'value' => '24',
                'type' => Setting::TYPE_INTEGER,
                'group' => Setting::GROUP_DELIVERY,
            ],

            // Payment Settings
            [
                'key' => 'payment_gateways_enabled',
                'value' => json_encode(['stripe', 'paypal']),
                'type' => Setting::TYPE_JSON,
                'group' => Setting::GROUP_PAYMENT,
            ],
            [
                'key' => 'stripe_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_PAYMENT,
            ],
            [
                'key' => 'paypal_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_PAYMENT,
            ],

            // Email Settings
            [
                'key' => 'order_confirmation_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_EMAIL,
            ],
            [
                'key' => 'order_shipped_notification_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_EMAIL,
            ],
            [
                'key' => 'promotional_emails_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => Setting::GROUP_EMAIL,
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
