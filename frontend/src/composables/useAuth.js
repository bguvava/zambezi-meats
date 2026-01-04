/**
 * useAuth Composable
 *
 * Provides authentication utilities and state for Vue components.
 *
 * @requirement AUTH-013 Create auth composables
 */
import { computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

export function useAuth() {
  const authStore = useAuthStore();
  const router = useRouter();

  // Computed properties for easy access
  const user = computed(() => authStore.user);
  const isAuthenticated = computed(() => authStore.isAuthenticated);
  const isLoading = computed(() => authStore.isLoading);
  const isAdmin = computed(() => authStore.isAdmin);
  const isStaff = computed(() => authStore.isStaff);
  const isCustomer = computed(() => authStore.isCustomer);
  const userName = computed(() => authStore.userName);
  const userRole = computed(() => authStore.userRole);
  const sessionWarningShown = computed(() => authStore.sessionWarningShown);

  /**
   * Login with redirect after success
   * @param {Object} credentials - { email, password, remember }
   * @param {string} redirectTo - Path to redirect after login
   */
  async function login(credentials, redirectTo = null) {
    const result = await authStore.login(credentials);

    if (result.success) {
      // Redirect based on role or specified path
      const destination = redirectTo || getDefaultRedirect();
      router.push(destination);
    }

    return result;
  }

  /**
   * Register with redirect after success
   * @param {Object} userData - { name, email, phone, password, password_confirmation }
   * @param {string} redirectTo - Path to redirect after registration
   */
  async function register(userData, redirectTo = null) {
    const result = await authStore.register(userData);

    if (result.success) {
      const destination = redirectTo || getDefaultRedirect();
      router.push(destination);
    }

    return result;
  }

  /**
   * Logout with redirect to homepage
   */
  async function logout() {
    await authStore.logout();
    // Redirect to homepage after logout
    router.push({ name: "home" });
  }

  /**
   * Get default redirect path based on user role
   */
  function getDefaultRedirect() {
    const role = authStore.userRole;

    switch (role) {
      case "admin":
        return "/admin";
      case "staff":
        return "/staff";
      case "customer":
        return "/customer"; // Redirect to customer dashboard, not shop
      default:
        return "/shop";
    }
  }

  /**
   * Request password reset
   * @param {string} email - User's email address
   */
  async function forgotPassword(email) {
    return await authStore.forgotPassword(email);
  }

  /**
   * Reset password with token
   * @param {Object} data - { email, password, password_confirmation, token }
   */
  async function resetPassword(data) {
    const result = await authStore.resetPassword(data);

    if (result.success) {
      router.push({ name: "login", query: { message: "password_reset" } });
    }

    return result;
  }

  /**
   * Check if email is available
   * @param {string} email - Email to check
   */
  async function checkEmailAvailability(email) {
    return await authStore.checkEmailAvailability(email);
  }

  /**
   * Refresh session to prevent timeout
   */
  async function refreshSession() {
    return await authStore.refreshSession();
  }

  /**
   * Dismiss session warning modal
   */
  function dismissSessionWarning() {
    authStore.dismissSessionWarning();
  }

  /**
   * Check if user has specific role
   * @param {string|string[]} roles - Role or array of roles to check
   */
  function hasRole(roles) {
    const userRole = authStore.userRole;
    if (Array.isArray(roles)) {
      return roles.includes(userRole);
    }
    return userRole === roles;
  }

  /**
   * Check if user can access a resource
   * @param {string[]} allowedRoles - Roles allowed to access
   */
  function canAccess(allowedRoles) {
    if (!isAuthenticated.value) return false;
    return hasRole(allowedRoles);
  }

  /**
   * Setup activity listeners for session management
   */
  function setupActivityListeners() {
    const resetTimer = () => {
      if (authStore.isAuthenticated) {
        authStore.resetSessionTimer();
      }
    };

    const events = ["mousedown", "keydown", "scroll", "touchstart"];
    events.forEach((event) => {
      document.addEventListener(event, resetTimer, { passive: true });
    });

    // Return cleanup function
    return () => {
      events.forEach((event) => {
        document.removeEventListener(event, resetTimer);
      });
    };
  }

  return {
    // State
    user,
    isAuthenticated,
    isLoading,
    isAdmin,
    isStaff,
    isCustomer,
    userName,
    userRole,
    sessionWarningShown,

    // Methods
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    checkEmailAvailability,
    refreshSession,
    dismissSessionWarning,
    hasRole,
    canAccess,
    getDefaultRedirect,
    setupActivityListeners,
  };
}

/**
 * useSessionTimeout Composable
 *
 * Manages session timeout with activity detection
 */
export function useSessionTimeout() {
  const authStore = useAuthStore();
  let cleanupFn = null;

  onMounted(() => {
    if (authStore.isAuthenticated) {
      const events = ["mousedown", "keydown", "scroll", "touchstart"];
      const resetTimer = () => authStore.resetSessionTimer();

      events.forEach((event) => {
        document.addEventListener(event, resetTimer, { passive: true });
      });

      cleanupFn = () => {
        events.forEach((event) => {
          document.removeEventListener(event, resetTimer);
        });
      };
    }
  });

  onUnmounted(() => {
    if (cleanupFn) cleanupFn();
  });

  return {
    sessionWarningShown: computed(() => authStore.sessionWarningShown),
    refreshSession: () => authStore.refreshSession(),
    dismissWarning: () => authStore.dismissSessionWarning(),
  };
}
