/**
 * Profile Store Tests
 *
 * Comprehensive tests for the profile Pinia store.
 *
 * @requirement CUST-008 Profile management
 * @requirement CUST-009 Password change
 * @requirement CUST-019 Currency preference
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useProfileStore } from "@/stores/profile";

// Mock API
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    put: vi.fn(),
    post: vi.fn(),
  },
}));

import api from "@/services/api";

describe("Profile Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useProfileStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with null profile", () => {
      expect(store.profile).toBeNull();
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

    it("starts with null updateError", () => {
      expect(store.updateError).toBeNull();
    });
  });

  describe("Getters", () => {
    it("returns empty string for userName when no profile", () => {
      expect(store.userName).toBe("");
    });

    it("returns userName from profile", () => {
      store.profile = { name: "John Doe", email: "john@example.com" };
      expect(store.userName).toBe("John Doe");
    });

    it("returns empty string for userEmail when no profile", () => {
      expect(store.userEmail).toBe("");
    });

    it("returns userEmail from profile", () => {
      store.profile = { name: "John", email: "john@example.com" };
      expect(store.userEmail).toBe("john@example.com");
    });

    it("returns empty string for userPhone when no profile", () => {
      expect(store.userPhone).toBe("");
    });

    it("returns userPhone from profile", () => {
      store.profile = { name: "John", phone: "0412345678" };
      expect(store.userPhone).toBe("0412345678");
    });

    it("returns AUD as default currencyPreference", () => {
      expect(store.currencyPreference).toBe("AUD");
    });

    it("returns currencyPreference from profile", () => {
      store.profile = { name: "John", currency_preference: "USD" };
      expect(store.currencyPreference).toBe("USD");
    });

    it("hasProfile returns false when no profile", () => {
      expect(store.hasProfile).toBe(false);
    });

    it("hasProfile returns true when profile exists", () => {
      store.profile = { name: "John" };
      expect(store.hasProfile).toBe(true);
    });
  });

  describe("fetchProfile", () => {
    it("sets isLoading to true during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));
      store.fetchProfile();
      expect(store.isLoading).toBe(true);
    });

    it("fetches profile successfully", async () => {
      const mockProfile = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
        phone: "0412345678",
        currency_preference: "AUD",
      };

      api.get.mockResolvedValue({
        data: { success: true, profile: mockProfile },
      });

      const result = await store.fetchProfile();

      expect(api.get).toHaveBeenCalledWith("/customer/profile");
      expect(result.success).toBe(true);
      expect(store.profile).toEqual(mockProfile);
      expect(store.isLoading).toBe(false);
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Failed to fetch profile" } },
      });

      const result = await store.fetchProfile();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to fetch profile");
      expect(store.isLoading).toBe(false);
    });

    it("clears error before fetching", async () => {
      store.error = "Previous error";
      api.get.mockResolvedValue({ data: { success: true, profile: {} } });

      await store.fetchProfile();

      expect(store.error).toBeNull();
    });
  });

  describe("updateProfile", () => {
    it("sets isUpdating to true during update", async () => {
      api.put.mockImplementation(() => new Promise(() => {}));
      store.updateProfile({ name: "John" });
      expect(store.isUpdating).toBe(true);
    });

    it("updates profile successfully", async () => {
      const updatedProfile = {
        id: 1,
        name: "John Updated",
        email: "john@example.com",
      };

      api.put.mockResolvedValue({
        data: { success: true, profile: updatedProfile },
      });

      const result = await store.updateProfile({ name: "John Updated" });

      expect(api.put).toHaveBeenCalledWith("/customer/profile", {
        name: "John Updated",
      });
      expect(result.success).toBe(true);
      expect(store.profile).toEqual(updatedProfile);
      expect(store.isUpdating).toBe(false);
    });

    it("handles update error", async () => {
      api.put.mockRejectedValue({
        response: {
          data: {
            message: "Validation error",
            errors: { email: ["Email already taken"] },
          },
        },
      });

      const result = await store.updateProfile({ email: "taken@example.com" });

      expect(result.success).toBe(false);
      expect(result.message).toBe("Validation error");
      expect(result.errors.email).toEqual(["Email already taken"]);
    });
  });

  describe("changePassword", () => {
    it("changes password successfully", async () => {
      api.put.mockResolvedValue({
        data: { success: true, message: "Password changed successfully" },
      });

      const result = await store.changePassword({
        currentPassword: "oldpass123",
        newPassword: "newpass123",
        confirmPassword: "newpass123",
      });

      expect(api.put).toHaveBeenCalledWith("/customer/password", {
        current_password: "oldpass123",
        password: "newpass123",
        password_confirmation: "newpass123",
      });
      expect(result.success).toBe(true);
      expect(result.message).toBe("Password changed successfully");
    });

    it("handles password change error", async () => {
      api.put.mockRejectedValue({
        response: { data: { message: "Current password is incorrect" } },
      });

      const result = await store.changePassword({
        currentPassword: "wrongpass",
        newPassword: "newpass123",
        confirmPassword: "newpass123",
      });

      expect(result.success).toBe(false);
      expect(result.message).toBe("Current password is incorrect");
    });
  });

  describe("updateCurrencyPreference", () => {
    it("updates currency preference", async () => {
      api.put.mockResolvedValue({
        data: { success: true, profile: { currency_preference: "USD" } },
      });

      const result = await store.updateCurrencyPreference("USD");

      expect(api.put).toHaveBeenCalledWith("/customer/profile", {
        currency_preference: "USD",
      });
      expect(result.success).toBe(true);
    });
  });

  describe("clearProfile", () => {
    it("clears profile state", () => {
      store.profile = { name: "John" };
      store.error = "Some error";
      store.updateError = "Update error";

      store.clearProfile();

      expect(store.profile).toBeNull();
      expect(store.error).toBeNull();
      expect(store.updateError).toBeNull();
    });
  });

  describe("clearErrors", () => {
    it("clears all errors", () => {
      store.error = "Error 1";
      store.updateError = "Error 2";

      store.clearErrors();

      expect(store.error).toBeNull();
      expect(store.updateError).toBeNull();
    });
  });
});
