/**
 * useAuth Composable Tests
 *
 * Tests for the useAuth composable functionality.
 *
 * @requirement AUTH-013 Create auth composables
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import { useAuth, useSessionTimeout } from "@/composables/useAuth";
import { useAuthStore } from "@/stores/auth";

// Mock vue-router
const mockPush = vi.fn();
vi.mock("vue-router", async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useRouter: () => ({
      push: mockPush,
    }),
  };
});

describe("useAuth Composable", () => {
  let authStore;
  let auth;

  beforeEach(() => {
    setActivePinia(createPinia());
    authStore = useAuthStore();
    vi.clearAllMocks();

    // Mock the auth store methods
    authStore.login = vi.fn();
    authStore.register = vi.fn();
    authStore.logout = vi.fn();
    authStore.forgotPassword = vi.fn();
    authStore.resetPassword = vi.fn();
    authStore.checkEmailAvailability = vi.fn();
    authStore.refreshSession = vi.fn();
    authStore.dismissSessionWarning = vi.fn();
    authStore.resetSessionTimer = vi.fn();

    auth = useAuth();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe("Computed Properties", () => {
    it("should expose user as computed", () => {
      expect(auth.user.value).toBeNull();
      authStore.user = { id: 1, name: "Test" };
      expect(auth.user.value).toEqual({ id: 1, name: "Test" });
    });

    it("should expose isAuthenticated as computed", () => {
      expect(auth.isAuthenticated.value).toBe(false);
      authStore.isAuthenticated = true;
      expect(auth.isAuthenticated.value).toBe(true);
    });

    it("should expose isLoading as computed", () => {
      expect(auth.isLoading.value).toBe(false);
      authStore.isLoading = true;
      expect(auth.isLoading.value).toBe(true);
    });

    it("should expose isAdmin as computed", () => {
      expect(auth.isAdmin.value).toBe(false);
      authStore.user = { role: "admin" };
      expect(auth.isAdmin.value).toBe(true);
    });

    it("should expose isStaff as computed", () => {
      expect(auth.isStaff.value).toBe(false);
      authStore.user = { role: "staff" };
      expect(auth.isStaff.value).toBe(true);
    });

    it("should expose isCustomer as computed", () => {
      expect(auth.isCustomer.value).toBe(false);
      authStore.user = { role: "customer" };
      expect(auth.isCustomer.value).toBe(true);
    });

    it("should expose userName as computed", () => {
      expect(auth.userName.value).toBe("Guest");
      authStore.user = { name: "John Doe" };
      expect(auth.userName.value).toBe("John Doe");
    });

    it("should expose userRole as computed", () => {
      expect(auth.userRole.value).toBe("guest");
      authStore.user = { role: "customer" };
      expect(auth.userRole.value).toBe("customer");
    });

    it("should expose sessionWarningShown as computed", () => {
      expect(auth.sessionWarningShown.value).toBe(false);
      authStore.sessionWarningShown = true;
      expect(auth.sessionWarningShown.value).toBe(true);
    });
  });

  describe("login()", () => {
    it("should call authStore.login and redirect on success", async () => {
      authStore.login.mockResolvedValueOnce({ success: true });
      authStore.user = { role: "customer" };

      await auth.login({ email: "test@example.com", password: "password" });

      expect(authStore.login).toHaveBeenCalledWith({
        email: "test@example.com",
        password: "password",
      });
      expect(mockPush).toHaveBeenCalled();
    });

    it("should not redirect on login failure", async () => {
      authStore.login.mockResolvedValueOnce({
        success: false,
        message: "Invalid",
      });

      const result = await auth.login({
        email: "wrong@example.com",
        password: "wrong",
      });

      expect(result.success).toBe(false);
      expect(mockPush).not.toHaveBeenCalled();
    });

    it("should redirect to custom path when specified", async () => {
      authStore.login.mockResolvedValueOnce({ success: true });
      authStore.user = { role: "customer" };

      await auth.login(
        { email: "test@example.com", password: "password" },
        "/custom/path"
      );

      expect(mockPush).toHaveBeenCalledWith("/custom/path");
    });
  });

  describe("register()", () => {
    it("should call authStore.register and redirect on success", async () => {
      authStore.register.mockResolvedValueOnce({ success: true });
      authStore.user = { role: "customer" };

      await auth.register({
        name: "New User",
        email: "new@example.com",
        password: "password123",
        password_confirmation: "password123",
      });

      expect(authStore.register).toHaveBeenCalled();
      expect(mockPush).toHaveBeenCalled();
    });

    it("should not redirect on registration failure", async () => {
      authStore.register.mockResolvedValueOnce({ success: false });

      await auth.register({
        name: "New User",
        email: "existing@example.com",
        password: "password123",
        password_confirmation: "password123",
      });

      expect(mockPush).not.toHaveBeenCalled();
    });
  });

  describe("logout()", () => {
    it("should call authStore.logout and redirect to home", async () => {
      authStore.logout.mockResolvedValueOnce();

      await auth.logout();

      expect(authStore.logout).toHaveBeenCalled();
      expect(mockPush).toHaveBeenCalledWith({ name: "home" });
    });
  });

  describe("forgotPassword()", () => {
    it("should call authStore.forgotPassword", async () => {
      authStore.forgotPassword.mockResolvedValueOnce({ success: true });

      await auth.forgotPassword("test@example.com");

      expect(authStore.forgotPassword).toHaveBeenCalledWith("test@example.com");
    });
  });

  describe("resetPassword()", () => {
    it("should call authStore.resetPassword and redirect on success", async () => {
      authStore.resetPassword.mockResolvedValueOnce({ success: true });

      await auth.resetPassword({
        email: "test@example.com",
        password: "newpassword",
        password_confirmation: "newpassword",
        token: "valid-token",
      });

      expect(authStore.resetPassword).toHaveBeenCalled();
      expect(mockPush).toHaveBeenCalledWith({
        name: "login",
        query: { message: "password_reset" },
      });
    });

    it("should not redirect on reset failure", async () => {
      authStore.resetPassword.mockResolvedValueOnce({ success: false });

      await auth.resetPassword({
        email: "test@example.com",
        password: "newpassword",
        password_confirmation: "newpassword",
        token: "invalid-token",
      });

      expect(mockPush).not.toHaveBeenCalled();
    });
  });

  describe("checkEmailAvailability()", () => {
    it("should call authStore.checkEmailAvailability", async () => {
      authStore.checkEmailAvailability.mockResolvedValueOnce(true);

      const result = await auth.checkEmailAvailability("new@example.com");

      expect(authStore.checkEmailAvailability).toHaveBeenCalledWith(
        "new@example.com"
      );
      expect(result).toBe(true);
    });
  });

  describe("refreshSession()", () => {
    it("should call authStore.refreshSession", async () => {
      authStore.refreshSession.mockResolvedValueOnce(true);

      const result = await auth.refreshSession();

      expect(authStore.refreshSession).toHaveBeenCalled();
      expect(result).toBe(true);
    });
  });

  describe("dismissSessionWarning()", () => {
    it("should call authStore.dismissSessionWarning", () => {
      auth.dismissSessionWarning();
      expect(authStore.dismissSessionWarning).toHaveBeenCalled();
    });
  });

  describe("getDefaultRedirect()", () => {
    it("should return /admin for admin role", () => {
      authStore.user = { role: "admin" };
      expect(auth.getDefaultRedirect()).toBe("/admin");
    });

    it("should return /staff for staff role", () => {
      authStore.user = { role: "staff" };
      expect(auth.getDefaultRedirect()).toBe("/staff");
    });

    it("should return /shop for customer role", () => {
      authStore.user = { role: "customer" };
      expect(auth.getDefaultRedirect()).toBe("/shop");
    });

    it("should return /shop for unknown role", () => {
      authStore.user = { role: "unknown" };
      expect(auth.getDefaultRedirect()).toBe("/shop");
    });
  });

  describe("hasRole()", () => {
    it("should return true when user has matching role", () => {
      authStore.user = { role: "admin" };
      expect(auth.hasRole("admin")).toBe(true);
    });

    it("should return false when user has different role", () => {
      authStore.user = { role: "customer" };
      expect(auth.hasRole("admin")).toBe(false);
    });

    it("should return true when role is in array", () => {
      authStore.user = { role: "staff" };
      expect(auth.hasRole(["admin", "staff"])).toBe(true);
    });

    it("should return false when role is not in array", () => {
      authStore.user = { role: "customer" };
      expect(auth.hasRole(["admin", "staff"])).toBe(false);
    });
  });

  describe("canAccess()", () => {
    it("should return false when not authenticated", () => {
      authStore.isAuthenticated = false;
      expect(auth.canAccess(["admin"])).toBe(false);
    });

    it("should return true when authenticated and has role", () => {
      authStore.isAuthenticated = true;
      authStore.user = { role: "admin" };
      expect(auth.canAccess(["admin"])).toBe(true);
    });

    it("should return false when authenticated but wrong role", () => {
      authStore.isAuthenticated = true;
      authStore.user = { role: "customer" };
      expect(auth.canAccess(["admin"])).toBe(false);
    });
  });

  describe("setupActivityListeners()", () => {
    it("should return a cleanup function", () => {
      const cleanup = auth.setupActivityListeners();
      expect(typeof cleanup).toBe("function");
      cleanup(); // Clean up
    });
  });
});
