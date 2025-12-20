/**
 * Auth Store Tests
 *
 * Tests for the Pinia auth store functionality.
 *
 * @requirement AUTH-010 to AUTH-014 Pinia auth store functionality
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAuthStore } from "@/stores/auth";
import api, { initializeCsrf } from "@/services/api";

// Mock the api module
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
  initializeCsrf: vi.fn(),
}));

// Mock dynamic imports for cart store and router
vi.mock("@/stores/cart", () => ({
  useCartStore: vi.fn(() => ({
    clearOnLogout: vi.fn(),
  })),
}));

describe("Auth Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useAuthStore();
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe("Initial State", () => {
    it("should have null user by default", () => {
      expect(store.user).toBeNull();
    });

    it("should not be authenticated by default", () => {
      expect(store.isAuthenticated).toBe(false);
    });

    it("should not be loading by default", () => {
      expect(store.isLoading).toBe(false);
    });

    it("should not show session warning by default", () => {
      expect(store.sessionWarningShown).toBe(false);
    });
  });

  describe("Getters", () => {
    it("should return false for isAdmin when no user", () => {
      expect(store.isAdmin).toBe(false);
    });

    it("should return true for isAdmin when user is admin", () => {
      store.user = { id: 1, role: "admin", name: "Admin User" };
      expect(store.isAdmin).toBe(true);
    });

    it("should return true for isStaff when user is staff", () => {
      store.user = { id: 1, role: "staff", name: "Staff User" };
      expect(store.isStaff).toBe(true);
    });

    it("should return true for isStaff when user is admin", () => {
      store.user = { id: 1, role: "admin", name: "Admin User" };
      expect(store.isStaff).toBe(true);
    });

    it("should return true for isCustomer when user is customer", () => {
      store.user = { id: 1, role: "customer", name: "Customer User" };
      expect(store.isCustomer).toBe(true);
    });

    it('should return "Guest" for userName when no user', () => {
      expect(store.userName).toBe("Guest");
    });

    it("should return user name when user exists", () => {
      store.user = { id: 1, role: "customer", name: "John Doe" };
      expect(store.userName).toBe("John Doe");
    });

    it('should return "guest" for userRole when no user', () => {
      expect(store.userRole).toBe("guest");
    });

    it("should return empty string for userEmail when no user", () => {
      expect(store.userEmail).toBe("");
    });

    it("should return email when user exists", () => {
      store.user = { id: 1, email: "test@example.com" };
      expect(store.userEmail).toBe("test@example.com");
    });
  });

  describe("login()", () => {
    it("should login successfully with valid credentials", async () => {
      const mockUser = {
        id: 1,
        name: "Test User",
        email: "test@example.com",
        role: "customer",
      };

      api.post.mockResolvedValueOnce({
        data: {
          success: true,
          message: "Login successful",
          data: { user: mockUser },
        },
      });

      const result = await store.login({
        email: "test@example.com",
        password: "password123",
      });

      expect(result.success).toBe(true);
      expect(store.user).toEqual(mockUser);
      expect(store.isAuthenticated).toBe(true);
    });

    it("should fail login with invalid credentials", async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: "Invalid credentials",
            error: {
              errors: {
                email: ["These credentials do not match our records."],
              },
            },
          },
        },
      });

      const result = await store.login({
        email: "wrong@example.com",
        password: "wrongpassword",
      });

      expect(result.success).toBe(false);
      expect(result.message).toBe("Invalid credentials");
      expect(store.isAuthenticated).toBe(false);
    });

    it("should set isLoading during login", async () => {
      let loadingDuringCall = false;

      api.post.mockImplementationOnce(async () => {
        loadingDuringCall = store.isLoading;
        return {
          data: { success: true, data: { user: { id: 1 } } },
        };
      });

      await store.login({ email: "test@example.com", password: "password" });

      expect(loadingDuringCall).toBe(true);
      expect(store.isLoading).toBe(false);
    });
  });

  describe("register()", () => {
    it("should register successfully with valid data", async () => {
      const mockUser = {
        id: 1,
        name: "New User",
        email: "new@example.com",
        role: "customer",
      };

      api.post.mockResolvedValueOnce({
        data: {
          success: true,
          message: "Registration successful",
          data: { user: mockUser },
        },
      });

      const result = await store.register({
        name: "New User",
        email: "new@example.com",
        password: "password123",
        password_confirmation: "password123",
      });

      expect(result.success).toBe(true);
      expect(store.user).toEqual(mockUser);
      expect(store.isAuthenticated).toBe(true);
    });

    it("should fail registration with existing email", async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: "Registration failed",
            error: { errors: { email: ["The email has already been taken."] } },
          },
        },
      });

      const result = await store.register({
        name: "New User",
        email: "existing@example.com",
        password: "password123",
        password_confirmation: "password123",
      });

      expect(result.success).toBe(false);
      expect(result.errors.email).toBeDefined();
    });
  });

  describe("logout()", () => {
    it("should clear auth state on logout", async () => {
      // Setup authenticated state
      store.user = { id: 1, name: "Test User" };
      store.isAuthenticated = true;

      api.post.mockResolvedValueOnce({ data: { success: true } });

      await store.logout(false); // Don't redirect

      expect(store.user).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });

    it("should handle logout errors gracefully", async () => {
      store.user = { id: 1, name: "Test User" };
      store.isAuthenticated = true;

      api.post.mockRejectedValueOnce(new Error("Network error"));

      await store.logout(false);

      // Should still clear auth state even if API call fails
      expect(store.user).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });
  });

  describe("forgotPassword()", () => {
    it("should send password reset link successfully", async () => {
      api.post.mockResolvedValueOnce({
        data: {
          success: true,
          message: "Reset link sent to your email",
        },
      });

      const result = await store.forgotPassword("test@example.com");

      expect(result.success).toBe(true);
      expect(result.message).toBe("Reset link sent to your email");
    });

    it("should handle non-existent email", async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: "We couldn't find a user with that email address.",
          },
        },
      });

      const result = await store.forgotPassword("nonexistent@example.com");

      expect(result.success).toBe(false);
    });
  });

  describe("resetPassword()", () => {
    it("should reset password successfully", async () => {
      api.post.mockResolvedValueOnce({
        data: {
          success: true,
          message: "Password reset successfully",
        },
      });

      const result = await store.resetPassword({
        email: "test@example.com",
        password: "newpassword123",
        password_confirmation: "newpassword123",
        token: "valid-token",
      });

      expect(result.success).toBe(true);
    });

    it("should fail with invalid token", async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: "This password reset token is invalid.",
          },
        },
      });

      const result = await store.resetPassword({
        email: "test@example.com",
        password: "newpassword123",
        password_confirmation: "newpassword123",
        token: "invalid-token",
      });

      expect(result.success).toBe(false);
    });
  });

  describe("checkEmailAvailability()", () => {
    it("should return true for available email", async () => {
      api.post.mockResolvedValueOnce({
        data: {
          data: { available: true },
        },
      });

      const available = await store.checkEmailAvailability("new@example.com");

      expect(available).toBe(true);
    });

    it("should return false for taken email", async () => {
      api.post.mockResolvedValueOnce({
        data: {
          data: { available: false },
        },
      });

      const available = await store.checkEmailAvailability("taken@example.com");

      expect(available).toBe(false);
    });

    it("should return false on error", async () => {
      api.post.mockRejectedValueOnce(new Error("Network error"));

      const available = await store.checkEmailAvailability("test@example.com");

      expect(available).toBe(false);
    });
  });

  describe("refreshSession()", () => {
    it("should refresh session successfully", async () => {
      store.isAuthenticated = true;
      api.post.mockResolvedValueOnce({ data: { success: true } });

      const result = await store.refreshSession();

      expect(result).toBe(true);
      expect(store.sessionWarningShown).toBe(false);
    });

    it("should return false on refresh failure", async () => {
      api.post.mockRejectedValueOnce(new Error("Session expired"));

      const result = await store.refreshSession();

      expect(result).toBe(false);
    });
  });

  describe("Session Management", () => {
    it("should update last activity time", () => {
      const before = store.lastActivityTime;
      store.updateLastActivity();
      expect(store.lastActivityTime).toBeGreaterThanOrEqual(before);
    });

    it("should detect expired session", () => {
      // Set last activity to 6 minutes ago (beyond 5-minute timeout)
      store.lastActivityTime = Date.now() - 6 * 60 * 1000;
      expect(store.isSessionExpired()).toBe(true);
    });

    it("should not detect expired session for recent activity", () => {
      store.lastActivityTime = Date.now() - 1 * 60 * 1000; // 1 minute ago
      expect(store.isSessionExpired()).toBe(false);
    });

    it("should dismiss session warning", () => {
      store.sessionWarningShown = true;
      store.dismissSessionWarning();
      expect(store.sessionWarningShown).toBe(false);
    });

    it("should reset session timer", () => {
      store.isAuthenticated = true;
      store.sessionWarningShown = true;
      store.resetSessionTimer();
      expect(store.sessionWarningShown).toBe(false);
    });
  });

  describe("clearAuth()", () => {
    it("should clear all auth state", () => {
      store.user = { id: 1, name: "Test" };
      store.isAuthenticated = true;
      store.sessionWarningShown = true;

      store.clearAuth();

      expect(store.user).toBeNull();
      expect(store.isAuthenticated).toBe(false);
      expect(store.sessionWarningShown).toBe(false);
    });
  });
});
