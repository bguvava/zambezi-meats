<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating default settings.
 *
 * @requirement SET-001 to SET-030 System Settings
 * @requirement DB-017 Populate settings table
 */
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // ============================================
            // Store Settings (group: store)
            // ============================================
            [
                'key' => 'store_name',
                'value' => 'Zambezi Meats',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'The name of the store displayed throughout the site',
            ],
            [
                'key' => 'store_tagline',
                'value' => 'Premium Quality Meats, Delivered Fresh',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store tagline/slogan',
            ],
            [
                'key' => 'store_logo',
                'value' => null,
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'URL to the store logo image',
            ],
            [
                'key' => 'store_address',
                'value' => '6/1053 Old Princes Highway',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store street address',
            ],
            [
                'key' => 'store_suburb',
                'value' => 'Engadine',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store suburb/city',
            ],
            [
                'key' => 'store_state',
                'value' => 'NSW',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store state/province',
            ],
            [
                'key' => 'store_postcode',
                'value' => '2233',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store postal/zip code',
            ],
            [
                'key' => 'store_phone',
                'value' => '+61 2 9000 0000',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store contact phone number',
            ],
            [
                'key' => 'store_email',
                'value' => 'info@zambezimeats.com.au',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Store contact email address',
            ],
            [
                'key' => 'store_abn',
                'value' => '12 345 678 901',
                'type' => Setting::TYPE_STRING,
                'group' => 'store',
                'description' => 'Australian Business Number',
            ],

            // ============================================
            // Operating Hours Settings (group: operating)
            // ============================================
            [
                'key' => 'operating_hours_monday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Monday operating hours',
            ],
            [
                'key' => 'operating_hours_tuesday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Tuesday operating hours',
            ],
            [
                'key' => 'operating_hours_wednesday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Wednesday operating hours',
            ],
            [
                'key' => 'operating_hours_thursday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Thursday operating hours',
            ],
            [
                'key' => 'operating_hours_friday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Friday operating hours',
            ],
            [
                'key' => 'operating_hours_saturday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Saturday operating hours',
            ],
            [
                'key' => 'operating_hours_sunday',
                'value' => json_encode(['open' => '07:00', 'close' => '18:00', 'closed' => false]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'Sunday operating hours',
            ],
            [
                'key' => 'holiday_dates',
                'value' => json_encode([]),
                'type' => Setting::TYPE_JSON,
                'group' => 'operating',
                'description' => 'List of holiday/closure dates',
            ],

            // ============================================
            // Payment Settings (group: payment)
            // ============================================
            [
                'key' => 'stripe_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'payment',
                'description' => 'Enable Stripe payment gateway',
            ],
            [
                'key' => 'stripe_public_key',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Stripe publishable key',
            ],
            [
                'key' => 'stripe_secret_key',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Stripe secret key (stored encrypted)',
            ],
            [
                'key' => 'stripe_webhook_secret',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Stripe webhook signing secret',
            ],
            [
                'key' => 'stripe_mode',
                'value' => 'test',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Stripe mode: test or live',
            ],
            [
                'key' => 'paypal_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'payment',
                'description' => 'Enable PayPal payment gateway',
            ],
            [
                'key' => 'paypal_client_id',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'PayPal client ID',
            ],
            [
                'key' => 'paypal_secret',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'PayPal client secret (stored encrypted)',
            ],
            [
                'key' => 'paypal_mode',
                'value' => 'sandbox',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'PayPal mode: sandbox or live',
            ],
            [
                'key' => 'afterpay_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'payment',
                'description' => 'Enable Afterpay payment gateway',
            ],
            [
                'key' => 'afterpay_merchant_id',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Afterpay merchant ID',
            ],
            [
                'key' => 'afterpay_secret',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'payment',
                'description' => 'Afterpay secret key (stored encrypted)',
            ],
            [
                'key' => 'cod_enabled',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'payment',
                'description' => 'Enable Cash on Delivery payment option',
            ],

            // ============================================
            // Email Settings (group: email)
            // ============================================
            [
                'key' => 'smtp_host',
                'value' => 'smtp.mailtrap.io',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'SMTP server hostname',
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => Setting::TYPE_INTEGER,
                'group' => 'email',
                'description' => 'SMTP server port',
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'SMTP username',
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'SMTP password (stored encrypted)',
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'SMTP encryption: tls, ssl, or none',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Zambezi Meats',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'From name for outgoing emails',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'orders@zambezimeats.com.au',
                'type' => Setting::TYPE_STRING,
                'group' => 'email',
                'description' => 'From email address for outgoing emails',
            ],

            // ============================================
            // Currency Settings (group: currency)
            // ============================================
            [
                'key' => 'default_currency',
                'value' => 'AUD',
                'type' => Setting::TYPE_STRING,
                'group' => 'currency',
                'description' => 'Default currency code (ISO 4217)',
            ],
            [
                'key' => 'exchange_rate_api_key',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'currency',
                'description' => 'API key for exchange rate service',
            ],
            [
                'key' => 'exchange_rate_update_frequency',
                'value' => 'daily',
                'type' => Setting::TYPE_STRING,
                'group' => 'currency',
                'description' => 'How often to update exchange rates',
            ],
            [
                'key' => 'manual_usd_rate',
                'value' => '0.65',
                'type' => Setting::TYPE_FLOAT,
                'group' => 'currency',
                'description' => 'Manual AUD to USD exchange rate',
            ],
            [
                'key' => 'use_manual_rate',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'currency',
                'description' => 'Use manual exchange rate instead of API',
            ],

            // ============================================
            // Delivery Settings (group: delivery)
            // ============================================
            [
                'key' => 'minimum_order_amount',
                'value' => '100',
                'type' => Setting::TYPE_FLOAT,
                'group' => 'delivery',
                'description' => 'Minimum order amount for delivery (in default currency)',
            ],
            [
                'key' => 'free_delivery_threshold',
                'value' => '100',
                'type' => Setting::TYPE_FLOAT,
                'group' => 'delivery',
                'description' => 'Order amount for free delivery',
            ],
            [
                'key' => 'default_delivery_fee',
                'value' => '10',
                'type' => Setting::TYPE_FLOAT,
                'group' => 'delivery',
                'description' => 'Default delivery fee when not in a specific zone',
            ],

            // ============================================
            // Security Settings (group: security)
            // ============================================
            [
                'key' => 'session_timeout_minutes',
                'value' => '5',
                'type' => Setting::TYPE_INTEGER,
                'group' => 'security',
                'description' => 'Session timeout in minutes',
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => Setting::TYPE_INTEGER,
                'group' => 'security',
                'description' => 'Minimum password length',
            ],
            [
                'key' => 'password_require_uppercase',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'security',
                'description' => 'Require uppercase letters in passwords',
            ],
            [
                'key' => 'password_require_numbers',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'security',
                'description' => 'Require numbers in passwords',
            ],
            [
                'key' => 'password_require_symbols',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'security',
                'description' => 'Require special symbols in passwords',
            ],

            // ============================================
            // Notification Settings (group: notifications)
            // ============================================
            [
                'key' => 'order_notification_emails',
                'value' => json_encode(['orders@zambezimeats.com.au']),
                'type' => Setting::TYPE_JSON,
                'group' => 'notifications',
                'description' => 'Email addresses to receive new order notifications',
            ],
            [
                'key' => 'low_stock_notification_emails',
                'value' => json_encode(['inventory@zambezimeats.com.au']),
                'type' => Setting::TYPE_JSON,
                'group' => 'notifications',
                'description' => 'Email addresses to receive low stock alerts',
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'notifications',
                'description' => 'Enable email notifications system-wide',
            ],
            [
                'key' => 'enable_sms_notifications',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'notifications',
                'description' => 'Enable SMS notifications',
            ],

            // ============================================
            // Feature Flags (group: features)
            // ============================================
            [
                'key' => 'enable_wishlist',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'features',
                'description' => 'Enable wishlist functionality',
            ],
            [
                'key' => 'enable_reviews',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'features',
                'description' => 'Enable product reviews',
            ],
            [
                'key' => 'enable_guest_checkout',
                'value' => '0',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'features',
                'description' => 'Allow checkout without registration',
            ],
            [
                'key' => 'enable_multi_currency',
                'value' => '1',
                'type' => Setting::TYPE_BOOLEAN,
                'group' => 'features',
                'description' => 'Enable multi-currency support (AUD/USD)',
            ],

            // ============================================
            // SEO Settings (group: seo)
            // ============================================
            [
                'key' => 'meta_title',
                'value' => 'Zambezi Meats - Premium Quality Meats Delivered',
                'type' => Setting::TYPE_STRING,
                'group' => 'seo',
                'description' => 'Default meta title for SEO',
            ],
            [
                'key' => 'meta_description',
                'value' => 'Zambezi Meats offers premium quality beef, lamb, chicken, and specialty cuts delivered fresh to your door in Sydney and surrounding areas.',
                'type' => Setting::TYPE_STRING,
                'group' => 'seo',
                'description' => 'Default meta description for SEO',
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'butcher, meat delivery, beef, lamb, chicken, Sydney, fresh meat, premium meats',
                'type' => Setting::TYPE_STRING,
                'group' => 'seo',
                'description' => 'Default meta keywords for SEO',
            ],

            // ============================================
            // Social Media Settings (group: social)
            // ============================================
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/zambezimeats',
                'type' => Setting::TYPE_STRING,
                'group' => 'social',
                'description' => 'Facebook page URL',
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/zambezimeats',
                'type' => Setting::TYPE_STRING,
                'group' => 'social',
                'description' => 'Instagram profile URL',
            ],
            [
                'key' => 'twitter_url',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'social',
                'description' => 'Twitter/X profile URL',
            ],
            [
                'key' => 'youtube_url',
                'value' => '',
                'type' => Setting::TYPE_STRING,
                'group' => 'social',
                'description' => 'YouTube channel URL',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }

        $this->command->info('Settings seeded: ' . count($settings) . ' settings created/updated.');
    }
}
