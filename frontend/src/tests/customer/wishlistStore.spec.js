/**
 * Wishlist Store Tests
 *
 * Comprehensive tests for the wishlist Pinia store.
 *
 * @requirement CUST-012 Wishlist page
 * @requirement CUST-013 Add to cart from wishlist
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useWishlistStore } from "@/stores/wishlist";

// Create a stable mock cart store reference
const mockAddItem = vi.fn().mockResolvedValue({ success: true });
const mockCartStore = {
  addItem: mockAddItem,
};

// Mock API
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}));

// Mock cart store with stable reference
vi.mock("@/stores/cart", () => ({
  useCartStore: () => mockCartStore,
}));

import api from "@/services/api";

describe("Wishlist Store", () => {
  let store;

  const mockWishlistItems = [
    {
      id: 1,
      product_id: 101,
      product: {
        id: 101,
        name: "Beef Steak",
        price: 24.99,
        image: "/images/steak.jpg",
      },
    },
    {
      id: 2,
      product_id: 102,
      product: {
        id: 102,
        name: "Lamb Chops",
        price: 29.99,
        image: "/images/lamb.jpg",
      },
    },
    {
      id: 3,
      product_id: 103,
      product: {
        id: 103,
        name: "Chicken Breast",
        price: 14.99,
        image: "/images/chicken.jpg",
      },
    },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useWishlistStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty items", () => {
      expect(store.items).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with isUpdating false", () => {
      expect(store.isUpdating).toBe(false);
    });
  });

  describe("Getters", () => {
    beforeEach(() => {
      store.items = mockWishlistItems;
    });

    it("returns correct itemCount", () => {
      expect(store.itemCount).toBe(3);
    });

    it("hasItems returns true when items exist", () => {
      expect(store.hasItems).toBe(true);
    });

    it("hasItems returns false when empty", () => {
      store.items = [];
      expect(store.hasItems).toBe(false);
    });

    it("returns product IDs", () => {
      expect(store.productIds).toEqual([101, 102, 103]);
    });

    it("isInWishlist returns true for existing product", () => {
      expect(store.isInWishlist(101)).toBe(true);
    });

    it("isInWishlist returns false for non-existing product", () => {
      expect(store.isInWishlist(999)).toBe(false);
    });
  });

  describe("fetchWishlist", () => {
    it("sets isLoading to true during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));
      store.fetchWishlist();
      expect(store.isLoading).toBe(true);
    });

    it("fetches wishlist successfully", async () => {
      api.get.mockResolvedValue({
        data: { success: true, wishlist: mockWishlistItems },
      });

      const result = await store.fetchWishlist();

      expect(api.get).toHaveBeenCalledWith("/customer/wishlist");
      expect(result.success).toBe(true);
      expect(store.items).toEqual(mockWishlistItems);
      expect(store.isLoading).toBe(false);
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Failed to fetch wishlist" } },
      });

      const result = await store.fetchWishlist();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to fetch wishlist");
    });
  });

  describe("addToWishlist", () => {
    it("sets isUpdating to true during add", async () => {
      api.post.mockImplementation(() => new Promise(() => {}));
      store.addToWishlist(101);
      expect(store.isUpdating).toBe(true);
    });

    it("adds item to wishlist successfully", async () => {
      const newItem = { id: 4, product_id: 104 };
      api.post.mockResolvedValue({
        data: { success: true, item: newItem },
      });

      const result = await store.addToWishlist(104);

      expect(api.post).toHaveBeenCalledWith("/customer/wishlist", {
        product_id: 104,
      });
      expect(result.success).toBe(true);
      expect(store.items).toContainEqual(newItem);
    });

    it("handles add error", async () => {
      api.post.mockRejectedValue({
        response: { data: { message: "Product already in wishlist" } },
      });

      const result = await store.addToWishlist(101);

      expect(result.success).toBe(false);
      expect(result.message).toBe("Product already in wishlist");
    });
  });

  describe("removeFromWishlist", () => {
    beforeEach(() => {
      store.items = [...mockWishlistItems];
    });

    it("removes item from wishlist successfully", async () => {
      api.delete.mockResolvedValue({
        data: { success: true, message: "Removed from wishlist" },
      });

      const result = await store.removeFromWishlist(101);

      expect(api.delete).toHaveBeenCalledWith("/customer/wishlist/101");
      expect(result.success).toBe(true);
      expect(store.items.find((i) => i.product_id === 101)).toBeUndefined();
    });

    it("handles remove error", async () => {
      api.delete.mockRejectedValue({
        response: { data: { message: "Item not found" } },
      });

      const result = await store.removeFromWishlist(999);

      expect(result.success).toBe(false);
      expect(result.message).toBe("Item not found");
    });
  });

  describe("toggleWishlist", () => {
    it("adds when product not in wishlist", async () => {
      api.post.mockResolvedValue({
        data: { success: true, item: { id: 4, product_id: 104 } },
      });

      await store.toggleWishlist(104);

      expect(api.post).toHaveBeenCalledWith("/customer/wishlist", {
        product_id: 104,
      });
    });

    it("removes when product is in wishlist", async () => {
      store.items = mockWishlistItems;
      api.delete.mockResolvedValue({
        data: { success: true },
      });

      await store.toggleWishlist(101);

      expect(api.delete).toHaveBeenCalledWith("/customer/wishlist/101");
    });
  });

  describe("addToCart", () => {
    it("calls cart store addItem", async () => {
      mockAddItem.mockResolvedValue({ success: true });

      await store.addToCart(101, 2);

      expect(mockAddItem).toHaveBeenCalledWith(101, 2);
    });
  });

  describe("moveToCart", () => {
    beforeEach(() => {
      store.items = [...mockWishlistItems];
      mockAddItem.mockClear();
    });

    it("adds to cart and removes from wishlist", async () => {
      mockAddItem.mockResolvedValue({ success: true });
      api.delete.mockResolvedValue({ data: { success: true } });

      const result = await store.moveToCart(101, 1);

      expect(mockAddItem).toHaveBeenCalledWith(101, 1);
      expect(api.delete).toHaveBeenCalledWith("/customer/wishlist/101");
      expect(result.success).toBe(true);
    });

    it("does not remove from wishlist if cart add fails", async () => {
      mockAddItem.mockResolvedValue({ success: false });
      api.delete.mockClear();

      await store.moveToCart(101, 1);

      expect(api.delete).not.toHaveBeenCalled();
    });
  });

  describe("clearWishlist", () => {
    it("clears all wishlist state", () => {
      store.items = mockWishlistItems;
      store.error = "Error";

      store.clearWishlist();

      expect(store.items).toEqual([]);
      expect(store.error).toBeNull();
    });
  });

  describe("clearError", () => {
    it("clears error", () => {
      store.error = "Error";

      store.clearError();

      expect(store.error).toBeNull();
    });
  });
});
