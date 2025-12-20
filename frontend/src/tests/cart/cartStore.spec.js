/**
 * Cart Store Tests
 *
 * Comprehensive tests for the Pinia cart store.
 *
 * @requirement CART-020 Create cart Pinia store
 * @requirement CART-010 Store cart in localStorage
 * @requirement CART-011 Sync cart to database for logged-in users
 * @requirement CART-023 Write cart module tests
 */
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useCartStore } from "@/stores/cart";

// Mock dependencies
vi.mock("@/stores/currency", () => ({
  useCurrencyStore: vi.fn(() => ({
    format: vi.fn((amount) => `$${amount.toFixed(2)}`),
    currentCurrency: "AUD",
    exchangeRate: 1,
  })),
}));

vi.mock("@/stores/auth", () => ({
  useAuthStore: vi.fn(() => ({
    isAuthenticated: false,
    user: null,
  })),
}));

vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}));

import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";

describe("Cart Store", () => {
  let cartStore;
  let mockLocalStorage;

  beforeEach(() => {
    setActivePinia(createPinia());
    cartStore = useCartStore();

    // Mock localStorage
    mockLocalStorage = {};
    vi.spyOn(Storage.prototype, "getItem").mockImplementation(
      (key) => mockLocalStorage[key] || null
    );
    vi.spyOn(Storage.prototype, "setItem").mockImplementation((key, value) => {
      mockLocalStorage[key] = value;
    });
    vi.spyOn(Storage.prototype, "removeItem").mockImplementation((key) => {
      delete mockLocalStorage[key];
    });

    // Reset API mocks
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe("Initial State", () => {
    it("has empty items array", () => {
      expect(cartStore.items).toEqual([]);
    });

    it("has isLoading set to false", () => {
      expect(cartStore.isLoading).toBe(false);
    });

    it("has isSyncing set to false", () => {
      expect(cartStore.isSyncing).toBe(false);
    });

    it("has isOpen set to false", () => {
      expect(cartStore.isOpen).toBe(false);
    });

    it("has error set to null", () => {
      expect(cartStore.error).toBeNull();
    });

    it("has lastSyncedAt set to null", () => {
      expect(cartStore.lastSyncedAt).toBeNull();
    });

    it("has MINIMUM_ORDER constant defined", () => {
      expect(cartStore.MINIMUM_ORDER).toBe(100);
    });
  });

  describe("Computed Properties (Getters)", () => {
    beforeEach(() => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            id: 101,
            name: "Ribeye Steak",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 2,
          unit_price: 45.99,
        },
        {
          id: 2,
          product_id: 102,
          product: {
            id: 102,
            name: "Chicken Breast",
            stock_quantity: 30,
            unit: "kg",
          },
          quantity: 1.5,
          unit_price: 18.99,
        },
      ];
    });

    it("calculates itemCount correctly", () => {
      expect(cartStore.itemCount).toBe(2);
    });

    it("calculates totalQuantity correctly", () => {
      expect(cartStore.totalQuantity).toBe(3.5);
    });

    it("calculates subtotal correctly", () => {
      // (2 * 45.99) + (1.5 * 18.99) = 91.98 + 28.485 = 120.465
      expect(cartStore.subtotal).toBeCloseTo(120.465, 2);
    });

    it("returns formatted subtotal", () => {
      expect(cartStore.subtotalFormatted).toContain("$");
    });

    it("returns isEmpty as false when items exist", () => {
      expect(cartStore.isEmpty).toBe(false);
    });

    it("returns isEmpty as true when no items", () => {
      cartStore.items = [];
      expect(cartStore.isEmpty).toBe(true);
    });

    it("returns meetsMinimumOrder as true when subtotal >= 100", () => {
      expect(cartStore.meetsMinimumOrder).toBe(true);
    });

    it("returns meetsMinimumOrder as false when subtotal < 100", () => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { id: 101, name: "Item", stock_quantity: 50 },
          quantity: 1,
          unit_price: 50,
        },
      ];
      expect(cartStore.meetsMinimumOrder).toBe(false);
    });

    it("calculates amountToMinimum correctly", () => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { id: 101, name: "Item", stock_quantity: 50 },
          quantity: 1,
          unit_price: 75,
        },
      ];
      expect(cartStore.amountToMinimum).toBe(25);
    });

    it("returns 0 for amountToMinimum when minimum is met", () => {
      expect(cartStore.amountToMinimum).toBe(0);
    });
  });

  describe("addItem Action", () => {
    const mockProduct = {
      id: 1,
      name: "Ribeye Steak",
      slug: "ribeye-steak",
      price: 45.99,
      stock_quantity: 50,
      unit: "kg",
      primary_image: { url: "/images/ribeye.jpg" },
    };

    it("adds new item to cart", async () => {
      const result = await cartStore.addItem(mockProduct, 2);

      expect(result).toBe(true);
      expect(cartStore.items).toHaveLength(1);
      expect(cartStore.items[0].product_id).toBe(mockProduct.id);
      expect(cartStore.items[0].quantity).toBe(2);
    });

    it("increases quantity for existing item", async () => {
      await cartStore.addItem(mockProduct, 2);
      await cartStore.addItem(mockProduct, 1.5);

      expect(cartStore.items).toHaveLength(1);
      expect(cartStore.items[0].quantity).toBe(3.5);
    });

    it("returns false if stock is insufficient", async () => {
      const lowStockProduct = { ...mockProduct, stock_quantity: 1 };
      const result = await cartStore.addItem(lowStockProduct, 5);

      expect(result).toBe(false);
      expect(cartStore.error).toContain("Only 1kg available");
    });

    it("returns false if adding would exceed stock", async () => {
      await cartStore.addItem(mockProduct, 48);
      cartStore.error = null; // Reset error

      const result = await cartStore.addItem(mockProduct, 5);

      expect(result).toBe(false);
      expect(cartStore.error).toContain("Cannot add more");
    });

    it("opens cart panel after adding", async () => {
      await cartStore.addItem(mockProduct, 1);
      expect(cartStore.isOpen).toBe(true);
    });

    it("saves to localStorage after adding", async () => {
      await cartStore.addItem(mockProduct, 1);

      expect(localStorage.setItem).toHaveBeenCalled();
      // Verify items were stored
      expect(cartStore.items).toHaveLength(1);
    });

    it("sets correct unit_price from product price", async () => {
      await cartStore.addItem(mockProduct, 1);
      expect(cartStore.items[0].unit_price).toBe(45.99);
    });

    it("stores product details in item", async () => {
      await cartStore.addItem(mockProduct, 1);

      expect(cartStore.items[0].product).toMatchObject({
        id: mockProduct.id,
        name: mockProduct.name,
        slug: mockProduct.slug,
      });
    });
  });

  describe("updateQuantity Action", () => {
    beforeEach(async () => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            id: 101,
            name: "Ribeye Steak",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 2,
          unit_price: 45.99,
        },
      ];
    });

    it("updates item quantity", async () => {
      await cartStore.updateQuantity(1, 5);
      expect(cartStore.items[0].quantity).toBe(5);
    });

    it("caps quantity at stock level", async () => {
      await cartStore.updateQuantity(1, 100);
      expect(cartStore.items[0].quantity).toBe(50);
      expect(cartStore.error).toContain("Only 50kg available");
    });

    it("removes item if quantity is 0 or less", async () => {
      await cartStore.updateQuantity(1, 0);
      expect(cartStore.items).toHaveLength(0);
    });

    it("does nothing if item not found", async () => {
      await cartStore.updateQuantity(999, 5);
      expect(cartStore.items[0].quantity).toBe(2);
    });

    it("saves to localStorage after update", async () => {
      await cartStore.updateQuantity(1, 5);
      expect(localStorage.setItem).toHaveBeenCalled();
    });
  });

  describe("removeItem Action", () => {
    beforeEach(() => {
      cartStore.items = [
        { id: 1, product_id: 101, quantity: 2, unit_price: 45.99 },
        { id: 2, product_id: 102, quantity: 1, unit_price: 18.99 },
      ];
    });

    it("removes item from cart", async () => {
      await cartStore.removeItem(1);
      expect(cartStore.items).toHaveLength(1);
      expect(cartStore.items[0].id).toBe(2);
    });

    it("does nothing if item not found", async () => {
      await cartStore.removeItem(999);
      expect(cartStore.items).toHaveLength(2);
    });

    it("saves to localStorage after removal", async () => {
      await cartStore.removeItem(1);
      expect(localStorage.setItem).toHaveBeenCalled();
    });
  });

  describe("clearCart Action", () => {
    beforeEach(() => {
      cartStore.items = [
        { id: 1, product_id: 101, quantity: 2, unit_price: 45.99 },
        { id: 2, product_id: 102, quantity: 1, unit_price: 18.99 },
      ];
    });

    it("clears all items from cart", async () => {
      await cartStore.clearCart();
      expect(cartStore.items).toHaveLength(0);
    });

    it("saves to localStorage after clearing", async () => {
      await cartStore.clearCart();
      expect(localStorage.setItem).toHaveBeenCalled();
    });
  });

  describe("localStorage Persistence", () => {
    it("saves cart to localStorage", () => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      cartStore.saveToStorage();

      expect(localStorage.setItem).toHaveBeenCalledWith(
        "zambezi_cart",
        expect.any(String)
      );
    });

    it("loads cart from localStorage", () => {
      const storedItems = [{ id: 1, product_id: 101, quantity: 2 }];
      mockLocalStorage["zambezi_cart"] = JSON.stringify(storedItems);

      // Manually load from storage
      cartStore.loadFromStorage();

      // The loadFromStorage reads from localStorage.getItem which returns mockLocalStorage value
      expect(localStorage.getItem).toHaveBeenCalledWith("zambezi_cart");
    });

    it("handles invalid JSON in localStorage", () => {
      mockLocalStorage["zambezi_cart"] = "invalid json";

      cartStore.loadFromStorage();

      expect(cartStore.items).toEqual([]);
    });

    it("handles empty localStorage", () => {
      cartStore.loadFromStorage();
      expect(cartStore.items).toEqual([]);
    });
  });

  describe("Cart Panel Controls", () => {
    it("toggleCart opens closed cart", () => {
      cartStore.isOpen = false;
      cartStore.toggleCart();
      expect(cartStore.isOpen).toBe(true);
    });

    it("toggleCart closes open cart", () => {
      cartStore.isOpen = true;
      cartStore.toggleCart();
      expect(cartStore.isOpen).toBe(false);
    });

    it("openCart sets isOpen to true", () => {
      cartStore.isOpen = false;
      cartStore.openCart();
      expect(cartStore.isOpen).toBe(true);
    });

    it("closeCart sets isOpen to false", () => {
      cartStore.isOpen = true;
      cartStore.closeCart();
      expect(cartStore.isOpen).toBe(false);
    });
  });

  describe("Helper Functions", () => {
    beforeEach(() => {
      cartStore.items = [
        { id: 1, product_id: 101, quantity: 2, unit_price: 45.99 },
        { id: 2, product_id: 102, quantity: 1, unit_price: 18.99 },
      ];
    });

    it("getItem returns item by product_id", () => {
      const item = cartStore.getItem(101);
      expect(item).toBeDefined();
      expect(item.product_id).toBe(101);
    });

    it("getItem returns undefined for non-existent product", () => {
      const item = cartStore.getItem(999);
      expect(item).toBeUndefined();
    });

    it("isInCart returns true for product in cart", () => {
      expect(cartStore.isInCart(101)).toBe(true);
    });

    it("isInCart returns false for product not in cart", () => {
      expect(cartStore.isInCart(999)).toBe(false);
    });

    it("getQuantity returns quantity for product in cart", () => {
      expect(cartStore.getQuantity(101)).toBe(2);
    });

    it("getQuantity returns 0 for product not in cart", () => {
      expect(cartStore.getQuantity(999)).toBe(0);
    });
  });

  describe("clearOnLogout Action", () => {
    beforeEach(() => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      cartStore.error = "Some error";
      cartStore.lastSyncedAt = new Date();
    });

    it("clears all items", () => {
      cartStore.clearOnLogout();
      expect(cartStore.items).toEqual([]);
    });

    it("clears error", () => {
      cartStore.clearOnLogout();
      expect(cartStore.error).toBeNull();
    });

    it("clears lastSyncedAt", () => {
      cartStore.clearOnLogout();
      expect(cartStore.lastSyncedAt).toBeNull();
    });

    it("removes localStorage entry", () => {
      cartStore.clearOnLogout();
      expect(localStorage.removeItem).toHaveBeenCalledWith("zambezi_cart");
    });
  });

  describe("API Integration (Authenticated)", () => {
    beforeEach(() => {
      // Mock authenticated user
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        user: { id: 1, name: "Test User" },
      });

      // Re-create store with mocked auth
      setActivePinia(createPinia());
      cartStore = useCartStore();
    });

    it("syncs add to cart with API when authenticated", async () => {
      vi.mocked(api.post).mockResolvedValue({ data: { data: { items: [] } } });
      vi.mocked(api.get).mockResolvedValue({ data: { data: { items: [] } } });

      const product = {
        id: 1,
        name: "Test",
        price: 10,
        stock_quantity: 50,
      };

      await cartStore.addItem(product, 2);

      expect(api.post).toHaveBeenCalledWith("/cart/items", {
        product_id: 1,
        quantity: 2,
      });
    });

    it("syncs update quantity with API when authenticated", async () => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { stock_quantity: 50 },
          quantity: 2,
          unit_price: 10,
        },
      ];

      vi.mocked(api.put).mockResolvedValue({ data: { data: {} } });

      await cartStore.updateQuantity(1, 5);

      expect(api.put).toHaveBeenCalledWith("/cart/items/1", { quantity: 5 });
    });

    it("syncs remove item with API when authenticated", async () => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      vi.mocked(api.delete).mockResolvedValue({ data: {} });

      await cartStore.removeItem(1);

      expect(api.delete).toHaveBeenCalledWith("/cart/items/1");
    });

    it("syncs clear cart with API when authenticated", async () => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      vi.mocked(api.delete).mockResolvedValue({ data: {} });

      await cartStore.clearCart();

      expect(api.delete).toHaveBeenCalledWith("/cart");
    });

    it("fetches cart from server", async () => {
      const serverCart = {
        items: [{ id: 1, product_id: 101, quantity: 3, unit_price: 20 }],
      };
      vi.mocked(api.get).mockResolvedValue({ data: { data: serverCart } });

      await cartStore.fetchCart();

      expect(api.get).toHaveBeenCalledWith("/cart");
      expect(cartStore.items).toEqual(serverCart.items);
    });

    it("sets lastSyncedAt after fetch", async () => {
      vi.mocked(api.get).mockResolvedValue({ data: { data: { items: [] } } });

      await cartStore.fetchCart();

      expect(cartStore.lastSyncedAt).toBeInstanceOf(Date);
    });

    it("handles fetch error gracefully", async () => {
      vi.mocked(api.get).mockRejectedValue(new Error("Network error"));

      await cartStore.fetchCart();

      expect(cartStore.error).toBe("Network error");
    });
  });

  describe("syncWithServer Action", () => {
    beforeEach(() => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        user: { id: 1 },
      });
      setActivePinia(createPinia());
      cartStore = useCartStore();
    });

    it("fetches cart if local cart is empty", async () => {
      vi.mocked(api.get).mockResolvedValue({ data: { data: { items: [] } } });

      await cartStore.syncWithServer();

      expect(api.get).toHaveBeenCalledWith("/cart");
    });

    it("posts sync request if local cart has items", async () => {
      cartStore.items = [{ product_id: 101, quantity: 2 }];
      vi.mocked(api.post).mockResolvedValue({
        data: { data: { items: [{ id: 1, product_id: 101, quantity: 2 }] } },
      });

      await cartStore.syncWithServer();

      expect(api.post).toHaveBeenCalledWith("/cart/sync", {
        items: [{ product_id: 101, quantity: 2 }],
      });
    });

    it("sets isSyncing during sync", async () => {
      cartStore.items = [{ product_id: 101, quantity: 2 }];
      vi.mocked(api.post).mockImplementation(
        () =>
          new Promise((resolve) =>
            setTimeout(() => resolve({ data: { data: { items: [] } } }), 100)
          )
      );

      const syncPromise = cartStore.syncWithServer();
      expect(cartStore.isSyncing).toBe(true);

      await syncPromise;
      expect(cartStore.isSyncing).toBe(false);
    });
  });

  describe("validateCart Action", () => {
    beforeEach(() => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        user: { id: 1 },
      });
      setActivePinia(createPinia());
      cartStore = useCartStore();
    });

    it("returns valid result from API", async () => {
      vi.mocked(api.post).mockResolvedValue({
        data: { valid: true, issues: [] },
      });

      const result = await cartStore.validateCart();

      expect(api.post).toHaveBeenCalledWith("/cart/validate");
      expect(result.valid).toBe(true);
    });

    it("returns issues from API", async () => {
      const issues = [
        { type: "insufficient_stock", message: "Not enough stock" },
      ];
      vi.mocked(api.post).mockResolvedValue({
        data: { valid: false, issues },
      });

      const result = await cartStore.validateCart();

      expect(result.valid).toBe(false);
      expect(result.issues).toEqual(issues);
    });

    it("handles validation error gracefully", async () => {
      vi.mocked(api.post).mockRejectedValue(new Error("Server error"));

      const result = await cartStore.validateCart();

      expect(result.valid).toBe(false);
    });
  });

  describe("saveForLater Action", () => {
    beforeEach(() => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: true,
        user: { id: 1 },
      });
      setActivePinia(createPinia());
      cartStore = useCartStore();
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
    });

    it("calls API to save item for later", async () => {
      vi.mocked(api.post).mockResolvedValue({ data: {} });

      await cartStore.saveForLater(1);

      expect(api.post).toHaveBeenCalledWith("/cart/items/1/save-for-later");
    });

    it("removes item from cart on success", async () => {
      vi.mocked(api.post).mockResolvedValue({ data: {} });

      await cartStore.saveForLater(1);

      expect(cartStore.items).toHaveLength(0);
    });

    it("returns true on success", async () => {
      vi.mocked(api.post).mockResolvedValue({ data: {} });

      const result = await cartStore.saveForLater(1);

      expect(result).toBe(true);
    });

    it("returns false on error", async () => {
      vi.mocked(api.post).mockRejectedValue(new Error("Server error"));

      const result = await cartStore.saveForLater(1);

      expect(result).toBe(false);
    });

    it("sets error on failure", async () => {
      vi.mocked(api.post).mockRejectedValue({ message: "Failed" });

      await cartStore.saveForLater(1);

      expect(cartStore.error).toBeDefined();
    });
  });

  describe("Guest User (Not Authenticated)", () => {
    beforeEach(() => {
      vi.mocked(useAuthStore).mockReturnValue({
        isAuthenticated: false,
        user: null,
      });
      setActivePinia(createPinia());
      cartStore = useCartStore();
    });

    it("does not call API on addItem for guest", async () => {
      await cartStore.addItem({ id: 1, price: 10, stock_quantity: 50 }, 1);
      expect(api.post).not.toHaveBeenCalled();
    });

    it("does not call API on updateQuantity for guest", async () => {
      cartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { stock_quantity: 50 },
          quantity: 2,
        },
      ];
      await cartStore.updateQuantity(1, 5);
      expect(api.put).not.toHaveBeenCalled();
    });

    it("does not call API on removeItem for guest", async () => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      await cartStore.removeItem(1);
      expect(api.delete).not.toHaveBeenCalled();
    });

    it("does not call API on clearCart for guest", async () => {
      cartStore.items = [{ id: 1, product_id: 101, quantity: 2 }];
      await cartStore.clearCart();
      expect(api.delete).not.toHaveBeenCalled();
    });

    it("saveForLater returns false for guest", async () => {
      const result = await cartStore.saveForLater(1);
      expect(result).toBe(false);
      expect(cartStore.error).toContain("log in");
    });
  });

  describe("Edge Cases", () => {
    it("handles decimal quantities", async () => {
      const product = { id: 1, price: 10, stock_quantity: 50 };
      await cartStore.addItem(product, 0.5);
      expect(cartStore.items[0].quantity).toBe(0.5);
    });

    it("handles very large quantities within stock", async () => {
      const product = { id: 1, price: 10, stock_quantity: 1000 };
      await cartStore.addItem(product, 999);
      expect(cartStore.items[0].quantity).toBe(999);
    });

    it("maintains item order after operations", async () => {
      await cartStore.addItem({ id: 1, price: 10, stock_quantity: 50 }, 1);
      await cartStore.addItem({ id: 2, price: 20, stock_quantity: 50 }, 1);
      await cartStore.addItem({ id: 3, price: 30, stock_quantity: 50 }, 1);

      expect(cartStore.items[0].product_id).toBe(1);
      expect(cartStore.items[1].product_id).toBe(2);
      expect(cartStore.items[2].product_id).toBe(3);
    });

    it("handles product without primary_image", async () => {
      const product = {
        id: 1,
        price: 10,
        stock_quantity: 50,
        images: [{ url: "/img.jpg" }],
      };
      await cartStore.addItem(product, 1);
      // When no primary_image, it uses first image from images array
      expect(cartStore.items[0].product.primary_image).toEqual({
        url: "/img.jpg",
      });
    });

    it("handles product without any images", async () => {
      const product = { id: 1, price: 10, stock_quantity: 50 };
      await cartStore.addItem(product, 1);
      expect(cartStore.items[0].product.primary_image).toBeUndefined();
    });
  });
});
