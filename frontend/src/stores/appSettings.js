/**
 * Application Settings Store
 *
 * Global Pinia store for managing public application settings.
 * Fetches settings from the backend API and provides them to all components
 * with caching, auto-refresh, and sensible defaults.
 *
 * @requirements SET-001 - Public settings access
 * @requirements SET-028 - Settings caching and refresh
 */

import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

const STORAGE_KEY = "zambezi_app_settings";
const CACHE_DURATION_MS = 5 * 60 * 1000; // 5 minutes
const REFRESH_INTERVAL_MS = 5 * 60 * 1000; // 5 minutes

/**
 * Application Settings Store
 * Provides centralized access to public application settings
 */
export const useAppSettingsStore = defineStore("appSettings", () => {
  // ============================================
  // State
  // ============================================

  /** @type {import('vue').Ref<Object>} Raw settings from API */
  const settings = ref({});

  /** @type {import('vue').Ref<boolean>} Loading state */
  const isLoading = ref(false);

  /** @type {import('vue').Ref<boolean>} Whether settings have been loaded at least once */
  const isLoaded = ref(false);

  /** @type {import('vue').Ref<string|null>} Error message if fetch failed */
  const error = ref(null);

  /** @type {import('vue').Ref<number|null>} Timestamp of last successful fetch */
  const lastFetchedAt = ref(null);

  /** @type {number|null} Interval ID for auto-refresh */
  let refreshIntervalId = null;

  // ============================================
  // Getters - Store Information
  // ============================================

  /**
   * Store name
   * @type {import('vue').ComputedRef<string>}
   */
  const storeName = computed(
    () => settings.value.store_name || "Zambezi Meats"
  );

  /**
   * Store tagline
   * @type {import('vue').ComputedRef<string>}
   */
  const storeTagline = computed(
    () => settings.value.store_tagline || "Premium Quality Meats"
  );

  /**
   * Store logo URL
   * @type {import('vue').ComputedRef<string|null>}
   */
  const storeLogo = computed(() => settings.value.store_logo || null);

  /**
   * Store phone number
   * @type {import('vue').ComputedRef<string>}
   */
  const storePhone = computed(() => settings.value.store_phone || "");

  /**
   * Store email address
   * @type {import('vue').ComputedRef<string>}
   */
  const storeEmail = computed(() => settings.value.store_email || "");

  /**
   * Store address object
   * @type {import('vue').ComputedRef<Object>}
   */
  const storeAddress = computed(() => ({
    street: settings.value.store_address_street || "",
    suburb: settings.value.store_address_suburb || "",
    state: settings.value.store_address_state || "",
    postcode: settings.value.store_address_postcode || "",
    country: settings.value.store_address_country || "Australia",
    full:
      [
        settings.value.store_address_street,
        settings.value.store_address_suburb,
        settings.value.store_address_state,
        settings.value.store_address_postcode,
      ]
        .filter(Boolean)
        .join(", ") || "",
  }));

  // ============================================
  // Getters - Order & Delivery Settings
  // ============================================

  /**
   * Minimum order amount in dollars
   * @type {import('vue').ComputedRef<number>}
   */
  const minimumOrderAmount = computed(() => {
    const value = settings.value.minimum_order_amount;
    return typeof value === "number" ? value : 100;
  });

  /**
   * Free delivery threshold in dollars
   * @type {import('vue').ComputedRef<number>}
   */
  const freeDeliveryThreshold = computed(() => {
    const value = settings.value.free_delivery_threshold;
    return typeof value === "number" ? value : 100;
  });

  /**
   * Default currency code
   * @type {import('vue').ComputedRef<string>}
   */
  const defaultCurrency = computed(
    () => settings.value.default_currency || "AUD"
  );

  // ============================================
  // Getters - Operating Hours
  // ============================================

  /**
   * Operating hours for each day of the week
   * @type {import('vue').ComputedRef<Object>}
   */
  const operatingHours = computed(() => {
    const defaultHours = { open: "09:00", close: "17:00", closed: false };
    return {
      monday: settings.value.operating_hours_monday || { ...defaultHours },
      tuesday: settings.value.operating_hours_tuesday || { ...defaultHours },
      wednesday: settings.value.operating_hours_wednesday || {
        ...defaultHours,
      },
      thursday: settings.value.operating_hours_thursday || { ...defaultHours },
      friday: settings.value.operating_hours_friday || { ...defaultHours },
      saturday: settings.value.operating_hours_saturday || {
        ...defaultHours,
        open: "09:00",
        close: "13:00",
      },
      sunday: settings.value.operating_hours_sunday || {
        ...defaultHours,
        closed: true,
      },
    };
  });

  /**
   * Whether the store is currently open based on operating hours
   * @type {import('vue').ComputedRef<boolean>}
   */
  const isStoreOpen = computed(() => {
    const now = new Date();
    const days = [
      "sunday",
      "monday",
      "tuesday",
      "wednesday",
      "thursday",
      "friday",
      "saturday",
    ];
    const today = days[now.getDay()];
    const todayHours = operatingHours.value[today];

    if (!todayHours || todayHours.closed) {
      return false;
    }

    const currentTime = now.getHours() * 60 + now.getMinutes();
    const [openHour, openMin] = (todayHours.open || "09:00")
      .split(":")
      .map(Number);
    const [closeHour, closeMin] = (todayHours.close || "17:00")
      .split(":")
      .map(Number);
    const openTime = openHour * 60 + openMin;
    const closeTime = closeHour * 60 + closeMin;

    return currentTime >= openTime && currentTime < closeTime;
  });

  // ============================================
  // Getters - Feature Flags
  // ============================================

  /**
   * Whether wishlist feature is enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const enableWishlist = computed(() => settings.value.enable_wishlist ?? true);

  /**
   * Whether reviews feature is enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const enableReviews = computed(() => settings.value.enable_reviews ?? false);

  /**
   * Whether guest checkout is enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const enableGuestCheckout = computed(
    () => settings.value.enable_guest_checkout ?? false
  );

  // ============================================
  // Getters - Payment Methods
  // ============================================

  /**
   * Whether Stripe payments are enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const stripeEnabled = computed(() => settings.value.stripe_enabled ?? false);

  /**
   * Whether PayPal payments are enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const paypalEnabled = computed(() => settings.value.paypal_enabled ?? false);

  /**
   * Whether Afterpay payments are enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const afterpayEnabled = computed(
    () => settings.value.afterpay_enabled ?? false
  );

  /**
   * Whether Cash on Delivery is enabled
   * @type {import('vue').ComputedRef<boolean>}
   */
  const codEnabled = computed(() => settings.value.cod_enabled ?? false);

  /**
   * Array of enabled payment methods
   * @type {import('vue').ComputedRef<string[]>}
   */
  const enabledPaymentMethods = computed(() => {
    const methods = [];
    if (stripeEnabled.value) methods.push("stripe");
    if (paypalEnabled.value) methods.push("paypal");
    if (afterpayEnabled.value) methods.push("afterpay");
    if (codEnabled.value) methods.push("cod");
    return methods;
  });

  // ============================================
  // Getters - Social & SEO
  // ============================================

  /**
   * Social media links
   * @type {import('vue').ComputedRef<Object>}
   */
  const socialLinks = computed(() => ({
    facebook: settings.value.social_facebook || "",
    instagram: settings.value.social_instagram || "",
    twitter: settings.value.social_twitter || "",
    youtube: settings.value.social_youtube || "",
  }));

  /**
   * Meta title for SEO
   * @type {import('vue').ComputedRef<string>}
   */
  const metaTitle = computed(
    () => settings.value.meta_title || storeName.value
  );

  /**
   * Meta description for SEO
   * @type {import('vue').ComputedRef<string>}
   */
  const metaDescription = computed(() => settings.value.meta_description || "");

  // ============================================
  // Private Helpers
  // ============================================

  /**
   * Check if cached settings are still valid
   * @returns {boolean}
   */
  function isCacheValid() {
    if (!lastFetchedAt.value) return false;
    return Date.now() - lastFetchedAt.value < CACHE_DURATION_MS;
  }

  /**
   * Load settings from localStorage
   * @returns {boolean} Whether settings were loaded from cache
   */
  function loadFromCache() {
    try {
      const cached = localStorage.getItem(STORAGE_KEY);
      if (!cached) return false;

      const { data, timestamp } = JSON.parse(cached);
      if (!data || !timestamp) return false;

      // Check if cache is expired
      if (Date.now() - timestamp >= CACHE_DURATION_MS) {
        localStorage.removeItem(STORAGE_KEY);
        return false;
      }

      settings.value = data;
      lastFetchedAt.value = timestamp;
      isLoaded.value = true;
      return true;
    } catch (e) {
      console.warn("[AppSettings] Failed to load from cache:", e);
      localStorage.removeItem(STORAGE_KEY);
      return false;
    }
  }

  /**
   * Save settings to localStorage
   */
  function saveToCache() {
    try {
      const cacheData = {
        data: settings.value,
        timestamp: lastFetchedAt.value,
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cacheData));
    } catch (e) {
      console.warn("[AppSettings] Failed to save to cache:", e);
    }
  }

  /**
   * Start the auto-refresh interval
   */
  function startAutoRefresh() {
    if (refreshIntervalId) return;

    refreshIntervalId = setInterval(() => {
      refreshSettings();
    }, REFRESH_INTERVAL_MS);
  }

  /**
   * Stop the auto-refresh interval
   */
  function stopAutoRefresh() {
    if (refreshIntervalId) {
      clearInterval(refreshIntervalId);
      refreshIntervalId = null;
    }
  }

  // ============================================
  // Actions
  // ============================================

  /**
   * Fetch settings from API if not cached or cache is expired
   * @returns {Promise<Object>} The settings object
   */
  async function fetchSettings() {
    // Return cached settings if valid
    if (isCacheValid() && isLoaded.value) {
      return settings.value;
    }

    return refreshSettings();
  }

  /**
   * Force refresh settings from API
   * @returns {Promise<Object>} The settings object
   */
  async function refreshSettings() {
    if (isLoading.value) return settings.value;

    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/settings/public");

      // Handle different response structures
      const data = response.data?.data || response.data || {};

      settings.value = data;
      lastFetchedAt.value = Date.now();
      isLoaded.value = true;

      // Save to cache
      saveToCache();

      return settings.value;
    } catch (e) {
      console.error("[AppSettings] Failed to fetch settings:", e);
      error.value = e.message || "Failed to load settings";

      // If we have cached data, continue using it
      if (!isLoaded.value) {
        loadFromCache();
      }

      throw e;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Get a single setting by key with a default value
   * @param {string} key - The setting key (supports dot notation)
   * @param {*} defaultValue - Default value if setting is not found
   * @returns {*} The setting value or default
   */
  function getSetting(key, defaultValue = null) {
    if (!key) return defaultValue;

    // Support dot notation for nested keys
    const keys = key.split(".");
    let value = settings.value;

    for (const k of keys) {
      if (value === null || value === undefined || typeof value !== "object") {
        return defaultValue;
      }
      value = value[k];
    }

    return value !== undefined && value !== null ? value : defaultValue;
  }

  /**
   * Check if a payment method is enabled
   * @param {string} method - Payment method identifier (stripe, paypal, afterpay, cod)
   * @returns {boolean} Whether the payment method is enabled
   */
  function isPaymentMethodEnabled(method) {
    if (!method) return false;

    const methodMap = {
      stripe: stripeEnabled.value,
      paypal: paypalEnabled.value,
      afterpay: afterpayEnabled.value,
      cod: codEnabled.value,
      "cash-on-delivery": codEnabled.value,
    };

    return methodMap[method.toLowerCase()] ?? false;
  }

  /**
   * Initialize the store - load from cache then fetch from API
   * @returns {Promise<void>}
   */
  async function initialize() {
    // Load from cache first for immediate availability
    loadFromCache();

    // Then fetch from API to get latest
    try {
      await fetchSettings();
    } catch (e) {
      // Error already logged, continue with cached data if available
    }

    // Start auto-refresh
    startAutoRefresh();
  }

  /**
   * Cleanup - stop auto-refresh interval
   */
  function cleanup() {
    stopAutoRefresh();
  }

  // ============================================
  // Return store interface
  // ============================================

  return {
    // State
    settings,
    isLoading,
    isLoaded,
    error,
    lastFetchedAt,

    // Getters - Store Info
    storeName,
    storeTagline,
    storeLogo,
    storePhone,
    storeEmail,
    storeAddress,

    // Getters - Order & Delivery
    minimumOrderAmount,
    freeDeliveryThreshold,
    defaultCurrency,

    // Getters - Operating Hours
    operatingHours,
    isStoreOpen,

    // Getters - Feature Flags
    enableWishlist,
    enableReviews,
    enableGuestCheckout,

    // Getters - Payment Methods
    stripeEnabled,
    paypalEnabled,
    afterpayEnabled,
    codEnabled,
    enabledPaymentMethods,

    // Getters - Social & SEO
    socialLinks,
    metaTitle,
    metaDescription,

    // Actions
    fetchSettings,
    refreshSettings,
    getSetting,
    isPaymentMethodEnabled,
    initialize,
    cleanup,
  };
});

/**
 * Initialize the app settings store
 * Call this from main.js after creating the Pinia instance
 *
 * @example
 * // In main.js
 * import { initializeAppSettings } from '@/stores/appSettings'
 *
 * const app = createApp(App)
 * const pinia = createPinia()
 * app.use(pinia)
 *
 * // Initialize settings
 * initializeAppSettings()
 *
 * @returns {Promise<void>}
 */
export async function initializeAppSettings() {
  const store = useAppSettingsStore();
  await store.initialize();
}

export default useAppSettingsStore;
