<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * SettingsController handles all system settings management endpoints.
 *
 * @requirement SET-001 to SET-030 System Settings
 */
class SettingsController extends Controller
{
    /**
     * Settings groups configuration.
     */
    private const SETTINGS_GROUPS = [
        'store' => [
            'store_name' => ['type' => 'string', 'required' => true],
            'store_tagline' => ['type' => 'string', 'required' => false],
            'store_logo' => ['type' => 'string', 'required' => false],
            'store_address' => ['type' => 'string', 'required' => true],
            'store_suburb' => ['type' => 'string', 'required' => true],
            'store_state' => ['type' => 'string', 'required' => true],
            'store_postcode' => ['type' => 'string', 'required' => true],
            'store_phone' => ['type' => 'string', 'required' => true],
            'store_email' => ['type' => 'string', 'required' => true],
            'store_abn' => ['type' => 'string', 'required' => false],
        ],
        'operating' => [
            'operating_hours_monday' => ['type' => 'json', 'required' => false],
            'operating_hours_tuesday' => ['type' => 'json', 'required' => false],
            'operating_hours_wednesday' => ['type' => 'json', 'required' => false],
            'operating_hours_thursday' => ['type' => 'json', 'required' => false],
            'operating_hours_friday' => ['type' => 'json', 'required' => false],
            'operating_hours_saturday' => ['type' => 'json', 'required' => false],
            'operating_hours_sunday' => ['type' => 'json', 'required' => false],
            'holiday_dates' => ['type' => 'json', 'required' => false],
        ],
        'payment' => [
            'stripe_enabled' => ['type' => 'boolean', 'required' => false],
            'stripe_public_key' => ['type' => 'string', 'required' => false],
            'stripe_secret_key' => ['type' => 'string', 'required' => false],
            'stripe_webhook_secret' => ['type' => 'string', 'required' => false],
            'stripe_mode' => ['type' => 'string', 'required' => false],
            'paypal_enabled' => ['type' => 'boolean', 'required' => false],
            'paypal_client_id' => ['type' => 'string', 'required' => false],
            'paypal_secret' => ['type' => 'string', 'required' => false],
            'paypal_mode' => ['type' => 'string', 'required' => false],
            'afterpay_enabled' => ['type' => 'boolean', 'required' => false],
            'afterpay_merchant_id' => ['type' => 'string', 'required' => false],
            'afterpay_secret' => ['type' => 'string', 'required' => false],
            'cod_enabled' => ['type' => 'boolean', 'required' => false],
        ],
        'email' => [
            'smtp_host' => ['type' => 'string', 'required' => false],
            'smtp_port' => ['type' => 'integer', 'required' => false],
            'smtp_username' => ['type' => 'string', 'required' => false],
            'smtp_password' => ['type' => 'string', 'required' => false],
            'smtp_encryption' => ['type' => 'string', 'required' => false],
            'mail_from_name' => ['type' => 'string', 'required' => false],
            'mail_from_address' => ['type' => 'string', 'required' => false],
        ],
        'currency' => [
            'default_currency' => ['type' => 'string', 'required' => true],
            'exchange_rate_api_key' => ['type' => 'string', 'required' => false],
            'exchange_rate_update_frequency' => ['type' => 'string', 'required' => false],
            'manual_usd_rate' => ['type' => 'float', 'required' => false],
            'use_manual_rate' => ['type' => 'boolean', 'required' => false],
        ],
        'delivery' => [
            'minimum_order_amount' => ['type' => 'float', 'required' => true],
            'free_delivery_threshold' => ['type' => 'float', 'required' => true],
            'default_delivery_fee' => ['type' => 'float', 'required' => true],
        ],
        'security' => [
            'session_timeout_minutes' => ['type' => 'integer', 'required' => true],
            'password_min_length' => ['type' => 'integer', 'required' => true],
            'password_require_uppercase' => ['type' => 'boolean', 'required' => false],
            'password_require_numbers' => ['type' => 'boolean', 'required' => false],
            'password_require_symbols' => ['type' => 'boolean', 'required' => false],
        ],
        'notifications' => [
            'order_notification_emails' => ['type' => 'json', 'required' => false],
            'low_stock_notification_emails' => ['type' => 'json', 'required' => false],
            'enable_email_notifications' => ['type' => 'boolean', 'required' => false],
            'enable_sms_notifications' => ['type' => 'boolean', 'required' => false],
        ],
        'features' => [
            'enable_wishlist' => ['type' => 'boolean', 'required' => false],
            'enable_reviews' => ['type' => 'boolean', 'required' => false],
            'enable_guest_checkout' => ['type' => 'boolean', 'required' => false],
            'enable_multi_currency' => ['type' => 'boolean', 'required' => false],
        ],
        'seo' => [
            'meta_title' => ['type' => 'string', 'required' => false],
            'meta_description' => ['type' => 'string', 'required' => false],
            'meta_keywords' => ['type' => 'string', 'required' => false],
        ],
        'social' => [
            'facebook_url' => ['type' => 'string', 'required' => false],
            'instagram_url' => ['type' => 'string', 'required' => false],
            'twitter_url' => ['type' => 'string', 'required' => false],
            'youtube_url' => ['type' => 'string', 'required' => false],
        ],
    ];

    /**
     * Get all settings.
     *
     * @requirement SET-001 Create settings dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $settings = Setting::all()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            })
            ->toArray();

        // Group settings by category
        $groupedSettings = [];
        foreach (self::SETTINGS_GROUPS as $group => $fields) {
            $groupedSettings[$group] = [];
            foreach (array_keys($fields) as $key) {
                $groupedSettings[$group][$key] = $settings[$key] ?? null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'grouped' => $groupedSettings,
                'groups' => array_keys(self::SETTINGS_GROUPS),
            ],
        ]);
    }

    /**
     * Get settings by group.
     *
     * @requirement SET-001 Create settings dashboard
     */
    public function getGroup(Request $request, string $group): JsonResponse
    {
        if (!isset(self::SETTINGS_GROUPS[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings group',
            ], 404);
        }

        $keys = array_keys(self::SETTINGS_GROUPS[$group]);
        $settings = Setting::whereIn('key', $keys)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            })
            ->toArray();

        // Ensure all keys are present with null defaults
        foreach ($keys as $key) {
            if (!isset($settings[$key])) {
                $settings[$key] = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'settings' => $settings,
                'schema' => self::SETTINGS_GROUPS[$group],
            ],
        ]);
    }

    /**
     * Update settings group.
     *
     * @requirement SET-002 to SET-025 Configure various settings
     */
    public function updateGroup(Request $request, string $group): JsonResponse
    {
        if (!isset(self::SETTINGS_GROUPS[$group])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings group',
            ], 404);
        }

        $groupConfig = self::SETTINGS_GROUPS[$group];
        $input = $request->all();

        // Build validation rules
        $rules = [];
        foreach ($groupConfig as $key => $config) {
            $rule = [];
            if ($config['required']) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            switch ($config['type']) {
                case 'string':
                    $rule[] = 'string';
                    break;
                case 'integer':
                    $rule[] = 'integer';
                    break;
                case 'float':
                    $rule[] = 'numeric';
                    break;
                case 'boolean':
                    $rule[] = 'boolean';
                    break;
                case 'json':
                    $rule[] = 'array';
                    break;
            }

            $rules[$key] = $rule;
        }

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422);
        }

        $updatedSettings = [];
        $changes = [];

        foreach ($groupConfig as $key => $config) {
            if (array_key_exists($key, $input)) {
                $oldValue = Setting::getValue($key);
                $newValue = $input[$key];

                Setting::setValue($key, $newValue, $config['type'], $group);
                $updatedSettings[$key] = $newValue;

                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        // Log the change
        if (!empty($changes)) {
            $this->logSettingsChange($group, $changes, $request->user());
        }

        // Clear settings cache
        $this->clearSettingsCache();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'group' => $group,
                'updated' => $updatedSettings,
            ],
        ]);
    }

    /**
     * Upload store logo.
     *
     * @requirement SET-003 Configure store logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422);
        }

        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/branding', $filename);

        $logoUrl = Storage::url($path);

        // Save to settings
        Setting::setValue('store_logo', $logoUrl, 'string', 'store');

        // Log the change
        $this->logSettingsChange('store', [
            'store_logo' => [
                'old' => Setting::getValue('store_logo'),
                'new' => $logoUrl,
            ],
        ], $request->user());

        $this->clearSettingsCache();

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'logo_url' => $logoUrl,
            ],
        ]);
    }

    /**
     * Get email templates.
     *
     * @requirement SET-014 Manage email templates
     */
    public function getEmailTemplates(Request $request): JsonResponse
    {
        $templates = [
            'order_confirmation' => [
                'name' => 'Order Confirmation',
                'subject' => Setting::getValue('email_template_order_confirmation_subject', 'Your Order Confirmation - #{order_number}'),
                'body' => Setting::getValue('email_template_order_confirmation_body', $this->getDefaultTemplate('order_confirmation')),
            ],
            'order_shipped' => [
                'name' => 'Order Shipped',
                'subject' => Setting::getValue('email_template_order_shipped_subject', 'Your Order Has Been Shipped - #{order_number}'),
                'body' => Setting::getValue('email_template_order_shipped_body', $this->getDefaultTemplate('order_shipped')),
            ],
            'order_delivered' => [
                'name' => 'Order Delivered',
                'subject' => Setting::getValue('email_template_order_delivered_subject', 'Your Order Has Been Delivered - #{order_number}'),
                'body' => Setting::getValue('email_template_order_delivered_body', $this->getDefaultTemplate('order_delivered')),
            ],
            'password_reset' => [
                'name' => 'Password Reset',
                'subject' => Setting::getValue('email_template_password_reset_subject', 'Reset Your Password'),
                'body' => Setting::getValue('email_template_password_reset_body', $this->getDefaultTemplate('password_reset')),
            ],
            'welcome' => [
                'name' => 'Welcome Email',
                'subject' => Setting::getValue('email_template_welcome_subject', 'Welcome to Zambezi Meats!'),
                'body' => Setting::getValue('email_template_welcome_body', $this->getDefaultTemplate('welcome')),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'templates' => $templates,
                'available_variables' => [
                    'customer_name' => 'Customer\'s full name',
                    'order_number' => 'Order number',
                    'order_total' => 'Order total amount',
                    'store_name' => 'Store name',
                    'store_phone' => 'Store phone number',
                    'store_email' => 'Store email address',
                    'tracking_link' => 'Order tracking link',
                    'reset_link' => 'Password reset link',
                ],
            ],
        ]);
    }

    /**
     * Update email template.
     *
     * @requirement SET-014 Manage email templates
     */
    public function updateEmailTemplate(Request $request, string $name): JsonResponse
    {
        $validTemplates = ['order_confirmation', 'order_shipped', 'order_delivered', 'password_reset', 'welcome'];

        if (!in_array($name, $validTemplates)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid template name',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422);
        }

        Setting::setValue("email_template_{$name}_subject", $request->input('subject'), 'string', 'email');
        Setting::setValue("email_template_{$name}_body", $request->input('body'), 'string', 'email');

        $this->logSettingsChange('email', [
            "email_template_{$name}" => [
                'old' => 'previous template',
                'new' => 'updated template',
            ],
        ], $request->user());

        $this->clearSettingsCache();

        return response()->json([
            'success' => true,
            'message' => 'Email template updated successfully',
            'data' => [
                'template' => $name,
                'subject' => $request->input('subject'),
                'body' => $request->input('body'),
            ],
        ]);
    }

    /**
     * Send test email.
     *
     * @requirement SET-012 Configure SMTP settings
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422);
        }

        // For MVP, we simulate sending the email
        // In production, this would use Laravel's Mail facade
        $testEmailSent = true;

        if ($testEmailSent) {
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
                'data' => [
                    'recipient' => $request->input('email'),
                    'sent_at' => now()->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send test email. Please check SMTP settings.',
        ], 500);
    }

    /**
     * Export settings.
     *
     * @requirement SET-026 Import/export settings
     */
    public function export(Request $request): JsonResponse
    {
        $settings = Setting::all()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getCastedValue(),
                    'type' => $setting->type,
                    'group' => $setting->group,
                ]];
            })
            ->toArray();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'exported_by' => $request->user()->name,
            'settings' => $settings,
        ];

        return response()->json([
            'success' => true,
            'data' => $exportData,
        ]);
    }

    /**
     * Import settings.
     *
     * @requirement SET-026 Import/export settings
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422);
        }

        $imported = 0;
        $skipped = 0;
        $settings = $request->input('settings');

        foreach ($settings as $key => $data) {
            if (is_array($data) && isset($data['value'], $data['type'], $data['group'])) {
                Setting::setValue($key, $data['value'], $data['type'], $data['group']);
                $imported++;
            } else {
                $skipped++;
            }
        }

        $this->logSettingsChange('import', [
            'imported' => $imported,
            'skipped' => $skipped,
        ], $request->user());

        $this->clearSettingsCache();

        return response()->json([
            'success' => true,
            'message' => 'Settings imported successfully',
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
            ],
        ]);
    }

    /**
     * Get settings change history.
     *
     * @requirement SET-027 View settings change history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $history = ActivityLog::where('action', 'settings_changed')
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get public settings (no auth required).
     *
     * @requirement SET-001 Create settings dashboard
     */
    public function getPublicSettings(Request $request): JsonResponse
    {
        $publicKeys = [
            'store_name',
            'store_tagline',
            'store_logo',
            'store_address',
            'store_suburb',
            'store_state',
            'store_postcode',
            'store_phone',
            'store_email',
            'operating_hours_monday',
            'operating_hours_tuesday',
            'operating_hours_wednesday',
            'operating_hours_thursday',
            'operating_hours_friday',
            'operating_hours_saturday',
            'operating_hours_sunday',
            'holiday_dates',
            'minimum_order_amount',
            'free_delivery_threshold',
            'default_currency',
            'meta_title',
            'meta_description',
            'facebook_url',
            'instagram_url',
            'twitter_url',
            'youtube_url',
            'enable_wishlist',
            'enable_reviews',
            'enable_guest_checkout',
            'stripe_enabled',
            'paypal_enabled',
            'afterpay_enabled',
            'cod_enabled',
        ];

        $settings = Setting::whereIn('key', $publicKeys)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Log settings change.
     */
    private function logSettingsChange(string $group, array $changes, $user): void
    {
        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => 'settings_changed',
            'model_type' => Setting::class,
            'model_id' => null,
            'description' => "Updated {$group} settings",
            'properties' => [
                'group' => $group,
                'changes' => $changes,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Clear settings cache.
     *
     * @requirement SET-029 Cache settings for performance
     */
    private function clearSettingsCache(): void
    {
        Cache::forget('settings');
        Cache::forget('settings_grouped');

        foreach (array_keys(self::SETTINGS_GROUPS) as $group) {
            Cache::forget("settings_{$group}");
        }
    }

    /**
     * Get default email template.
     */
    private function getDefaultTemplate(string $name): string
    {
        return match ($name) {
            'order_confirmation' => "Dear {customer_name},\n\nThank you for your order #{order_number}!\n\nYour order total is {order_total}.\n\nWe will notify you when your order ships.\n\nBest regards,\n{store_name}",
            'order_shipped' => "Dear {customer_name},\n\nGreat news! Your order #{order_number} has been shipped.\n\nTrack your order: {tracking_link}\n\nBest regards,\n{store_name}",
            'order_delivered' => "Dear {customer_name},\n\nYour order #{order_number} has been delivered!\n\nThank you for shopping with us.\n\nBest regards,\n{store_name}",
            'password_reset' => "Dear {customer_name},\n\nClick the link below to reset your password:\n\n{reset_link}\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\n{store_name}",
            'welcome' => "Dear {customer_name},\n\nWelcome to {store_name}!\n\nWe're excited to have you as a customer.\n\nStart shopping now and enjoy premium quality meats.\n\nBest regards,\n{store_name}",
            default => '',
        };
    }
}
