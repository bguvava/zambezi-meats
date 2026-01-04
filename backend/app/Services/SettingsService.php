<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Settings Service
 *
 * Provides cached access to system settings from the database.
 * This service is the primary way to access settings throughout the application.
 *
 * @requirement SET-029 Cache settings for performance
 * @requirement SET-028 Provide settings access across system
 */
class SettingsService
{
    /**
     * Cache key prefix for settings.
     */
    private const CACHE_PREFIX = 'settings';

    /**
     * Cache duration in seconds (1 hour).
     */
    private const CACHE_DURATION = 3600;

    /**
     * Get a setting value by key.
     *
     * @param string $key The setting key
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . '_' . $key;

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($key, $default) {
            return Setting::getValue($key, $default);
        });
    }

    /**
     * Get all settings.
     *
     * @return array
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_PREFIX . '_all', self::CACHE_DURATION, function () {
            return Setting::all()
                ->mapWithKeys(fn($setting) => [$setting->key => $setting->getCastedValue()])
                ->toArray();
        });
    }

    /**
     * Get settings by group.
     *
     * @param string $group
     * @return array
     */
    public function getGroup(string $group): array
    {
        return Cache::remember(self::CACHE_PREFIX . '_group_' . $group, self::CACHE_DURATION, function () use ($group) {
            return Setting::getGroup($group);
        });
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '_all');

        $groups = ['store', 'operating', 'payment', 'email', 'currency', 'delivery', 'security', 'notifications', 'features', 'seo', 'social'];
        foreach ($groups as $group) {
            Cache::forget(self::CACHE_PREFIX . '_group_' . $group);
        }

        // Clear individual setting caches
        $settings = Setting::all();
        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . '_' . $setting->key);
        }
    }

    // ============================================
    // Payment Settings Accessors
    // ============================================

    /**
     * Check if Stripe is enabled.
     */
    public function isStripeEnabled(): bool
    {
        return (bool) $this->get('stripe_enabled', false);
    }

    /**
     * Get Stripe public key.
     */
    public function getStripePublicKey(): ?string
    {
        // First check database, then fall back to config
        $dbValue = $this->get('stripe_public_key');
        return $dbValue ?: config('services.stripe.key');
    }

    /**
     * Get Stripe secret key.
     */
    public function getStripeSecretKey(): ?string
    {
        // First check database, then fall back to config
        $dbValue = $this->get('stripe_secret_key');
        return $dbValue ?: config('services.stripe.secret');
    }

    /**
     * Get Stripe webhook secret.
     */
    public function getStripeWebhookSecret(): ?string
    {
        // First check database, then fall back to config
        $dbValue = $this->get('stripe_webhook_secret');
        return $dbValue ?: config('services.stripe.webhook_secret');
    }

    /**
     * Get Stripe mode (test/live).
     */
    public function getStripeMode(): string
    {
        return $this->get('stripe_mode', 'test');
    }

    /**
     * Check if PayPal is enabled.
     */
    public function isPayPalEnabled(): bool
    {
        return (bool) $this->get('paypal_enabled', false);
    }

    /**
     * Get PayPal client ID.
     */
    public function getPayPalClientId(): ?string
    {
        $dbValue = $this->get('paypal_client_id');
        return $dbValue ?: config('services.paypal.client_id');
    }

    /**
     * Get PayPal secret.
     */
    public function getPayPalSecret(): ?string
    {
        $dbValue = $this->get('paypal_secret');
        return $dbValue ?: config('services.paypal.client_secret');
    }

    /**
     * Get PayPal mode (sandbox/live).
     */
    public function getPayPalMode(): string
    {
        return $this->get('paypal_mode', 'sandbox');
    }

    /**
     * Check if Afterpay is enabled.
     */
    public function isAfterpayEnabled(): bool
    {
        return (bool) $this->get('afterpay_enabled', false);
    }

    /**
     * Get Afterpay merchant ID.
     */
    public function getAfterpayMerchantId(): ?string
    {
        $dbValue = $this->get('afterpay_merchant_id');
        return $dbValue ?: config('services.afterpay.merchant_id');
    }

    /**
     * Get Afterpay secret.
     */
    public function getAfterpaySecret(): ?string
    {
        $dbValue = $this->get('afterpay_secret');
        return $dbValue ?: config('services.afterpay.secret_key');
    }

    /**
     * Check if Cash on Delivery is enabled.
     */
    public function isCodEnabled(): bool
    {
        return (bool) $this->get('cod_enabled', true);
    }

    /**
     * Get all enabled payment methods.
     */
    public function getEnabledPaymentMethods(): array
    {
        $methods = [];

        if ($this->isStripeEnabled()) {
            $methods[] = 'stripe';
        }
        if ($this->isPayPalEnabled()) {
            $methods[] = 'paypal';
        }
        if ($this->isAfterpayEnabled()) {
            $methods[] = 'afterpay';
        }
        if ($this->isCodEnabled()) {
            $methods[] = 'cod';
        }

        return $methods;
    }

    // ============================================
    // Store Settings Accessors
    // ============================================

    /**
     * Get store name.
     */
    public function getStoreName(): string
    {
        return $this->get('store_name', 'Zambezi Meats');
    }

    /**
     * Get minimum order amount.
     */
    public function getMinimumOrderAmount(): float
    {
        return (float) $this->get('minimum_order_amount', 100.00);
    }

    /**
     * Get free delivery threshold.
     */
    public function getFreeDeliveryThreshold(): float
    {
        return (float) $this->get('free_delivery_threshold', 100.00);
    }

    /**
     * Get default delivery fee.
     */
    public function getDefaultDeliveryFee(): float
    {
        return (float) $this->get('default_delivery_fee', 10.00);
    }

    /**
     * Get default currency.
     */
    public function getDefaultCurrency(): string
    {
        return $this->get('default_currency', 'AUD');
    }

    // ============================================
    // Security Settings Accessors
    // ============================================

    /**
     * Get session timeout in minutes.
     */
    public function getSessionTimeoutMinutes(): int
    {
        return (int) $this->get('session_timeout_minutes', 5);
    }

    /**
     * Get password minimum length.
     */
    public function getPasswordMinLength(): int
    {
        return (int) $this->get('password_min_length', 8);
    }

    // ============================================
    // Feature Flags
    // ============================================

    /**
     * Check if wishlist is enabled.
     */
    public function isWishlistEnabled(): bool
    {
        return (bool) $this->get('enable_wishlist', true);
    }

    /**
     * Check if reviews are enabled.
     */
    public function isReviewsEnabled(): bool
    {
        return (bool) $this->get('enable_reviews', false);
    }

    /**
     * Check if guest checkout is enabled.
     */
    public function isGuestCheckoutEnabled(): bool
    {
        return (bool) $this->get('enable_guest_checkout', false);
    }

    /**
     * Check if multi-currency is enabled.
     */
    public function isMultiCurrencyEnabled(): bool
    {
        return (bool) $this->get('enable_multi_currency', true);
    }
}
