/**
 * Address Store Tests
 *
 * Comprehensive tests for the address Pinia store.
 *
 * @requirement CUST-010 Address management page
 * @requirement CUST-011 Add/edit address modal
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAddressStore } from "@/stores/address";

// Mock API
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}));

import api from "@/services/api";

describe("Address Store", () => {
  let store;

  const mockAddresses = [
    {
      id: 1,
      label: "Home",
      street: "123 Main St",
      suburb: "Engadine",
      city: "Sydney",
      state: "NSW",
      postcode: "2233",
      is_default: true,
    },
    {
      id: 2,
      label: "Work",
      street: "456 Office Rd",
      suburb: "Sydney CBD",
      city: "Sydney",
      state: "NSW",
      postcode: "2000",
      is_default: false,
    },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAddressStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty addresses", () => {
      expect(store.addresses).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with isSaving false", () => {
      expect(store.isSaving).toBe(false);
    });

    it("starts with null saveError", () => {
      expect(store.saveError).toBeNull();
    });
  });

  describe("Getters", () => {
    it("returns undefined for defaultAddress when empty", () => {
      expect(store.defaultAddress).toBeUndefined();
    });

    it("returns default address when present", () => {
      store.addresses = mockAddresses;
      expect(store.defaultAddress).toEqual(mockAddresses[0]);
    });

    it("returns 0 for addressCount when empty", () => {
      expect(store.addressCount).toBe(0);
    });

    it("returns correct addressCount", () => {
      store.addresses = mockAddresses;
      expect(store.addressCount).toBe(2);
    });

    it("hasAddresses returns false when empty", () => {
      expect(store.hasAddresses).toBe(false);
    });

    it("hasAddresses returns true when addresses exist", () => {
      store.addresses = mockAddresses;
      expect(store.hasAddresses).toBe(true);
    });

    it("sortedAddresses puts default first", () => {
      store.addresses = [...mockAddresses].reverse();
      const sorted = store.sortedAddresses;
      expect(sorted[0].is_default).toBe(true);
    });
  });

  describe("fetchAddresses", () => {
    it("sets isLoading to true during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));
      store.fetchAddresses();
      expect(store.isLoading).toBe(true);
    });

    it("fetches addresses successfully", async () => {
      api.get.mockResolvedValue({
        data: { success: true, addresses: mockAddresses },
      });

      const result = await store.fetchAddresses();

      expect(api.get).toHaveBeenCalledWith("/customer/addresses");
      expect(result.success).toBe(true);
      expect(store.addresses).toEqual(mockAddresses);
      expect(store.isLoading).toBe(false);
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Failed to fetch addresses" } },
      });

      const result = await store.fetchAddresses();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to fetch addresses");
    });
  });

  describe("addAddress", () => {
    it("sets isSaving to true during add", async () => {
      api.post.mockImplementation(() => new Promise(() => {}));
      store.addAddress({ label: "New" });
      expect(store.isSaving).toBe(true);
    });

    it("adds address successfully", async () => {
      const newAddress = {
        id: 3,
        label: "Vacation",
        street: "789 Beach Blvd",
        is_default: false,
      };

      api.post.mockResolvedValue({
        data: { success: true, address: newAddress },
      });

      const result = await store.addAddress({
        label: "Vacation",
        street: "789 Beach Blvd",
      });

      expect(api.post).toHaveBeenCalledWith("/customer/addresses", {
        label: "Vacation",
        street: "789 Beach Blvd",
      });
      expect(result.success).toBe(true);
      expect(store.addresses).toContainEqual(newAddress);
    });

    it("updates other addresses when new is default", async () => {
      store.addresses = [{ id: 1, is_default: true }];

      const newAddress = { id: 2, label: "New Default", is_default: true };
      api.post.mockResolvedValue({
        data: { success: true, address: newAddress },
      });

      await store.addAddress({ label: "New Default", is_default: true });

      expect(store.addresses[0].is_default).toBe(false);
      expect(store.addresses[1].is_default).toBe(true);
    });

    it("handles add error with validation", async () => {
      api.post.mockRejectedValue({
        response: {
          data: {
            message: "Validation error",
            errors: { postcode: ["Invalid postcode format"] },
          },
        },
      });

      const result = await store.addAddress({ postcode: "123" });

      expect(result.success).toBe(false);
      expect(result.errors.postcode).toEqual(["Invalid postcode format"]);
    });
  });

  describe("updateAddress", () => {
    beforeEach(() => {
      store.addresses = [...mockAddresses];
    });

    it("updates address successfully", async () => {
      const updated = { ...mockAddresses[0], label: "Home Updated" };
      api.put.mockResolvedValue({
        data: { success: true, address: updated },
      });

      const result = await store.updateAddress(1, { label: "Home Updated" });

      expect(api.put).toHaveBeenCalledWith("/customer/addresses/1", {
        label: "Home Updated",
      });
      expect(result.success).toBe(true);
      expect(store.addresses[0].label).toBe("Home Updated");
    });

    it("updates default status of other addresses", async () => {
      const updated = { ...mockAddresses[1], is_default: true };
      api.put.mockResolvedValue({
        data: { success: true, address: updated },
      });

      await store.updateAddress(2, { is_default: true });

      expect(store.addresses[0].is_default).toBe(false);
      expect(store.addresses[1].is_default).toBe(true);
    });

    it("handles update error", async () => {
      api.put.mockRejectedValue({
        response: { data: { message: "Address not found" } },
      });

      const result = await store.updateAddress(999, { label: "Test" });

      expect(result.success).toBe(false);
      expect(result.message).toBe("Address not found");
    });
  });

  describe("deleteAddress", () => {
    beforeEach(() => {
      store.addresses = [...mockAddresses];
    });

    it("deletes address successfully", async () => {
      api.delete.mockResolvedValue({
        data: { success: true, message: "Address deleted" },
      });

      const result = await store.deleteAddress(1);

      expect(api.delete).toHaveBeenCalledWith("/customer/addresses/1");
      expect(result.success).toBe(true);
      expect(store.addresses).toHaveLength(1);
      expect(store.addresses[0].id).toBe(2);
    });

    it("handles delete error for address with orders", async () => {
      api.delete.mockRejectedValue({
        response: { data: { message: "Cannot delete address used in orders" } },
      });

      const result = await store.deleteAddress(1);

      expect(result.success).toBe(false);
      expect(result.message).toBe("Cannot delete address used in orders");
      expect(store.addresses).toHaveLength(2); // Not deleted
    });
  });

  describe("setDefaultAddress", () => {
    it("calls updateAddress with is_default true", async () => {
      api.put.mockResolvedValue({
        data: { success: true, address: { id: 2, is_default: true } },
      });

      await store.setDefaultAddress(2);

      expect(api.put).toHaveBeenCalledWith("/customer/addresses/2", {
        is_default: true,
      });
    });
  });

  describe("getAddressById", () => {
    it("returns address by ID", () => {
      store.addresses = mockAddresses;
      const address = store.getAddressById(2);
      expect(address).toEqual(mockAddresses[1]);
    });

    it("returns undefined for non-existent ID", () => {
      store.addresses = mockAddresses;
      const address = store.getAddressById(999);
      expect(address).toBeUndefined();
    });
  });

  describe("clearAddresses", () => {
    it("clears all address state", () => {
      store.addresses = mockAddresses;
      store.error = "Error";
      store.saveError = "Save error";

      store.clearAddresses();

      expect(store.addresses).toEqual([]);
      expect(store.error).toBeNull();
      expect(store.saveError).toBeNull();
    });
  });

  describe("clearErrors", () => {
    it("clears all errors", () => {
      store.error = "Error 1";
      store.saveError = "Error 2";

      store.clearErrors();

      expect(store.error).toBeNull();
      expect(store.saveError).toBeNull();
    });
  });
});
