/**
 * Staff Activity Store
 *
 * Manages activity log and performance stats for staff dashboard
 * including action history, productivity metrics, and performance tracking.
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { staffDashboard } from "@/services/dashboard";

export const useStaffActivityStore = defineStore("staffActivity", () => {
  // State
  const activities = ref([]);
  const performanceStats = ref(null);
  const isLoading = ref(false);
  const isLoadingStats = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    total: 0,
    perPage: 20,
  });
  const filters = ref({
    type: "", // 'order', 'delivery', 'stock', 'waste'
    dateFrom: "",
    dateTo: "",
    search: "",
  });

  // Activity type options
  const activityTypes = [
    {
      value: "order_status_updated",
      label: "Order Status Updated",
      icon: "ClipboardList",
    },
    { value: "order_note_added", label: "Order Note Added", icon: "FileText" },
    { value: "delivery_started", label: "Delivery Started", icon: "Truck" },
    {
      value: "delivery_completed",
      label: "Delivery Completed",
      icon: "CheckCircle",
    },
    { value: "pod_uploaded", label: "POD Uploaded", icon: "Camera" },
    { value: "pickup_completed", label: "Pickup Completed", icon: "Package" },
    { value: "stock_updated", label: "Stock Updated", icon: "Boxes" },
    { value: "waste_logged", label: "Waste Logged", icon: "Trash2" },
  ];

  // Computed
  const hasActivities = computed(() => activities.value.length > 0);

  const todaysActivities = computed(() => {
    const today = new Date().toISOString().split("T")[0];
    return activities.value.filter((activity) =>
      activity.created_at?.startsWith(today)
    );
  });

  const activitiesByType = computed(() => {
    const byType = {};
    activities.value.forEach((activity) => {
      const type = activity.type || "other";
      if (!byType[type]) {
        byType[type] = [];
      }
      byType[type].push(activity);
    });
    return byType;
  });

  const recentActivities = computed(() => activities.value.slice(0, 10));

  const activityStats = computed(() => ({
    total: activities.value.length,
    today: todaysActivities.value.length,
    byType: Object.keys(activitiesByType.value).reduce((acc, type) => {
      acc[type] = activitiesByType.value[type].length;
      return acc;
    }, {}),
  }));

  // Actions
  async function fetchActivityLog(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const queryParams = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...filters.value,
        ...params,
      };

      const response = await staffDashboard.getActivityLog(queryParams);

      if (response.success) {
        activities.value = response.data || response.activities || [];
        if (response.meta) {
          pagination.value = {
            currentPage: response.meta.current_page,
            totalPages: response.meta.last_page,
            total: response.meta.total,
            perPage: response.meta.per_page,
          };
        }
      } else {
        throw new Error(response.message || "Failed to fetch activity log");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load activity log";
      console.error("Error fetching activity log:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchPerformanceStats(params = {}) {
    isLoadingStats.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.getPerformanceStats(params);

      if (response.success) {
        performanceStats.value = response.data || response.stats || null;
      } else {
        throw new Error(
          response.message || "Failed to fetch performance stats"
        );
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load performance stats";
      console.error("Error fetching performance stats:", err);
      throw err;
    } finally {
      isLoadingStats.value = false;
    }
  }

  async function fetchAll(params = {}) {
    await Promise.all([
      fetchActivityLog(params),
      fetchPerformanceStats(params),
    ]);
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
    activities.value = [];
    performanceStats.value = null;
    isLoading.value = false;
    isLoadingStats.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      total: 0,
      perPage: 20,
    };
    filters.value = {
      type: "",
      dateFrom: "",
      dateTo: "",
      search: "",
    };
  }

  return {
    // State
    activities,
    performanceStats,
    isLoading,
    isLoadingStats,
    error,
    pagination,
    filters,
    activityTypes,

    // Computed
    hasActivities,
    todaysActivities,
    activitiesByType,
    recentActivities,
    activityStats,

    // Actions
    fetchActivityLog,
    fetchPerformanceStats,
    fetchAll,
    setFilters,
    setPage,
    clearError,
    $reset,
  };
});
