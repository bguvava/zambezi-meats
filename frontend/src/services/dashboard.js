/**
 * Dashboard API Service
 *
 * Provides API methods for fetching dashboard data across all user roles.
 */
import api from "./api";

/**
 * Customer Dashboard APIs
 */
export const customerDashboard = {
  /**
   * Get customer dashboard overview
   */
  async getOverview() {
    const response = await api.get("/customer/dashboard");
    return response.data;
  },

  /**
   * Get customer orders
   */
  async getOrders(params = {}) {
    const response = await api.get("/customer/orders", { params });
    return response.data;
  },

  /**
   * Get customer order by number
   */
  async getOrder(orderNumber) {
    const response = await api.get(`/customer/orders/${orderNumber}`);
    return response.data;
  },

  /**
   * Get customer profile
   */
  async getProfile() {
    const response = await api.get("/customer/profile");
    return response.data;
  },

  /**
   * Update customer profile
   */
  async updateProfile(data) {
    const response = await api.put("/customer/profile", data);
    return response.data;
  },

  /**
   * Get customer addresses
   */
  async getAddresses() {
    const response = await api.get("/customer/addresses");
    return response.data;
  },

  /**
   * Create new address
   */
  async createAddress(data) {
    const response = await api.post("/customer/addresses", data);
    return response.data;
  },

  /**
   * Update address
   */
  async updateAddress(id, data) {
    const response = await api.put(`/customer/addresses/${id}`, data);
    return response.data;
  },

  /**
   * Delete address
   */
  async deleteAddress(id) {
    const response = await api.delete(`/customer/addresses/${id}`);
    return response.data;
  },

  /**
   * Get customer wishlist
   */
  async getWishlist() {
    const response = await api.get("/customer/wishlist");
    return response.data;
  },

  /**
   * Add item to wishlist
   */
  async addToWishlist(productId) {
    const response = await api.post("/customer/wishlist", {
      product_id: productId,
    });
    return response.data;
  },

  /**
   * Remove item from wishlist
   */
  async removeFromWishlist(productId) {
    const response = await api.delete(`/customer/wishlist/${productId}`);
    return response.data;
  },
};

/**
 * Staff Dashboard APIs
 */
export const staffDashboard = {
  /**
   * Get staff dashboard overview
   */
  async getOverview() {
    const response = await api.get("/staff/dashboard");
    return response.data;
  },

  // ==================== ORDER QUEUE ====================

  /**
   * Get order queue with filtering
   */
  async getOrderQueue(params = {}) {
    const response = await api.get("/staff/orders/queue", { params });
    return response.data;
  },

  /**
   * Get staff orders
   */
  async getOrders(params = {}) {
    const response = await api.get("/staff/orders", { params });
    return response.data;
  },

  /**
   * Get single order details
   */
  async getOrder(orderId) {
    const response = await api.get(`/staff/orders/${orderId}`);
    return response.data;
  },

  /**
   * Update order status
   */
  async updateOrderStatus(orderId, status, notes = null) {
    const data = { status };
    if (notes) data.notes = notes;
    const response = await api.put(`/staff/orders/${orderId}/status`, data);
    return response.data;
  },

  /**
   * Add note to order
   */
  async addOrderNote(orderId, note) {
    const response = await api.post(`/staff/orders/${orderId}/notes`, { note });
    return response.data;
  },

  // ==================== DELIVERIES ====================

  /**
   * Get staff deliveries
   */
  async getDeliveries(params = {}) {
    const response = await api.get("/staff/deliveries", { params });
    return response.data;
  },

  /**
   * Get today's deliveries
   */
  async getTodaysDeliveries() {
    const response = await api.get("/staff/deliveries/today");
    return response.data;
  },

  /**
   * Mark order as out for delivery
   */
  async markOutForDelivery(orderId) {
    const response = await api.post(
      `/staff/deliveries/${orderId}/out-for-delivery`
    );
    return response.data;
  },

  /**
   * Update delivery status
   */
  async updateDeliveryStatus(deliveryId, status, data = {}) {
    const response = await api.patch(`/staff/deliveries/${deliveryId}/status`, {
      status,
      ...data,
    });
    return response.data;
  },

  /**
   * Upload proof of delivery (signature + photo)
   */
  async uploadProofOfDelivery(orderId, formData) {
    const response = await api.post(
      `/staff/deliveries/${orderId}/pod`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );
    return response.data;
  },

  /**
   * Get proof of delivery
   */
  async getProofOfDelivery(orderId) {
    const response = await api.get(`/staff/deliveries/${orderId}/pod`);
    return response.data;
  },

  /**
   * Upload delivery proof (legacy)
   */
  async uploadDeliveryProof(deliveryId, formData) {
    const response = await api.post(
      `/staff/deliveries/${deliveryId}/proof`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );
    return response.data;
  },

  // ==================== PICKUPS ====================

  /**
   * Get today's pickups
   */
  async getTodaysPickups() {
    const response = await api.get("/staff/pickups/today");
    return response.data;
  },

  /**
   * Mark pickup as complete
   */
  async markAsPickedUp(orderId, receiverName) {
    const response = await api.post(`/staff/pickups/${orderId}/picked-up`, {
      receiver_name: receiverName,
    });
    return response.data;
  },

  // ==================== STOCK CHECK ====================

  /**
   * Get stock check list
   */
  async getStockCheck(params = {}) {
    const response = await api.get("/staff/stock", { params });
    return response.data;
  },

  /**
   * Update product stock count
   */
  async updateStock(productId, quantity, notes = null) {
    const data = { quantity };
    if (notes) data.notes = notes;
    const response = await api.put(`/staff/stock/${productId}`, data);
    return response.data;
  },

  // ==================== WASTE LOG ====================

  /**
   * Get waste logs
   */
  async getWasteLogs(params = {}) {
    const response = await api.get("/staff/waste", { params });
    return response.data;
  },

  /**
   * Log waste item
   */
  async logWaste(data) {
    const response = await api.post("/staff/waste", data);
    return response.data;
  },

  // ==================== ACTIVITY ====================

  /**
   * Get activity log
   */
  async getActivityLog(params = {}) {
    const response = await api.get("/staff/activity", { params });
    return response.data;
  },

  /**
   * Get performance stats
   */
  async getPerformanceStats(params = {}) {
    const response = await api.get("/staff/performance", { params });
    return response.data;
  },
};

/**
 * Admin Dashboard APIs
 */
export const adminDashboard = {
  /**
   * Get admin dashboard overview with stats
   */
  async getOverview() {
    const response = await api.get("/admin/dashboard");
    return response.data;
  },

  // ==================== ORDERS ====================

  /**
   * Get all orders with filtering
   */
  async getOrders(params = {}) {
    const response = await api.get("/admin/orders", { params });
    return response.data;
  },

  /**
   * Get order details
   */
  async getOrder(id) {
    const response = await api.get(`/admin/orders/${id}`);
    return response.data;
  },

  /**
   * Update order
   */
  async updateOrder(id, data) {
    const response = await api.put(`/admin/orders/${id}`, data);
    return response.data;
  },

  /**
   * Update order status
   */
  async updateOrderStatus(id, status, reason = null) {
    const data = { status };
    if (reason) data.reason = reason;
    const response = await api.put(`/admin/orders/${id}/status`, data);
    return response.data;
  },

  /**
   * Assign order to staff
   */
  async assignOrder(orderId, staffId) {
    const response = await api.post(`/admin/orders/${orderId}/assign`, {
      staff_id: staffId,
    });
    return response.data;
  },

  /**
   * Process refund
   */
  async refundOrder(orderId, amount, reason) {
    const response = await api.post(`/admin/orders/${orderId}/refund`, {
      amount,
      reason,
    });
    return response.data;
  },

  // ==================== PRODUCTS ====================

  /**
   * Get all products
   */
  async getProducts(params = {}) {
    const response = await api.get("/admin/products", { params });
    return response.data;
  },

  /**
   * Get single product
   */
  async getProduct(id) {
    const response = await api.get(`/admin/products/${id}`);
    return response.data;
  },

  /**
   * Create new product
   */
  async createProduct(data) {
    const response = await api.post("/admin/products", data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  /**
   * Update product
   */
  async updateProduct(id, data) {
    const response = await api.put(`/admin/products/${id}`, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  /**
   * Delete product
   */
  async deleteProduct(id) {
    const response = await api.delete(`/admin/products/${id}`);
    return response.data;
  },

  /**
   * Get low stock products
   */
  async getLowStockProducts(threshold = 10) {
    const response = await api.get("/admin/products/low-stock", {
      params: { threshold },
    });
    return response.data;
  },

  /**
   * Adjust stock
   */
  async adjustStock(productId, quantity, type, reason) {
    const response = await api.post(
      `/admin/products/${productId}/adjust-stock`,
      {
        quantity,
        type,
        reason,
      }
    );
    return response.data;
  },

  /**
   * Export products to PDF
   */
  async exportProducts(params = {}) {
    const response = await api.post("/admin/products/export", params, {
      responseType: "blob",
    });
    return response.data;
  },

  // ==================== CATEGORIES ====================

  /**
   * Get all categories
   */
  async getCategories(params = {}) {
    const response = await api.get("/admin/categories", { params });
    return response.data;
  },

  /**
   * Create new category
   */
  async createCategory(data) {
    const response = await api.post("/admin/categories", data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  /**
   * Update category
   */
  async updateCategory(id, data) {
    const response = await api.put(`/admin/categories/${id}`, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return response.data;
  },

  /**
   * Delete category
   */
  async deleteCategory(id) {
    const response = await api.delete(`/admin/categories/${id}`);
    return response.data;
  },

  // ==================== CUSTOMERS ====================

  /**
   * Get all customers
   */
  async getCustomers(params = {}) {
    const response = await api.get("/admin/customers", { params });
    return response.data;
  },

  /**
   * Get customer details
   */
  async getCustomer(id) {
    const response = await api.get(`/admin/customers/${id}`);
    return response.data;
  },

  /**
   * Update customer
   */
  async updateCustomer(id, data) {
    const response = await api.put(`/admin/customers/${id}`, data);
    return response.data;
  },

  // ==================== STAFF ====================

  /**
   * Get all staff members
   */
  async getStaff(params = {}) {
    const response = await api.get("/admin/staff", { params });
    return response.data;
  },

  /**
   * Create staff account
   */
  async createStaff(data) {
    const response = await api.post("/admin/staff", data);
    return response.data;
  },

  /**
   * Update staff account
   */
  async updateStaff(id, data) {
    const response = await api.put(`/admin/staff/${id}`, data);
    return response.data;
  },

  /**
   * Delete staff account
   */
  async deleteStaff(id) {
    const response = await api.delete(`/admin/staff/${id}`);
    return response.data;
  },

  /**
   * Get staff activity
   */
  async getStaffActivity(id) {
    const response = await api.get(`/admin/staff/${id}/activity`);
    return response.data;
  },

  // ==================== PROMOTIONS ====================

  /**
   * Get all promotions
   */
  async getPromotions(params = {}) {
    const response = await api.get("/admin/promotions", { params });
    return response.data;
  },

  /**
   * Create promotion
   */
  async createPromotion(data) {
    const response = await api.post("/admin/promotions", data);
    return response.data;
  },

  /**
   * Update promotion
   */
  async updatePromotion(id, data) {
    const response = await api.put(`/admin/promotions/${id}`, data);
    return response.data;
  },

  /**
   * Delete promotion
   */
  async deletePromotion(id) {
    const response = await api.delete(`/admin/promotions/${id}`);
    return response.data;
  },

  // ==================== ACTIVITY LOGS ====================

  /**
   * Get activity logs
   */
  async getActivityLogs(params = {}) {
    const response = await api.get("/admin/logs", { params });
    return response.data;
  },

  /**
   * Bulk delete activity logs
   */
  async bulkDeleteActivityLogs(data) {
    const response = await api.delete("/admin/logs/bulk", { data });
    return response.data;
  },

  // ==================== USERS ====================

  /**
   * Get all users
   */
  async getUsers(params = {}) {
    const response = await api.get("/admin/users", { params });
    return response.data;
  },

  /**
   * Create new user
   */
  async createUser(data) {
    const response = await api.post("/admin/users", data);
    return response.data;
  },

  /**
   * Update user
   */
  async updateUser(id, data) {
    const response = await api.put(`/admin/users/${id}`, data);
    return response.data;
  },

  /**
   * Delete user
   */
  async deleteUser(id) {
    const response = await api.delete(`/admin/users/${id}`);
    return response.data;
  },

  // ==================== INVENTORY ====================

  /**
   * Get inventory data
   */
  async getInventory(params = {}) {
    const response = await api.get("/admin/inventory", { params });
    return response.data;
  },

  /**
   * Update inventory
   */
  async updateInventory(productId, data) {
    const response = await api.patch(`/admin/inventory/${productId}`, data);
    return response.data;
  },

  /**
   * Get inventory logs
   */
  async getInventoryLogs(params = {}) {
    const response = await api.get("/admin/inventory/logs", { params });
    return response.data;
  },

  // ==================== REPORTS ====================

  /**
   * Get reports
   */
  async getReports(type, params = {}) {
    const response = await api.get(`/admin/reports/${type}`, { params });
    return response.data;
  },

  /**
   * Get sales report
   */
  async getSalesReport(params = {}) {
    return this.getReports("sales", params);
  },

  /**
   * Get revenue report
   */
  async getRevenueReport(params = {}) {
    return this.getReports("revenue", params);
  },

  /**
   * Get products report
   */
  async getProductsReport(params = {}) {
    return this.getReports("products", params);
  },

  /**
   * Get customers report
   */
  async getCustomersReport(params = {}) {
    return this.getReports("customers", params);
  },

  // ==================== SETTINGS ====================

  /**
   * Get settings
   */
  async getSettings() {
    const response = await api.get("/admin/settings");
    return response.data;
  },

  /**
   * Update settings
   */
  async updateSettings(data) {
    const response = await api.put("/admin/settings", data);
    return response.data;
  },
};

export default {
  customer: customerDashboard,
  staff: staffDashboard,
  admin: adminDashboard,
};
