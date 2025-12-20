/**
 * Staff Waste Log Store
 *
 * Manages waste logging state for staff dashboard including
 * logging waste items, viewing waste history, and waste reports.
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { staffDashboard } from "@/services/dashboard";

export const useStaffWasteStore = defineStore("staffWaste", () => {
  // State
  const wasteLogs = ref([]);
  const isLoading = ref(false);
  const isSubmitting = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    total: 0,
    perPage: 15,
  });
  const filters = ref({
    search: "",
    reason: "", // 'expired', 'damaged', 'quality', 'other'
    dateFrom: "",
    dateTo: "",
    productId: null,
  });

  // Waste reason options
  const wasteReasons = [
    { value: "expired", label: "Expired" },
    { value: "damaged", label: "Damaged" },
    { value: "quality", label: "Quality Issue" },
    { value: "spoiled", label: "Spoiled" },
    { value: "contaminated", label: "Contaminated" },
    { value: "other", label: "Other" },
  ];

  // Computed
  const hasWasteLogs = computed(() => wasteLogs.value.length > 0);

  const todaysWaste = computed(() => {
    const today = new Date().toISOString().split("T")[0];
    return wasteLogs.value.filter((log) => log.created_at?.startsWith(today));
  });

  const wasteByReason = computed(() => {
    const byReason = {};
    wasteLogs.value.forEach((log) => {
      const reason = log.reason || "other";
      if (!byReason[reason]) {
        byReason[reason] = { count: 0, quantity: 0 };
      }
      byReason[reason].count++;
      byReason[reason].quantity += log.quantity || 0;
    });
    return byReason;
  });

  const totalWasteValue = computed(() =>
    wasteLogs.value.reduce((sum, log) => sum + (log.value || 0), 0)
  );

  const wasteStats = computed(() => ({
    total: wasteLogs.value.length,
    todayCount: todaysWaste.value.length,
    totalValue: totalWasteValue.value,
    byReason: wasteByReason.value,
  }));

  // Actions
  async function fetchWasteLogs(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const queryParams = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...filters.value,
        ...params,
      };

      const response = await staffDashboard.getWasteLogs(queryParams);

      if (response.success) {
        wasteLogs.value = response.data || response.waste_logs || [];
        if (response.meta) {
          pagination.value = {
            currentPage: response.meta.current_page,
            totalPages: response.meta.last_page,
            total: response.meta.total,
            perPage: response.meta.per_page,
          };
        }
      } else {
        throw new Error(response.message || "Failed to fetch waste logs");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load waste logs";
      console.error("Error fetching waste logs:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function logWaste(wasteData) {
    isSubmitting.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.logWaste(wasteData);

      if (response.success) {
        // Add to local waste logs
        const newLog = response.data || response.waste_log;
        if (newLog) {
          wasteLogs.value.unshift(newLog);
        }
      } else {
        throw new Error(response.message || "Failed to log waste");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to log waste";
      console.error("Error logging waste:", err);
      throw err;
    } finally {
      isSubmitting.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    wasteLogs.value = [];
    isLoading.value = false;
    isSubmitting.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      total: 0,
      perPage: 15,
    };
    filters.value = {
      search: "",
      reason: "",
      dateFrom: "",
      dateTo: "",
      productId: null,
    };
  }

  return {
    // State
    wasteLogs,
    isLoading,
    isSubmitting,
    error,
    pagination,
    filters,
    wasteReasons,

    // Computed
    hasWasteLogs,
    todaysWaste,
    wasteByReason,
    totalWasteValue,
    wasteStats,

    // Actions
    fetchWasteLogs,
    logWaste,
    setFilters,
    setPage,
    clearError,
    $reset,
  };
});
