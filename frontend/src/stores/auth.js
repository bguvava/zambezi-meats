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
import router from "@/router";
import { useCartStore } from "./cart";

export const useAuthStore = defineStore("auth", () => {
  // State
  const user = ref(null);
  const isLoading = ref(false);
  const isAuthenticated = ref(false);
  const lastActivityTime = ref(Date.now());
  const sessionWarningShown = ref(false);
  const sessionLocked = ref(false);
  const sessionTimeoutId = ref(null);
  const sessionWarningTimeoutId = ref(null);
  const initialized = ref(false); // Track if auth has been initialized

  // Session timeout in milliseconds (30 minutes of inactivity)
  const SESSION_TIMEOUT = 30 * 60 * 1000;
  // Warning shown at 29 minutes (1 minute before timeout)
  const SESSION_WARNING_TIME = 29 * 60 * 1000;

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
   * Only fetches user if we have a valid session cookie
   */
  async function initialize() {
    // Prevent double initialization
    if (initialized.value && user.value) {
      return;
    }

    isLoading.value = true;
    try {
      await initializeCsrf();

      // Check if user was previously authenticated (from localStorage)
      const wasAuthenticated = localStorage.getItem("zambezi_auth") === "true";

      // Only try to fetch user if they were previously authenticated
      if (wasAuthenticated) {
        try {
          await fetchUser();
          if (isAuthenticated.value) {
            startSessionTimer();
            initialized.value = true;
          } else {
            // Clear stale auth data if fetch failed
            clearAuth();
          }
        } catch (error) {
          // Clear auth on any non-401 error
          if (error.response?.status !== 401) {
            console.error("Auth initialization error:", error);
          }
          clearAuth();
        }
      } else {
        // No previous authentication - user is a guest
        clearAuth();
      }
    } catch (error) {
      console.error("CSRF initialization failed:", error);
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

        // Persist auth state to localStorage for page refresh
        localStorage.setItem("zambezi_auth", "true");
        localStorage.setItem("zambezi_user_role", user.value.role);
      }
    } catch (error) {
      // Silently handle 401 for unauthenticated users (expected behavior)
      if (error.response?.status === 401 || error.handled) {
        // This is normal for guest users - no need to log
        clearAuth();
        return;
      }
      // Log other errors only in development
      if (import.meta.env.DEV && error.response?.status !== 401) {
        console.error("Failed to fetch user:", error);
      }
      clearAuth();
      // Don't throw for 401 errors
      if (error.response?.status !== 401) {
        throw error;
      }
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
        initialized.value = true;
        updateLastActivity();

        // Persist auth state to localStorage for page refresh
        localStorage.setItem("zambezi_auth", "true");
        localStorage.setItem("zambezi_user_role", user.value.role);

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
        initialized.value = true;
        updateLastActivity();

        // Persist auth state to localStorage for page refresh
        localStorage.setItem("zambezi_auth", "true");
        localStorage.setItem("zambezi_user_role", user.value.role);

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
      const cartStore = useCartStore();
      cartStore.clearOnLogout();

      // Redirect to homepage if specified
      if (redirectToHome) {
        router.push({
          path: "/",
          query: reason ? { message: reason } : undefined,
        });
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
    initialized.value = false;

    // Clear localStorage
    localStorage.removeItem("zambezi_auth");
    localStorage.removeItem("zambezi_user_role");
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
      // Only show warning if user is still authenticated and not already locked
      if (isAuthenticated.value && !sessionLocked.value) {
        sessionWarningShown.value = true;
      }
    }, SESSION_WARNING_TIME);

    // Set lock screen timer (5:00)
    sessionTimeoutId.value = setTimeout(() => {
      // Only lock if user is still authenticated and hasn't been active
      if (isAuthenticated.value && !sessionWarningShown.value === false) {
        sessionWarningShown.value = false;
        sessionLocked.value = true;
      }
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
    // Only reset if authenticated and not locked
    if (!isAuthenticated.value || sessionLocked.value) return;

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

  /**
   * Unlock session with password
   * @param {string} password - User's password
   */
  async function unlockSession(password) {
    try {
      // Use login endpoint instead of unlock since it's the same validation
      const response = await api.post("/auth/login", {
        email: user.value.email,
        password: password,
      });

      if (response.data?.success) {
        sessionLocked.value = false;
        updateLastActivity();
        startSessionTimer();
        return { success: true };
      }

      return { success: false, message: "Invalid password" };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || "Incorrect password",
      };
    }
  }

  /**
   * Lock session manually
   */
  function lockSession() {
    sessionLocked.value = true;
    stopSessionTimer();
  }

  return {
    // State
    user,
    isLoading,
    isAuthenticated,
    lastActivityTime,
    sessionWarningShown,
    sessionLocked,
    initialized,

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
    unlockSession,
    lockSession,
  };
});
