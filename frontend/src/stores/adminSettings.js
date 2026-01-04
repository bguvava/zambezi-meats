/**
 * Admin Settings Store
 * Manages all system settings for the admin dashboard
 * Requirements: SET-001 to SET-030
 */

import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

/**
 * Settings group definitions
 * @type {string[]}
 */
const SETTINGS_GROUPS = [
  "store",
  "operating",
  "payment",
  "email",
  "currency",
  "delivery",
  "security",
  "notifications",
  "features",
  "seo",
  "social",
];

/**
 * Default settings structure by group
 */
const DEFAULT_SETTINGS = {
  store: {
    store_name: "",
    tagline: "",
    logo: "",
    address: "",
    suburb: "",
    state: "",
    postcode: "",
    phone: "",
    email: "",
    abn: "",
  },
  operating: {
    hours: {
      monday: { open: "09:00", close: "17:00", closed: false },
      tuesday: { open: "09:00", close: "17:00", closed: false },
      wednesday: { open: "09:00", close: "17:00", closed: false },
      thursday: { open: "09:00", close: "17:00", closed: false },
      friday: { open: "09:00", close: "17:00", closed: false },
      saturday: { open: "09:00", close: "13:00", closed: false },
      sunday: { open: "", close: "", closed: true },
    },
    holiday_dates: [],
  },
  payment: {
    stripe_enabled: false,
    stripe_public_key: "",
    stripe_secret_key: "",
    paypal_enabled: false,
    paypal_client_id: "",
    paypal_secret: "",
    afterpay_enabled: false,
    afterpay_merchant_id: "",
    afterpay_secret: "",
    cod_enabled: false,
  },
  email: {
    smtp_host: "",
    smtp_port: 587,
    smtp_username: "",
    smtp_password: "",
    smtp_encryption: "tls",
    mail_from_name: "",
    mail_from_address: "",
  },
  currency: {
    default_currency: "AUD",
    exchange_rate_api_key: "",
    manual_exchange_rate: null,
    rate_update_frequency: "daily",
  },
  delivery: {
    minimum_order_amount: 0,
    free_delivery_threshold: 0,
    default_delivery_fee: 0,
  },
  security: {
    session_timeout: 120,
    password_min_length: 8,
    require_uppercase: true,
    require_lowercase: true,
    require_number: true,
    require_special: false,
  },
  notifications: {
    order_notification_emails: [],
    low_stock_emails: [],
    enable_email_notifications: true,
    enable_sms_notifications: false,
  },
  features: {
    enable_wishlist: true,
    enable_reviews: true,
    enable_guest_checkout: true,
    enable_multi_currency: false,
  },
  seo: {
    meta_title: "",
    meta_description: "",
    meta_keywords: "",
  },
  social: {
    facebook_url: "",
    instagram_url: "",
    twitter_url: "",
    youtube_url: "",
  },
};

export const useAdminSettingsStore = defineStore("adminSettings", () => {
  // ============================================
  // State
  // ============================================

  /** @type {import('vue').Ref<Object>} All settings flat */
  const settings = ref({});

  /** @type {import('vue').Ref<Object>} Settings grouped by category */
  const grouped = ref({});

  /** @type {import('vue').Ref<string[]>} Available group names */
  const groups = ref([...SETTINGS_GROUPS]);

  /** @type {import('vue').Ref<string>} Currently selected group */
  const currentGroup = ref("store");

  /** @type {import('vue').Ref<Object>} Email template configurations */
  const emailTemplates = ref({});

  /** @type {import('vue').Ref<Object>} Settings change history */
  const history = ref({
    data: [],
    pagination: {},
  });

  /** @type {import('vue').Ref<boolean>} Loading state */
  const loading = ref(false);

  /** @type {import('vue').Ref<boolean>} Saving state */
  const saving = ref(false);

  /** @type {import('vue').Ref<string|null>} Error message */
  const error = ref(null);

  /** @type {import('vue').Ref<Object>} Original settings for change detection */
  const originalSettings = ref({});

  // ============================================
  // Getters
  // ============================================

  /**
   * Settings for the currently selected group
   * @returns {Object}
   */
  const currentGroupSettings = computed(() => {
    return (
      grouped.value[currentGroup.value] ||
      DEFAULT_SETTINGS[currentGroup.value] ||
      {}
    );
  });

  /**
   * Loading state indicator
   * @returns {boolean}
   */
  const isLoading = computed(() => loading.value);

  /**
   * Saving state indicator
   * @returns {boolean}
   */
  const isSaving = computed(() => saving.value);

  /**
   * Check if there are unsaved changes
   * @returns {boolean}
   */
  const hasChanges = computed(() => {
    const current = JSON.stringify(grouped.value[currentGroup.value] || {});
    const original = JSON.stringify(
      originalSettings.value[currentGroup.value] || {}
    );
    return current !== original;
  });

  /**
   * Get a specific setting value by key
   * @returns {function(string): any}
   */
  const getSettingValue = computed(() => {
    return (key) => {
      // Check in flat settings first
      if (settings.value[key] !== undefined) {
        return settings.value[key];
      }
      // Check in grouped settings
      for (const group of Object.values(grouped.value)) {
        if (group[key] !== undefined) {
          return group[key];
        }
      }
      return null;
    };
  });

  /**
   * Get which payment methods are enabled
   * @returns {Object}
   */
  const paymentMethodsEnabled = computed(() => {
    const payment = grouped.value.payment || {};
    return {
      stripe: payment.stripe_enabled || false,
      paypal: payment.paypal_enabled || false,
      afterpay: payment.afterpay_enabled || false,
      cod: payment.cod_enabled || false,
    };
  });

  /**
   * Get feature flags
   * @returns {Object}
   */
  const featureFlags = computed(() => {
    const features = grouped.value.features || {};
    return {
      wishlist: features.enable_wishlist || false,
      reviews: features.enable_reviews || false,
      guestCheckout: features.enable_guest_checkout || false,
      multiCurrency: features.enable_multi_currency || false,
    };
  });

  /**
   * Get store information
   * @returns {Object}
   */
  const storeInfo = computed(() => {
    return grouped.value.store || {};
  });

  // ============================================
  // Actions
  // ============================================

  /**
   * Fetch all settings from the API
   * @returns {Promise<Object>}
   */
  async function fetchAllSettings() {
    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getSettings();

      if (response.data) {
        settings.value = response.data.settings || response.data;
        grouped.value =
          response.data.grouped || organizeByGroup(settings.value);
        originalSettings.value = JSON.parse(JSON.stringify(grouped.value));

        if (response.data.groups) {
          groups.value = response.data.groups;
        }
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to fetch settings";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Fetch settings for a specific group
   * @param {string} group - The settings group to fetch
   * @returns {Promise<Object>}
   */
  async function fetchSettingsGroup(group) {
    if (!SETTINGS_GROUPS.includes(group)) {
      throw new Error(`Invalid settings group: ${group}`);
    }

    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getSettingsGroup(group);

      if (response.data) {
        grouped.value[group] = response.data;
        originalSettings.value[group] = JSON.parse(
          JSON.stringify(response.data)
        );

        // Update flat settings
        Object.entries(response.data).forEach(([key, value]) => {
          settings.value[key] = value;
        });
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        `Failed to fetch ${group} settings`;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update settings for a specific group
   * @param {string} group - The settings group to update
   * @param {Object} data - The settings data to save
   * @returns {Promise<Object>}
   */
  async function updateSettingsGroup(group, data) {
    if (!SETTINGS_GROUPS.includes(group)) {
      throw new Error(`Invalid settings group: ${group}`);
    }

    saving.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateSettingsGroup(group, data);

      if (response.data) {
        grouped.value[group] = response.data.settings || data;
        originalSettings.value[group] = JSON.parse(
          JSON.stringify(grouped.value[group])
        );

        // Update flat settings
        Object.entries(grouped.value[group]).forEach(([key, value]) => {
          settings.value[key] = value;
        });
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        `Failed to update ${group} settings`;
      throw err;
    } finally {
      saving.value = false;
    }
  }

  /**
   * Upload store logo
   * @param {File} file - The logo file to upload
   * @returns {Promise<Object>}
   */
  async function uploadStoreLogo(file) {
    saving.value = true;
    error.value = null;

    try {
      const formData = new FormData();
      formData.append("logo", file);

      const response = await adminDashboard.uploadLogo(formData);

      if (response.data?.logo_url) {
        // Update logo in store settings
        if (grouped.value.store) {
          grouped.value.store.logo = response.data.logo_url;
        }
        settings.value.logo = response.data.logo_url;
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message || err.message || "Failed to upload logo";
      throw err;
    } finally {
      saving.value = false;
    }
  }

  /**
   * Fetch all email templates
   * @returns {Promise<Object>}
   */
  async function fetchEmailTemplates() {
    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getEmailTemplates();
      emailTemplates.value = response.data || {};
      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to fetch email templates";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update an email template
   * @param {string} name - The template name
   * @param {Object} data - The template data
   * @returns {Promise<Object>}
   */
  async function updateEmailTemplate(name, data) {
    saving.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateEmailTemplate(name, data);

      if (response.data) {
        emailTemplates.value[name] = response.data;
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to update email template";
      throw err;
    } finally {
      saving.value = false;
    }
  }

  /**
   * Send a test email
   * @param {string} email - The email address to send to
   * @returns {Promise<Object>}
   */
  async function sendTestEmail(email) {
    saving.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.sendTestEmail(email);
      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to send test email";
      throw err;
    } finally {
      saving.value = false;
    }
  }

  /**
   * Export all settings to JSON
   * @returns {Promise<Object>}
   */
  async function exportSettings() {
    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.exportSettings();
      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to export settings";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Import settings from JSON
   * @param {Object} data - The settings data to import
   * @returns {Promise<Object>}
   */
  async function importSettings(data) {
    saving.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.importSettings(data);

      // Refresh all settings after import
      await fetchAllSettings();

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to import settings";
      throw err;
    } finally {
      saving.value = false;
    }
  }

  /**
   * Fetch settings change history
   * @param {Object} params - Query parameters (page, per_page, etc.)
   * @returns {Promise<Object>}
   */
  async function fetchHistory(params = {}) {
    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getSettingsHistory(params);

      history.value = {
        data: response.data?.data || response.data || [],
        pagination: response.data?.meta || response.data?.pagination || {},
      };

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to fetch settings history";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Fetch public settings (no auth required)
   * @returns {Promise<Object>}
   */
  async function fetchPublicSettings() {
    loading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getPublicSettings();

      if (response.data) {
        // Only update public-safe settings
        const publicData = response.data;
        Object.entries(publicData).forEach(([key, value]) => {
          settings.value[key] = value;
        });
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Failed to fetch public settings";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Set the currently active settings group
   * @param {string} group - The group to set as active
   */
  function setCurrentGroup(group) {
    if (SETTINGS_GROUPS.includes(group)) {
      currentGroup.value = group;
    }
  }

  /**
   * Reset a group to its default settings
   * @param {string} group - The group to reset
   * @returns {Object} The default settings for the group
   */
  function resetGroupToDefaults(group) {
    if (!SETTINGS_GROUPS.includes(group)) {
      throw new Error(`Invalid settings group: ${group}`);
    }

    const defaults = DEFAULT_SETTINGS[group] || {};
    grouped.value[group] = JSON.parse(JSON.stringify(defaults));

    return grouped.value[group];
  }

  /**
   * Clear the error state
   */
  function clearError() {
    error.value = null;
  }

  /**
   * Update a single setting value locally
   * @param {string} group - The settings group
   * @param {string} key - The setting key
   * @param {any} value - The new value
   */
  function updateLocalSetting(group, key, value) {
    if (!grouped.value[group]) {
      grouped.value[group] = {};
    }
    grouped.value[group][key] = value;
    settings.value[key] = value;
  }

  /**
   * Revert changes for a group to the last saved state
   * @param {string} group - The group to revert
   */
  function revertGroupChanges(group) {
    if (originalSettings.value[group]) {
      grouped.value[group] = JSON.parse(
        JSON.stringify(originalSettings.value[group])
      );
    }
  }

  /**
   * Check if a specific group has unsaved changes
   * @param {string} group - The group to check
   * @returns {boolean}
   */
  function groupHasChanges(group) {
    const current = JSON.stringify(grouped.value[group] || {});
    const original = JSON.stringify(originalSettings.value[group] || {});
    return current !== original;
  }

  // ============================================
  // Helper Functions
  // ============================================

  /**
   * Organize flat settings into groups
   * @param {Object} flatSettings - Flat settings object
   * @returns {Object} Grouped settings
   */
  function organizeByGroup(flatSettings) {
    const result = {};

    SETTINGS_GROUPS.forEach((group) => {
      result[group] = {};
    });

    // Map known setting keys to their groups
    const keyGroupMap = {
      // Store
      store_name: "store",
      tagline: "store",
      logo: "store",
      address: "store",
      suburb: "store",
      state: "store",
      postcode: "store",
      phone: "store",
      email: "store",
      abn: "store",

      // Operating
      hours: "operating",
      holiday_dates: "operating",

      // Payment
      stripe_enabled: "payment",
      stripe_public_key: "payment",
      stripe_secret_key: "payment",
      paypal_enabled: "payment",
      paypal_client_id: "payment",
      paypal_secret: "payment",
      afterpay_enabled: "payment",
      afterpay_merchant_id: "payment",
      afterpay_secret: "payment",
      cod_enabled: "payment",

      // Email
      smtp_host: "email",
      smtp_port: "email",
      smtp_username: "email",
      smtp_password: "email",
      smtp_encryption: "email",
      mail_from_name: "email",
      mail_from_address: "email",

      // Currency
      default_currency: "currency",
      exchange_rate_api_key: "currency",
      manual_exchange_rate: "currency",
      rate_update_frequency: "currency",

      // Delivery
      minimum_order_amount: "delivery",
      free_delivery_threshold: "delivery",
      default_delivery_fee: "delivery",

      // Security
      session_timeout: "security",
      password_min_length: "security",
      require_uppercase: "security",
      require_lowercase: "security",
      require_number: "security",
      require_special: "security",

      // Notifications
      order_notification_emails: "notifications",
      low_stock_emails: "notifications",
      enable_email_notifications: "notifications",
      enable_sms_notifications: "notifications",

      // Features
      enable_wishlist: "features",
      enable_reviews: "features",
      enable_guest_checkout: "features",
      enable_multi_currency: "features",

      // SEO
      meta_title: "seo",
      meta_description: "seo",
      meta_keywords: "seo",

      // Social
      facebook_url: "social",
      instagram_url: "social",
      twitter_url: "social",
      youtube_url: "social",
    };

    Object.entries(flatSettings).forEach(([key, value]) => {
      const group = keyGroupMap[key];
      if (group) {
        result[group][key] = value;
      }
    });

    return result;
  }

  // ============================================
  // Return Store
  // ============================================

  return {
    // State
    settings,
    grouped,
    groups,
    currentGroup,
    emailTemplates,
    history,
    loading,
    saving,
    error,

    // Getters
    currentGroupSettings,
    isLoading,
    isSaving,
    hasChanges,
    getSettingValue,
    paymentMethodsEnabled,
    featureFlags,
    storeInfo,

    // Actions
    fetchAllSettings,
    fetchSettingsGroup,
    updateSettingsGroup,
    uploadStoreLogo,
    fetchEmailTemplates,
    updateEmailTemplate,
    sendTestEmail,
    exportSettings,
    importSettings,
    fetchHistory,
    fetchPublicSettings,
    setCurrentGroup,
    resetGroupToDefaults,
    clearError,
    updateLocalSetting,
    revertGroupChanges,
    groupHasChanges,
  };
});
