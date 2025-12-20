/**
 * Auth Store
 *
 * Manages user authentication state, login/logout, and session handling.
 *
 * @requirement PROJ-INIT-010 Configure Pinia store structure
 * @requirement AUTH-010 to AUTH-014 Pinia auth store functionality
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api, { initializeCsrf } from "@/services/api";

export const useAuthStore = defineStore("auth", () => {
  // State
  const user = ref(null);
  const isLoading = ref(false);
  const isAuthenticated = ref(false);
  const lastActivityTime = ref(Date.now());
  const sessionWarningShown = ref(false);
  const sessionTimeoutId = ref(null);
  const sessionWarningTimeoutId = ref(null);

  // Session timeout in milliseconds (5 minutes)
  const SESSION_TIMEOUT = 5 * 60 * 1000;
  // Warning shown at 4:30 (30 seconds before timeout)
  const SESSION_WARNING_TIME = 4.5 * 60 * 1000;

  // Getters
  const isAdmin = computed(() => user.value?.role === "admin");
  const isStaff = computed(() => ["staff", "admin"].includes(user.value?.role));
  const isCustomer = computed(() => user.value?.role === "customer");
  const userName = computed(() => user.value?.name || "Guest");
  const userRole = computed(() => user.value?.role || "guest");
  const userEmail = computed(() => user.value?.email || "");
  const userPhone = computed(() => user.value?.phone || "");
  const isEmailVerified = computed(() => !!user.value?.email_verified_at);

  // Actions

  /**
   * Initialize authentication state
   */
  async function initialize() {
    isLoading.value = true;
    try {
      await initializeCsrf();
      // Only try to fetch user if we might have a session
      // This prevents 401 errors on login/register pages
      try {
        await fetchUser();
        startSessionTimer();
      } catch {
        // Silently fail - user is just not authenticated
        // This is normal for guests browsing the shop
        clearAuth();
      }
    } catch {
      // CSRF initialization failed - user can still browse as guest
      clearAuth();
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch current authenticated user
   */
  async function fetchUser() {
    try {
      const response = await api.get("/auth/user");
      if (response.data?.success) {
        user.value = response.data.data.user;
        isAuthenticated.value = true;
        updateLastActivity();
      }
    } catch (error) {
      // Only throw for non-401 errors (401 is expected for unauthenticated users)
      if (error.response?.status !== 401) {
        console.error("Failed to fetch user:", error);
      }
      clearAuth();
      throw error;
    }
  }

  /**
   * Login user
   * @param {Object} credentials - { email, password, remember }
   */
  async function login(credentials) {
    isLoading.value = true;
    try {
      await initializeCsrf();
      const response = await api.post("/auth/login", credentials);
      if (response.data?.success) {
        user.value = response.data.data.user;
        isAuthenticated.value = true;
        startSessionTimer();
        return { success: true, message: response.data.message };
      }
      return { success: false, message: "Login failed" };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || "Login failed",
        errors: error.response?.data?.error?.errors || {},
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Register new user
   * @param {Object} userData - { name, email, phone, password, password_confirmation }
   */
  async function register(userData) {
    isLoading.value = true;
    try {
      await initializeCsrf();
      const response = await api.post("/auth/register", userData);
      if (response.data?.success) {
        user.value = response.data.data.user;
        isAuthenticated.value = true;
        startSessionTimer();
        return { success: true, message: response.data.message };
      }
      return { success: false, message: "Registration failed" };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || "Registration failed",
        errors: error.response?.data?.error?.errors || {},
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Logout user
   */
  async function logout(redirectToHome = true, reason = null) {
    isLoading.value = true;
    try {
      await api.post("/auth/logout");
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      clearAuth();
      stopSessionTimer();
      isLoading.value = false;

      // Clear cart on logout (Issue 5)
      if (typeof window !== "undefined") {
        try {
          const { useCartStore } = await import("./cart");
          const cartStore = useCartStore();
          cartStore.clearOnLogout();
        } catch (err) {
          console.error("Failed to clear cart on logout:", err);
        }
      }

      // Redirect to homepage if specified
      if (redirectToHome && typeof window !== "undefined") {
        try {
          const router = (await import("../router")).default;
          router.push({
            path: "/",
            query: reason ? { message: reason } : undefined,
          });
        } catch (err) {
          // Fallback to window location if router import fails
          window.location.href = reason
            ? `/?message=${encodeURIComponent(reason)}`
            : "/";
        }
      }
    }
  }

  /**
   * Request password reset
   * @param {string} email - User's email address
   */
  async function forgotPassword(email) {
    isLoading.value = true;
    try {
      await initializeCsrf();
      const response = await api.post("/auth/forgot-password", {
        email,
      });
      return {
        success: response.data?.success,
        message: response.data?.message,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || "Failed to send reset link",
        errors: error.response?.data?.error?.errors || {},
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Reset password with token
   * @param {Object} data - { email, password, password_confirmation, token }
   */
  async function resetPassword(data) {
    isLoading.value = true;
    try {
      await initializeCsrf();
      const response = await api.post("/auth/reset-password", data);
      return {
        success: response.data?.success,
        message: response.data?.message,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || "Failed to reset password",
        errors: error.response?.data?.error?.errors || {},
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Check if email is available for registration
   * @param {string} email - Email to check
   */
  async function checkEmailAvailability(email) {
    try {
      const response = await api.post("/public/check-email", { email });
      return response.data?.data?.available ?? false;
    } catch (error) {
      console.error("Email check failed:", error);
      return false;
    }
  }

  /**
   * Refresh session to prevent timeout
   */
  async function refreshSession() {
    try {
      await api.post("/auth/refresh");
      updateLastActivity();
      sessionWarningShown.value = false;
      startSessionTimer();
      return true;
    } catch (error) {
      console.error("Session refresh failed:", error);
      return false;
    }
  }

  /**
   * Clear authentication state
   */
  function clearAuth() {
    user.value = null;
    isAuthenticated.value = false;
    sessionWarningShown.value = false;
  }

  /**
   * Update last activity timestamp
   */
  function updateLastActivity() {
    lastActivityTime.value = Date.now();
  }

  /**
   * Start session timeout timer
   */
  function startSessionTimer() {
    stopSessionTimer();

    if (!isAuthenticated.value) return;

    // Set warning timer (4:30)
    sessionWarningTimeoutId.value = setTimeout(() => {
      sessionWarningShown.value = true;
    }, SESSION_WARNING_TIME);

    // Set timeout timer (5:00)
    sessionTimeoutId.value = setTimeout(async () => {
      sessionWarningShown.value = false;
      await logout(true, "Session expired due to inactivity");
    }, SESSION_TIMEOUT);
  }

  /**
   * Stop session timeout timer
   */
  function stopSessionTimer() {
    if (sessionTimeoutId.value) {
      clearTimeout(sessionTimeoutId.value);
      sessionTimeoutId.value = null;
    }
    if (sessionWarningTimeoutId.value) {
      clearTimeout(sessionWarningTimeoutId.value);
      sessionWarningTimeoutId.value = null;
    }
  }

  /**
   * Reset session timer on user activity
   */
  function resetSessionTimer() {
    updateLastActivity();
    sessionWarningShown.value = false;
    startSessionTimer();
  }

  /**
   * Check if session has expired due to inactivity
   */
  function isSessionExpired() {
    return Date.now() - lastActivityTime.value > SESSION_TIMEOUT;
  }

  /**
   * Check session and logout if expired
   */
  async function checkSession() {
    if (isAuthenticated.value && isSessionExpired()) {
      await logout();
      return false;
    }
    return true;
  }

  /**
   * Dismiss session warning
   */
  function dismissSessionWarning() {
    sessionWarningShown.value = false;
  }

  return {
    // State
    user,
    isLoading,
    isAuthenticated,
    lastActivityTime,
    sessionWarningShown,

    // Getters
    isAdmin,
    isStaff,
    isCustomer,
    userName,
    userRole,
    userEmail,
    userPhone,
    isEmailVerified,

    // Actions
    initialize,
    fetchUser,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    checkEmailAvailability,
    refreshSession,
    clearAuth,
    updateLastActivity,
    resetSessionTimer,
    isSessionExpired,
    checkSession,
    dismissSessionWarning,
  };
});
