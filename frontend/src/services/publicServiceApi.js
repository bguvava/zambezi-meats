/**
 * Public Service API Service
 *
 * Handles all API calls for public service browsing (no authentication required)
 * @module services/publicServiceApi
 */

import axios from "axios";

const API_BASE_URL =
  import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1";

/**
 * Public Services API
 */
export const publicServiceApi = {
  /**
   * Get all active services for public viewing
   * @param {Object} params - Query parameters
   * @param {number} params.category_id - Filter by category
   * @param {string} params.billing_cycle - Filter by billing cycle
   * @param {boolean} params.featured - Show only featured services
   * @param {string} params.search - Search query
   * @param {number} params.per_page - Items per page
   * @param {number} params.page - Current page
   * @returns {Promise<Object>} Public services data
   */
  async getAll(params = {}) {
    const response = await axios.get(`${API_BASE_URL}/public/services`, {
      params: {
        ...params,
        status: "active", // Only show active services to public
      },
    });
    return response.data;
  },

  /**
   * Get a single active service by ID or slug
   * @param {number|string} idOrSlug - Service ID or slug
   * @returns {Promise<Object>} Service data
   */
  async getByIdOrSlug(idOrSlug) {
    const response = await axios.get(
      `${API_BASE_URL}/public/services/${idOrSlug}`
    );
    return response.data;
  },

  /**
   * Get all active service categories
   * @returns {Promise<Object>} Public categories data
   */
  async getCategories() {
    const response = await axios.get(
      `${API_BASE_URL}/public/service-categories`,
      {
        params: {
          status: "active",
        },
      }
    );
    return response.data;
  },

  /**
   * Get featured services
   * @param {number} limit - Number of featured services to retrieve
   * @returns {Promise<Object>} Featured services data
   */
  async getFeatured(limit = 6) {
    const response = await axios.get(`${API_BASE_URL}/public/services`, {
      params: {
        featured: true,
        status: "active",
        per_page: limit,
      },
    });
    return response.data;
  },

  /**
   * Submit a service inquiry
   * @param {Object} data - Inquiry data
   * @param {number} data.service_id - Service ID
   * @param {string} data.name - Customer name
   * @param {string} data.email - Customer email
   * @param {string} data.phone - Customer phone
   * @param {string} data.message - Inquiry message
   * @returns {Promise<Object>} Success message
   */
  async submitInquiry(data) {
    const response = await axios.post(
      `${API_BASE_URL}/public/service-inquiries`,
      data
    );
    return response.data;
  },
};

export default publicServiceApi;
