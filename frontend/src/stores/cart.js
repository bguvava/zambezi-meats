/**
 * Cart Store
 *
 * Manages shopping cart state with localStorage persistence and API sync.
 *
 * @requirement PROJ-INIT-010 Configure Pinia store structure
 * @requirement CART-020 Cart Pinia store with localStorage + API sync
 * @requirement CART-010 Store cart in localStorage for guests
 * @requirement CART-011 Sync cart to database for logged-in users
 */
import { defineStore } from "pinia";
import { ref, computed, watch } from "vue";
import { useCurrencyStore } from "./currency";
import { useAuthStore } from "./auth";
import api from "@/services/api";

const STORAGE_KEY = "zambezi_cart";

export const useCartStore = defineStore("cart", () => {
  // State
  const items = ref([]);
  const isLoading = ref(false);
  const isSyncing = ref(false);
  const isOpen = ref(false);
  const error = ref(null);
  const lastSyncedAt = ref(null);

  // Minimum order amount in AUD
  const MINIMUM_ORDER = 100;

  // Get stores
  const currencyStore = useCurrencyStore();
  const authStore = useAuthStore();

  // Getters
  const itemCount = computed(() => items.value.length);

  const totalQuantity = computed(() =>
    items.value.reduce((sum, item) => sum + item.quantity, 0)
  );

  const subtotal = computed(() =>
    items.value.reduce((sum, item) => sum + item.unit_price * item.quantity, 0)
  );

  const subtotalFormatted = computed(() =>
    currencyStore.format(subtotal.value)
  );

  const isEmpty = computed(() => items.value.length === 0);

  const meetsMinimumOrder = computed(() => subtotal.value >= MINIMUM_ORDER);

  const amountToMinimum = computed(() =>
    Math.max(0, MINIMUM_ORDER - subtotal.value)
  );

  const amountToMinimumFormatted = computed(() =>
    currencyStore.format(amountToMinimum.value)
  );

  // Actions

  /**
   * Initialize cart - load from storage and sync if authenticated
   * @requirement CART-010 Load cart from localStorage
   * @requirement CART-011 Sync cart on login
   */
  async function initialize() {
    loadFromStorage();

    if (authStore.isAuthenticated) {
      await syncWithServer();
    }
  }

  /**
   * Add item to cart
   * @requirement CART-002 Click add-to-cart on product card
   * @param {Object} product - Product to add
   * @param {number} quantity - Quantity in kg
   */
  async function addItem(product, quantity = 1) {
    error.value = null;

    // Validate stock
    if (product.stock_quantity < quantity) {
      error.value = `Only ${product.stock_quantity}kg available`;
      return false;
    }

    const existingItem = items.value.find(
      (item) => item.product_id === product.id
    );

    if (existingItem) {
      const newQuantity = existingItem.quantity + quantity;
      if (product.stock_quantity < newQuantity) {
        error.value = `Cannot add more. Only ${product.stock_quantity}kg available`;
        return false;
      }
      existingItem.quantity = newQuantity;
    } else {
      items.value.push({
        id: Date.now(), // Temporary ID for local items
        product_id: product.id,
        product: {
          id: product.id,
          name: product.name,
          slug: product.slug,
          primary_image: product.primary_image || product.images?.[0],
          stock_quantity: product.stock_quantity,
          unit: product.unit || "kg",
        },
        quantity,
        unit_price: product.price,
      });
    }

    saveToStorage();

    // Sync with server if authenticated
    if (authStore.isAuthenticated) {
      try {
        await api.post("/cart/items", {
          product_id: product.id,
          quantity,
        });
        await fetchCart(); // Refresh cart from server
      } catch (err) {
        console.error("Failed to sync add to cart:", err);
      }
    }

    // Open cart panel
    isOpen.value = true;

    return true;
  }

  /**
   * Update item quantity
   * @requirement CART-003 Implement quantity adjustment
   * @param {number} itemId - Item ID
   * @param {number} quantity - New quantity
   */
  async function updateQuantity(itemId, quantity) {
    error.value = null;

    const item = items.value.find((item) => item.id === itemId);
    if (!item) return;

    const product = item.product;

    // Validate stock
    if (product && product.stock_quantity < quantity) {
      error.value = `Only ${product.stock_quantity}kg available`;
      quantity = product.stock_quantity;
    }

    if (quantity <= 0) {
      await removeItem(itemId);
      return;
    }

    item.quantity = quantity;
    saveToStorage();

    // Sync with server if authenticated
    if (authStore.isAuthenticated) {
      try {
        await api.put(`/cart/items/${itemId}`, { quantity });
      } catch (err) {
        console.error("Failed to sync quantity update:", err);
      }
    }
  }

  /**
   * Remove item from cart
   * @requirement CART-004 Implement item removal
   * @param {number} itemId - Item ID to remove
   */
  async function removeItem(itemId) {
    items.value = items.value.filter((item) => item.id !== itemId);
    saveToStorage();

    // Sync with server if authenticated
    if (authStore.isAuthenticated) {
      try {
        await api.delete(`/cart/items/${itemId}`);
      } catch (err) {
        console.error("Failed to sync item removal:", err);
      }
    }
  }

  /**
   * Clear all items from cart
   * @requirement CART-005 Clear cart
   */
  async function clearCart() {
    items.value = [];
    saveToStorage();

    // Sync with server if authenticated
    if (authStore.isAuthenticated) {
      try {
        await api.delete("/cart");
      } catch (err) {
        console.error("Failed to sync cart clear:", err);
      }
    }
  }

  /**
   * Fetch cart from server
   * @requirement CART-019 GET /api/v1/cart
   */
  async function fetchCart() {
    if (!authStore.isAuthenticated) return;

    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/cart");
      const cartData = response.data.data || response.data;

      items.value = cartData.items || [];
      lastSyncedAt.value = new Date();
      saveToStorage();
    } catch (err) {
      error.value = err.message || "Failed to fetch cart";
      console.error("Failed to fetch cart:", err);
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Sync localStorage cart with server on login
   * @requirement CART-011 Sync cart to database for logged-in users
   */
  async function syncWithServer() {
    if (!authStore.isAuthenticated) return;
    if (items.value.length === 0) {
      await fetchCart();
      return;
    }

    isSyncing.value = true;
    error.value = null;

    try {
      const localItems = items.value.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
      }));

      const response = await api.post("/cart/sync", {
        items: localItems,
      });

      const cartData = response.data.data || response.data;
      items.value = cartData.items || [];
      lastSyncedAt.value = new Date();
      saveToStorage();
    } catch (err) {
      error.value = err.message || "Failed to sync cart";
      console.error("Failed to sync cart:", err);
      // Fall back to fetching server cart
      await fetchCart();
    } finally {
      isSyncing.value = false;
    }
  }

  /**
   * Validate cart before checkout
   * @requirement CART-013 Validate stock on checkout
   */
  async function validateCart() {
    if (!authStore.isAuthenticated) {
      return { valid: true, issues: [] };
    }

    try {
      const response = await api.post("/cart/validate");
      return response.data;
    } catch (err) {
      console.error("Cart validation failed:", err);
      return { valid: false, issues: [{ message: "Validation failed" }] };
    }
  }

  /**
   * Save for later (move to wishlist)
   * @requirement CART-022 Implement "Save for Later"
   * @param {number} itemId - Item ID
   */
  async function saveForLater(itemId) {
    if (!authStore.isAuthenticated) {
      error.value = "Please log in to save items for later";
      return false;
    }

    try {
      await api.post(`/cart/items/${itemId}/save-for-later`);
      items.value = items.value.filter((item) => item.id !== itemId);
      saveToStorage();
      return true;
    } catch (err) {
      error.value = err.message || "Failed to save item for later";
      return false;
    }
  }

  /**
   * Save cart to local storage
   * @requirement CART-010 Store cart in localStorage
   */
  function saveToStorage() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value));
    } catch (err) {
      console.error("Failed to save cart to storage:", err);
    }
  }

  /**
   * Load cart from local storage
   * @requirement CART-010 Store cart in localStorage
   */
  function loadFromStorage() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      try {
        items.value = JSON.parse(stored);
      } catch (err) {
        console.error("Failed to parse cart from storage:", err);
        items.value = [];
      }
    }
  }

  /**
   * Get item by product ID
   * @param {number} productId - Product ID
   */
  function getItem(productId) {
    return items.value.find((item) => item.product_id === productId);
  }

  /**
   * Check if product is in cart
   * @param {number} productId - Product ID
   */
  function isInCart(productId) {
    return items.value.some((item) => item.product_id === productId);
  }

  /**
   * Get quantity of product in cart
   * @param {number} productId - Product ID
   */
  function getQuantity(productId) {
    const item = getItem(productId);
    return item ? item.quantity : 0;
  }

  /**
   * Toggle cart panel visibility
   * @requirement CART-001 Display cart icon with badge
   */
  function toggleCart() {
    isOpen.value = !isOpen.value;
  }

  /**
   * Open cart panel
   */
  function openCart() {
    isOpen.value = true;
  }

  /**
   * Close cart panel
   */
  function closeCart() {
    isOpen.value = false;
  }

  /**
   * Clear cart on logout (Issue 5 from issues003.md)
   */
  function clearOnLogout() {
    items.value = [];
    error.value = null;
    lastSyncedAt.value = null;
    localStorage.removeItem(STORAGE_KEY);
  }

  // Watch for auth changes to sync cart
  watch(
    () => authStore.isAuthenticated,
    async (isAuth, wasAuth) => {
      if (isAuth && !wasAuth) {
        // User just logged in, sync cart
        await syncWithServer();
      } else if (!isAuth && wasAuth) {
        // User logged out, clear cart completely (Issue 5 fix)
        clearOnLogout();
      }
    }
  );

  return {
    // State
    items,
    isLoading,
    isSyncing,
    isOpen,
    error,
    lastSyncedAt,
    MINIMUM_ORDER,

    // Getters
    itemCount,
    totalQuantity,
    subtotal,
    subtotalFormatted,
    isEmpty,
    meetsMinimumOrder,
    amountToMinimum,
    amountToMinimumFormatted,

    // Actions
    initialize,
    addItem,
    updateQuantity,
    removeItem,
    clearCart,
    fetchCart,
    syncWithServer,
    validateCart,
    saveForLater,
    saveToStorage,
    loadFromStorage,
    getItem,
    isInCart,
    getQuantity,
    toggleCart,
    openCart,
    closeCart,
    clearOnLogout,
  };
});
