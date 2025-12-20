/**
 * Wishlist Store
 *
 * Manages customer wishlist state and operations.
 *
 * @requirement CUST-012 Wishlist page
 * @requirement CUST-013 Add to cart from wishlist
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";
import { useCartStore } from "./cart";

export const useWishlistStore = defineStore("wishlist", () => {
  // State
  const items = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const isUpdating = ref(false);

  // Getters
  const itemCount = computed(() => items.value.length);

  const hasItems = computed(() => items.value.length > 0);

  const productIds = computed(() => items.value.map((item) => item.product_id));

  const isInWishlist = computed(() => (productId) => {
    return items.value.some((item) => item.product_id === productId);
  });

  // Actions

  /**
   * Fetch wishlist items
   */
  async function fetchWishlist() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/customer/wishlist");

      if (response.data.success) {
        items.value = response.data.wishlist || [];
      } else {
        items.value = response.data || [];
      }

      return { success: true, items: items.value };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch wishlist";
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Add product to wishlist
   * @param {number} productId - Product ID
   */
  async function addToWishlist(productId) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await api.post("/customer/wishlist", {
        product_id: productId,
      });

      if (response.data.success) {
        // Add to local state
        if (response.data.item) {
          items.value.push(response.data.item);
        } else {
          // Refetch to get the full item data
          await fetchWishlist();
        }
      }

      return {
        success: true,
        message: response.data.message || "Added to wishlist",
      };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to add to wishlist";
      return { success: false, message: error.value };
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Remove product from wishlist
   * @param {number} productId - Product ID
   */
  async function removeFromWishlist(productId) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await api.delete(`/customer/wishlist/${productId}`);

      if (response.data.success) {
        items.value = items.value.filter(
          (item) => item.product_id !== productId
        );
      }

      return {
        success: true,
        message: response.data.message || "Removed from wishlist",
      };
    } catch (err) {
      error.value =
        err.response?.data?.message || "Failed to remove from wishlist";
      return { success: false, message: error.value };
    } finally {
      isUpdating.value = false;
    }
  }

  /**
   * Toggle wishlist status for a product
   * @param {number} productId - Product ID
   */
  async function toggleWishlist(productId) {
    if (isInWishlist.value(productId)) {
      return removeFromWishlist(productId);
    } else {
      return addToWishlist(productId);
    }
  }

  /**
   * Add wishlist item to cart
   * @param {number} productId - Product ID
   * @param {number} quantity - Quantity (default: 1)
   */
  async function addToCart(productId, quantity = 1) {
    const cartStore = useCartStore();
    return cartStore.addItem(productId, quantity);
  }

  /**
   * Move item from wishlist to cart
   * @param {number} productId - Product ID
   * @param {number} quantity - Quantity (default: 1)
   */
  async function moveToCart(productId, quantity = 1) {
    const cartResult = await addToCart(productId, quantity);

    if (cartResult.success) {
      await removeFromWishlist(productId);
    }

    return cartResult;
  }

  /**
   * Clear wishlist
   */
  function clearWishlist() {
    items.value = [];
    error.value = null;
  }

  /**
   * Clear error
   */
  function clearError() {
    error.value = null;
  }

  return {
    // State
    items,
    isLoading,
    error,
    isUpdating,

    // Getters
    itemCount,
    hasItems,
    productIds,
    isInWishlist,

    // Actions
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    toggleWishlist,
    addToCart,
    moveToCart,
    clearWishlist,
    clearError,
  };
});
