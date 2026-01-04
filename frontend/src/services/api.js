/**
 * Axios API Service
 *
 * Centralized HTTP client configuration with interceptors for
 * authentication, error handling, and CSRF token management.
 *
 * @requirement PROJ-INIT-011 Set up Axios instance with interceptors
 */
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

// Base URL for the backend
const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";
const API_URL = `${BASE_URL}/api/v1`;

// Create Axios instance with base configuration for API calls
const api = axios.create({
  baseURL: API_URL,
  timeout: 30000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
  withCredentials: true, // Required for Sanctum cookie-based auth
  withXSRFToken: true,
});

// Create separate instance for non-API calls (like CSRF)
const baseApi = axios.create({
  baseURL: BASE_URL,
  timeout: 30000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
  withCredentials: true,
  withXSRFToken: true,
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add XSRF token from cookie if available
    const xsrfToken = getCookie("XSRF-TOKEN");
    if (xsrfToken) {
      config.headers["X-XSRF-TOKEN"] = decodeURIComponent(xsrfToken);
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    // Handle 401 Unauthorized
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      // List of endpoints where 401 should NOT trigger a redirect or console error
      // These are either public endpoints or auth check endpoints
      // Note: paths are relative to /api/v1 base URL
      const noRedirectEndpoints = [
        "/products",
        "/categories",
        "/public",
        "/auth/user", // Auth check - 401 just means not logged in
        "/auth/login", // Login attempt
        "/auth/register", // Register attempt
        "sanctum/csrf-cookie", // CSRF initialization
      ];

      const shouldNotRedirect = noRedirectEndpoints.some((endpoint) =>
        originalRequest.url?.includes(endpoint)
      );

      // Only redirect if this was a protected endpoint AND we were previously authenticated
      if (!shouldNotRedirect) {
        try {
          const authStore = useAuthStore();
          // Only redirect if user WAS authenticated and NOW got 401 (actual session expiry)
          // Don't redirect if they were never logged in (guest accessing protected route)
          const wasAuthenticated =
            authStore.isAuthenticated ||
            localStorage.getItem("zambezi_auth") === "true";

          if (wasAuthenticated) {
            authStore.clearAuth();
            // Session actually expired - redirect to login
            window.location.href = "/login?session_expired=true";
          } else {
            // Not authenticated, just clear auth state
            authStore.clearAuth();
          }
        } catch (e) {
          console.error("Failed to handle 401:", e);
        }
      } else {
        // For auth/user endpoint, silently reject without logging
        if (originalRequest.url?.includes("/auth/user")) {
          // Mark as handled and don't log in console
          error.handled = true;
          return Promise.reject(error);
        }
      }

      return Promise.reject(error);
    }

    // Handle 419 CSRF Token Mismatch
    if (error.response?.status === 419 && !originalRequest._retryCSRF) {
      originalRequest._retryCSRF = true;

      try {
        // Refresh CSRF token using base API (not versioned endpoint)
        await baseApi.get("/sanctum/csrf-cookie");
        return api(originalRequest);
      } catch (e) {
        console.error("Failed to refresh CSRF token:", e);
      }
    }

    // Handle 422 Validation Errors
    if (error.response?.status === 422) {
      return Promise.reject({
        message: error.response.data.message || "Validation failed",
        errors: error.response.data.errors || {},
        status: 422,
      });
    }

    // Handle 429 Too Many Requests
    if (error.response?.status === 429) {
      return Promise.reject({
        message: "Too many requests. Please try again later.",
        status: 429,
      });
    }

    // Handle 500 Server Errors
    if (error.response?.status >= 500) {
      return Promise.reject({
        message: "An unexpected error occurred. Please try again later.",
        status: error.response.status,
      });
    }

    // Handle network errors
    if (!error.response) {
      return Promise.reject({
        message: "Network error. Please check your connection.",
        status: 0,
      });
    }

    return Promise.reject(error);
  }
);

/**
 * Get cookie value by name
 * @param {string} name - Cookie name
 * @returns {string|null} Cookie value or null
 */
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return parts.pop().split(";").shift();
  }
  return null;
}

/**
 * Initialize CSRF cookie for Sanctum
 */
export async function initializeCsrf() {
  await baseApi.get("/sanctum/csrf-cookie");
}

export { baseApi, BASE_URL, API_URL };
export default api;
