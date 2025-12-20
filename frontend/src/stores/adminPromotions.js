/**
 * Admin Promotions Store
 *
 * Manages admin promotion management state and actions.
 *
 * @requirement ADMIN-022 Create promotions management
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminPromotionsStore = defineStore("adminPromotions", () => {
  // State
  const promotions = ref([]);
  const currentPromotion = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    status: "all",
  });

  // Promotion types
  const promotionTypes = [
    { value: "percentage", label: "Percentage Off" },
    { value: "fixed", label: "Fixed Amount" },
  ];

  // Getters
  const hasPromotions = computed(() => promotions.value.length > 0);

  const activePromotionsCount = computed(
    () =>
      promotions.value.filter(
        (p) =>
          p.is_active &&
          new Date(p.start_date) <= new Date() &&
          new Date(p.end_date) >= new Date()
      ).length
  );

  const expiredPromotionsCount = computed(
    () =>
      promotions.value.filter((p) => new Date(p.end_date) < new Date()).length
  );

  // Actions
  async function fetchPromotions(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.status !== "all") {
        params.status = filters.value.status;
      }

      const response = await adminDashboard.getPromotions(params);

      if (response.success) {
        promotions.value = response.promotions || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 15,
          total: response.pagination?.total || 0,
        };
      }
    } catch (err) {
      console.error("Failed to fetch promotions:", err);
      error.value = err.message || "Failed to load promotions";
    } finally {
      isLoading.value = false;
    }
  }

  async function createPromotion(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.createPromotion(data);

      if (response.success && response.promotion) {
        promotions.value.unshift(response.promotion);
        pagination.value.total += 1;
      }

      return response;
    } catch (err) {
      console.error("Failed to create promotion:", err);
      error.value = err.message || "Failed to create promotion";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updatePromotion(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updatePromotion(id, data);

      if (response.success && response.promotion) {
        const index = promotions.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          promotions.value[index] = response.promotion;
        }
        currentPromotion.value = response.promotion;
      }

      return response;
    } catch (err) {
      console.error("Failed to update promotion:", err);
      error.value = err.message || "Failed to update promotion";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deletePromotion(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.deletePromotion(id);

      if (response.success) {
        const index = promotions.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          promotions.value.splice(index, 1);
          pagination.value.total -= 1;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to delete promotion:", err);
      error.value = err.message || "Failed to delete promotion";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function togglePromotionStatus(id, isActive) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updatePromotion(id, {
        is_active: isActive,
      });

      if (response.success && response.promotion) {
        const index = promotions.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          promotions.value[index] = response.promotion;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to toggle promotion status:", err);
      error.value = err.message || "Failed to update promotion status";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      status: "all",
    };
  }

  function setCurrentPromotion(promotion) {
    currentPromotion.value = promotion;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    promotions.value = [];
    currentPromotion.value = null;
    isLoading.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    };
    resetFilters();
  }

  return {
    // State
    promotions,
    currentPromotion,
    isLoading,
    error,
    pagination,
    filters,
    promotionTypes,

    // Getters
    hasPromotions,
    activePromotionsCount,
    expiredPromotionsCount,

    // Actions
    fetchPromotions,
    createPromotion,
    updatePromotion,
    deletePromotion,
    togglePromotionStatus,
    setFilters,
    resetFilters,
    setCurrentPromotion,
    clearError,
    $reset,
  };
});
