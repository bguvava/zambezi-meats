/**
 * Address Store
 *
 * Manages customer delivery addresses state and operations.
 *
 * @requirement CUST-010 Address management page
 * @requirement CUST-011 Add/edit address modal
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useAddressStore = defineStore("address", () => {
  // State
  const addresses = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const isSaving = ref(false);
  const saveError = ref(null);

  // Getters
  const defaultAddress = computed(() =>
    addresses.value.find((addr) => addr.is_default)
  );

  const addressCount = computed(() => addresses.value.length);

  const hasAddresses = computed(() => addresses.value.length > 0);

  const sortedAddresses = computed(() => {
    return [...addresses.value].sort((a, b) => {
      if (a.is_default) return -1;
      if (b.is_default) return 1;
      return (a.label || "").localeCompare(b.label || "");
    });
  });

  // Actions

  /**
   * Fetch all addresses
   */
  async function fetchAddresses() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/customer/addresses");

      if (response.data.success) {
        addresses.value = response.data.addresses;
      } else {
        addresses.value = response.data;
      }

      return { success: true, addresses: addresses.value };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch addresses";
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Add new address
   * @param {Object} data - Address data
   */
  async function addAddress(data) {
    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await api.post("/customer/addresses", data);

      if (response.data.success) {
        const newAddress = response.data.address;

        // If new address is default, update existing addresses
        if (newAddress.is_default) {
          addresses.value = addresses.value.map((addr) => ({
            ...addr,
            is_default: false,
          }));
        }

        addresses.value.push(newAddress);
      }

      return {
        success: true,
        message: "Address added successfully",
        address: response.data.address,
      };
    } catch (err) {
      saveError.value = err.response?.data?.message || "Failed to add address";
      return {
        success: false,
        message: saveError.value,
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isSaving.value = false;
    }
  }

  /**
   * Update existing address
   * @param {number} id - Address ID
   * @param {Object} data - Address data
   */
  async function updateAddress(id, data) {
    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await api.put(`/customer/addresses/${id}`, data);

      if (response.data.success) {
        const updatedAddress = response.data.address;

        // If updated address is default, update other addresses
        if (updatedAddress.is_default) {
          addresses.value = addresses.value.map((addr) => ({
            ...addr,
            is_default: addr.id === id,
          }));
        }

        // Update address in the array
        const index = addresses.value.findIndex((addr) => addr.id === id);
        if (index !== -1) {
          addresses.value[index] = updatedAddress;
        }
      }

      return {
        success: true,
        message: "Address updated successfully",
        address: response.data.address,
      };
    } catch (err) {
      saveError.value =
        err.response?.data?.message || "Failed to update address";
      return {
        success: false,
        message: saveError.value,
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isSaving.value = false;
    }
  }

  /**
   * Delete address
   * @param {number} id - Address ID
   */
  async function deleteAddress(id) {
    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await api.delete(`/customer/addresses/${id}`);

      if (response.data.success) {
        addresses.value = addresses.value.filter((addr) => addr.id !== id);
      }

      return {
        success: true,
        message: response.data.message || "Address deleted successfully",
      };
    } catch (err) {
      saveError.value =
        err.response?.data?.message || "Failed to delete address";
      return { success: false, message: saveError.value };
    } finally {
      isSaving.value = false;
    }
  }

  /**
   * Set address as default
   * @param {number} id - Address ID
   */
  async function setDefaultAddress(id) {
    return updateAddress(id, { is_default: true });
  }

  /**
   * Get address by ID
   * @param {number} id - Address ID
   */
  function getAddressById(id) {
    return addresses.value.find((addr) => addr.id === id);
  }

  /**
   * Clear addresses state
   */
  function clearAddresses() {
    addresses.value = [];
    error.value = null;
    saveError.value = null;
  }

  /**
   * Clear errors
   */
  function clearErrors() {
    error.value = null;
    saveError.value = null;
  }

  return {
    // State
    addresses,
    isLoading,
    error,
    isSaving,
    saveError,

    // Getters
    defaultAddress,
    addressCount,
    hasAddresses,
    sortedAddresses,

    // Actions
    fetchAddresses,
    addAddress,
    updateAddress,
    deleteAddress,
    setDefaultAddress,
    getAddressById,
    clearAddresses,
    clearErrors,
  };
});
