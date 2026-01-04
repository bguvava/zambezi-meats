/**
 * Staff Deliveries Store
 *
 * Manages delivery state for staff dashboard including
 * today's deliveries, route tracking, and proof of delivery capture.
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { staffDashboard } from "@/services/dashboard";

export const useStaffDeliveriesStore = defineStore("staffDeliveries", () => {
  // State
  const deliveries = ref([]);
  const pickups = ref([]);
  const selectedDelivery = ref(null);
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const isUploadingPOD = ref(false);
  const error = ref(null);
  const filters = ref({
    status: "",
    timeSlot: "",
    search: "",
  });

  // Computed
  const hasDeliveries = computed(() => deliveries.value.length > 0);
  const hasPickups = computed(() => pickups.value.length > 0);

  const scheduledDeliveries = computed(() =>
    (Array.isArray(deliveries.value) ? deliveries.value : []).filter(
      (d) => d.status === "scheduled" || d.status === "ready_for_delivery"
    )
  );

  const outForDeliveryItems = computed(() =>
    (Array.isArray(deliveries.value) ? deliveries.value : []).filter(
      (d) => d.status === "out_for_delivery"
    )
  );

  const completedDeliveries = computed(() =>
    (Array.isArray(deliveries.value) ? deliveries.value : []).filter(
      (d) => d.status === "delivered"
    )
  );

  const pendingPickups = computed(() =>
    (Array.isArray(pickups.value) ? pickups.value : []).filter(
      (p) => p.status === "ready_for_pickup"
    )
  );

  const completedPickups = computed(() =>
    (Array.isArray(pickups.value) ? pickups.value : []).filter(
      (p) => p.status === "picked_up"
    )
  );

  const deliveriesByTimeSlot = computed(() => {
    const slots = {
      "08:00-10:00": [],
      "10:00-12:00": [],
      "14:00-16:00": [],
      "16:00-18:00": [],
    };

    if (Array.isArray(deliveries.value)) {
      deliveries.value.forEach((delivery) => {
        const slot = delivery.delivery_time_slot || delivery.time_slot;
        if (slots[slot]) {
          slots[slot].push(delivery);
        }
      });
    }

    return slots;
  });

  const deliveryCounts = computed(() => ({
    total: deliveries.value.length,
    scheduled: scheduledDeliveries.value.length,
    outForDelivery: outForDeliveryItems.value.length,
    completed: completedDeliveries.value.length,
  }));

  const pickupCounts = computed(() => ({
    total: pickups.value.length,
    pending: pendingPickups.value.length,
    completed: completedPickups.value.length,
  }));

  // Actions
  async function fetchTodaysDeliveries() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.getTodaysDeliveries();

      if (response.success) {
        // Ensure deliveries is always an array
        const rawData = response.data || response.deliveries || {};
        if (Array.isArray(rawData)) {
          deliveries.value = rawData;
        } else if (
          rawData.pending_dispatch ||
          rawData.in_transit ||
          rawData.completed
        ) {
          // Handle grouped response format
          deliveries.value = [
            ...(Array.isArray(rawData.pending_dispatch)
              ? rawData.pending_dispatch
              : []),
            ...(Array.isArray(rawData.in_transit) ? rawData.in_transit : []),
            ...(Array.isArray(rawData.completed) ? rawData.completed : []),
          ];
        } else {
          deliveries.value = [];
        }
      } else {
        throw new Error(response.message || "Failed to fetch deliveries");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load deliveries";
      deliveries.value = []; // Reset to empty array on error
      console.error("Error fetching deliveries:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchTodaysPickups() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.getTodaysPickups();

      if (response.success) {
        // Ensure pickups is always an array
        const rawData = response.data || response.pickups || {};
        if (Array.isArray(rawData)) {
          pickups.value = rawData;
        } else if (rawData.awaiting || rawData.picked_up) {
          // Handle grouped response format
          pickups.value = [
            ...(Array.isArray(rawData.awaiting) ? rawData.awaiting : []),
            ...(Array.isArray(rawData.picked_up) ? rawData.picked_up : []),
          ];
        } else {
          pickups.value = [];
        }
      } else {
        throw new Error(response.message || "Failed to fetch pickups");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load pickups";
      pickups.value = []; // Reset to empty array on error
      console.error("Error fetching pickups:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchAll() {
    await Promise.all([fetchTodaysDeliveries(), fetchTodaysPickups()]);
  }

  async function markOutForDelivery(orderId) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.markOutForDelivery(orderId);

      if (response.success) {
        // Update local delivery status
        const index = deliveries.value.findIndex(
          (d) => d.order_id === orderId || d.id === orderId
        );
        if (index !== -1) {
          deliveries.value[index] = {
            ...deliveries.value[index],
            status: "out_for_delivery",
            departed_at: new Date().toISOString(),
          };
        }
      } else {
        throw new Error(response.message || "Failed to mark out for delivery");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to update delivery status";
      console.error("Error marking out for delivery:", err);
      throw err;
    } finally {
      isUpdating.value = false;
    }
  }

  async function uploadProofOfDelivery(orderId, formData) {
    isUploadingPOD.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.uploadProofOfDelivery(
        orderId,
        formData
      );

      if (response.success) {
        // Update local delivery status to delivered
        const index = deliveries.value.findIndex(
          (d) => d.order_id === orderId || d.id === orderId
        );
        if (index !== -1) {
          deliveries.value[index] = {
            ...deliveries.value[index],
            status: "delivered",
            delivered_at: new Date().toISOString(),
            pod_uploaded: true,
          };
        }
      } else {
        throw new Error(
          response.message || "Failed to upload proof of delivery"
        );
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to upload proof of delivery";
      console.error("Error uploading POD:", err);
      throw err;
    } finally {
      isUploadingPOD.value = false;
    }
  }

  async function markAsPickedUp(orderId, receiverName) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.markAsPickedUp(
        orderId,
        receiverName
      );

      if (response.success) {
        // Update local pickup status
        const index = pickups.value.findIndex(
          (p) => p.order_id === orderId || p.id === orderId
        );
        if (index !== -1) {
          pickups.value[index] = {
            ...pickups.value[index],
            status: "picked_up",
            picked_up_at: new Date().toISOString(),
            receiver_name: receiverName,
          };
        }
      } else {
        throw new Error(response.message || "Failed to mark as picked up");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to update pickup status";
      console.error("Error marking as picked up:", err);
      throw err;
    } finally {
      isUpdating.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function selectDelivery(delivery) {
    selectedDelivery.value = delivery;
  }

  function clearSelectedDelivery() {
    selectedDelivery.value = null;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    deliveries.value = [];
    pickups.value = [];
    selectedDelivery.value = null;
    isLoading.value = false;
    isUpdating.value = false;
    isUploadingPOD.value = false;
    error.value = null;
    filters.value = {
      status: "",
      timeSlot: "",
      search: "",
    };
  }

  return {
    // State
    deliveries,
    pickups,
    selectedDelivery,
    isLoading,
    isUpdating,
    isUploadingPOD,
    error,
    filters,

    // Computed
    hasDeliveries,
    hasPickups,
    scheduledDeliveries,
    outForDeliveryItems,
    completedDeliveries,
    pendingPickups,
    completedPickups,
    deliveriesByTimeSlot,
    deliveryCounts,
    pickupCounts,

    // Actions
    fetchTodaysDeliveries,
    fetchTodaysPickups,
    fetchAll,
    markOutForDelivery,
    uploadProofOfDelivery,
    markAsPickedUp,
    setFilters,
    selectDelivery,
    clearSelectedDelivery,
    clearError,
    $reset,
  };
});
