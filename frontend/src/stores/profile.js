/**
 * Profile Store
 *
 * Manages customer profile state and operations.
 *
 * @requirement CUST-008 Profile management
 * @requirement CUST-009 Password change
 * @requirement CUST-019 Currency preference
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useProfileStore = defineStore("profile", () => {
  // State
  const profile = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const isUpdating = ref(false);
  const updateError = ref(null);

  // Getters
  const userName = computed(() => profile.value?.name || "");
  const userEmail = computed(() => profile.value?.email || "");
  const userPhone = computed(() => profile.value?.phone || "");
  const currencyPreference = computed(
    () => profile.value?.currency_preference || "AUD"
  );
  const hasProfile = computed(() => profile.value !== null);

  // Actions

  /**
   * Fetch customer profile
   */
  async function fetchProfile() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/customer/profile");

      if (response.data.success) {
        profile.value = response.data.profile;
      } else {
        profile.value = response.data;
      }

      return { success: true, profile: profile.value };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch profile";
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Update customer profile
   * @param {Object} data - Profile data to update
   */
  async function updateProfile(data) {
    isUpdating.value = true;
    updateError.value = null;

    try {
      const response = await api.put("/customer/profile", data);

      if (response.data.success) {
        profile.value = response.data.profile;
      }

      return {
        success: true,
        message: "Profile updated successfully",
        profile: profile.value,
      };
    } catch (err) {
      updateError.value =
        err.response?.data?.message || "Failed to update profile";
      return {
        success: false,
        message: updateError.value,
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Change password
   * @param {Object} data - Password data
   */
  async function changePassword(data) {
    isUpdating.value = true;
    updateError.value = null;

    try {
      const response = await api.put("/customer/password", {
        current_password: data.currentPassword,
        password: data.newPassword,
        password_confirmation: data.confirmPassword,
      });

      return {
        success: true,
        message: response.data.message || "Password changed successfully",
      };
    } catch (err) {
      updateError.value =
        err.response?.data?.message || "Failed to change password";
      return {
        success: false,
        message: updateError.value,
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Update currency preference
   * @param {string} currency - Currency code (AUD, USD, ZAR, etc.)
   */
  async function updateCurrencyPreference(currency) {
    return updateProfile({ currency_preference: currency });
  }

  /**
   * Clear profile state
   */
  function clearProfile() {
    profile.value = null;
    error.value = null;
    updateError.value = null;
  }

  /**
   * Clear errors
   */
  function clearErrors() {
    error.value = null;
    updateError.value = null;
  }

  return {
    // State
    profile,
    isLoading,
    error,
    isUpdating,
    updateError,

    // Getters
    userName,
    userEmail,
    userPhone,
    currencyPreference,
    hasProfile,

    // Actions
    fetchProfile,
    updateProfile,
    changePassword,
    updateCurrencyPreference,
    clearProfile,
    clearErrors,
  };
});
