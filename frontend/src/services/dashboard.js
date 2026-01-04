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

  // ==================== INVOICES (READ-ONLY) ====================

  /**
   * Get all invoices with filters and pagination (read-only)
   */
  async getInvoices(params = {}) {
    const response = await api.get("/staff/invoices", { params });
    return response.data;
  },

  /**
   * Get invoice details (read-only)
   */
  async getInvoice(id) {
    const response = await api.get(`/staff/invoices/${id}`);
    return response.data;
  },

  /**
   * Get invoice statistics (read-only)
   */
  async getInvoiceStats() {
    const response = await api.get("/staff/invoices/stats");
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
   * Get inventory logs/history
   */
  async getInventoryLogs(params = {}) {
    const response = await api.get("/admin/inventory/history", { params });
    return response.data;
  },

  /**
   * Get inventory dashboard overview
   * @requirement INV-001 Create inventory dashboard
   */
  async getInventoryDashboard() {
    const response = await api.get("/admin/inventory/dashboard");
    return response.data;
  },

  /**
   * Get product inventory detail
   * @requirement INV-012 View product inventory detail
   */
  async getProductInventory(productId) {
    const response = await api.get(`/admin/inventory/${productId}`);
    return response.data;
  },

  /**
   * Receive stock (add incoming stock)
   * @requirement INV-004 Create stock receive form
   */
  async receiveStock(data) {
    const response = await api.post("/admin/inventory/receive", data);
    return response.data;
  },

  /**
   * Adjust stock
   * @requirement INV-005 Create stock adjustment form
   */
  async adjustStock(productId, newQuantity, reason, notes = null) {
    const response = await api.post("/admin/inventory/adjust", {
      product_id: productId,
      new_quantity: newQuantity,
      reason,
      notes,
    });
    return response.data;
  },

  /**
   * Update minimum stock threshold
   * @requirement INV-008 Set minimum stock thresholds
   */
  async updateMinStock(productId, minStock) {
    const response = await api.put(`/admin/inventory/${productId}/min-stock`, {
      min_stock: minStock,
    });
    return response.data;
  },

  /**
   * Get low stock items
   * @requirement INV-009 Create low stock alerts
   */
  async getLowStockItems() {
    const response = await api.get("/admin/inventory/low-stock");
    return response.data;
  },

  /**
   * Get inventory alerts
   * @requirement INV-009, INV-010 Low stock and out of stock alerts
   */
  async getInventoryAlerts() {
    const response = await api.get("/admin/inventory/alerts");
    return response.data;
  },

  /**
   * Get waste entries
   * @requirement INV-013 Create waste management section
   */
  async getWasteEntries(params = {}) {
    const response = await api.get("/admin/waste", { params });
    return response.data;
  },

  /**
   * Approve or reject waste entry
   * @requirement INV-014 Review and approve waste logs
   */
  async approveWaste(wasteId, approved, notes = null) {
    const response = await api.put(`/admin/waste/${wasteId}/approve`, {
      approved,
      notes,
    });
    return response.data;
  },

  /**
   * Get inventory report
   * @requirement INV-015 Generate inventory report
   */
  async getInventoryReport(params = {}) {
    const response = await api.get("/admin/inventory/report", { params });
    return response.data;
  },

  /**
   * Export inventory data
   * @requirement INV-016 Export inventory data
   */
  async exportInventory(params = {}) {
    const response = await api.get("/admin/inventory/export", { params });
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

  /**
   * Get reports dashboard with quick stats
   * @requirement RPT-001 Create reports dashboard
   */
  async getReportsDashboard(params = {}) {
    const response = await api.get("/admin/reports/dashboard", { params });
    return response.data;
  },

  /**
   * Get sales summary report
   * @requirement RPT-002 Create sales summary report
   */
  async getSalesSummaryReport(params = {}) {
    const response = await api.get("/admin/reports/sales-summary", { params });
    return response.data;
  },

  /**
   * Get revenue by period report
   * @requirement RPT-003 Create revenue by period report
   */
  async getRevenueByPeriodReport(params = {}) {
    const response = await api.get("/admin/reports/revenue", { params });
    return response.data;
  },

  /**
   * Get orders by status report
   * @requirement RPT-004 Create orders by status report
   */
  async getOrdersReport(params = {}) {
    const response = await api.get("/admin/reports/orders", { params });
    return response.data;
  },

  /**
   * Get product sales report
   * @requirement RPT-005 Create product sales report
   */
  async getProductSalesReport(params = {}) {
    const response = await api.get("/admin/reports/products", { params });
    return response.data;
  },

  /**
   * Get category sales report
   * @requirement RPT-006 Create category sales report
   */
  async getCategorySalesReport(params = {}) {
    const response = await api.get("/admin/reports/categories", { params });
    return response.data;
  },

  /**
   * Get top products report
   * @requirement RPT-007 Create top products report
   */
  async getTopProductsReport(params = {}) {
    const response = await api.get("/admin/reports/top-products", { params });
    return response.data;
  },

  /**
   * Get low performing products report
   * @requirement RPT-008 Create low performing products
   */
  async getLowPerformingProductsReport(params = {}) {
    const response = await api.get("/admin/reports/low-performing", { params });
    return response.data;
  },

  /**
   * Get customer analytics report
   * @requirement RPT-009 Create customer report
   */
  async getCustomerAnalyticsReport(params = {}) {
    const response = await api.get("/admin/reports/customers", { params });
    return response.data;
  },

  /**
   * Get customer acquisition report
   * @requirement RPT-010 Create customer acquisition report
   */
  async getCustomerAcquisitionReport(params = {}) {
    const response = await api.get("/admin/reports/customer-acquisition", {
      params,
    });
    return response.data;
  },

  /**
   * Get staff performance report
   * @requirement RPT-011 Create staff performance report
   */
  async getStaffPerformanceReport(params = {}) {
    const response = await api.get("/admin/reports/staff", { params });
    return response.data;
  },

  /**
   * Get delivery performance report
   * @requirement RPT-012 Create delivery performance report
   */
  async getDeliveryPerformanceReport(params = {}) {
    const response = await api.get("/admin/reports/deliveries", { params });
    return response.data;
  },

  /**
   * Get inventory report
   * @requirement RPT-013 Create inventory report
   */
  async getInventoryReport(params = {}) {
    const response = await api.get("/admin/reports/inventory", { params });
    return response.data;
  },

  /**
   * Get financial summary report
   * @requirement RPT-014 Create financial summary report
   */
  async getFinancialSummaryReport(params = {}) {
    const response = await api.get("/admin/reports/financial", { params });
    return response.data;
  },

  /**
   * Get payment methods report
   * @requirement RPT-015 Create payment methods report
   */
  async getPaymentMethodsReport(params = {}) {
    const response = await api.get("/admin/reports/payment-methods", {
      params,
    });
    return response.data;
  },

  /**
   * Export report as PDF
   * @requirement RPT-018 Export report to PDF (View)
   * @requirement RPT-019 Export report to PDF (Download)
   */
  async exportReport(type, params = {}) {
    const response = await api.get(`/admin/reports/${type}/export`, {
      params,
      responseType: "blob", // Important for PDF downloads
    });
    return response;
  },

  // ==================== DELIVERIES ====================

  /**
   * Get delivery dashboard overview
   * @requirement DEL-001 Create delivery dashboard
   */
  async getDeliveryDashboard() {
    const response = await api.get("/admin/deliveries/dashboard");
    return response.data;
  },

  /**
   * Get all deliveries with filtering
   * @requirement DEL-002 Display all deliveries list
   */
  async getDeliveries(params = {}) {
    const response = await api.get("/admin/deliveries", { params });
    return response.data;
  },

  /**
   * Get single delivery details
   * @requirement DEL-004 View delivery detail
   */
  async getDelivery(id) {
    const response = await api.get(`/admin/deliveries/${id}`);
    return response.data;
  },

  /**
   * Assign delivery to staff member
   * @requirement DEL-005 Assign delivery to staff
   */
  async assignDelivery(id, data) {
    const response = await api.put(`/admin/deliveries/${id}/assign`, data);
    return response.data;
  },

  /**
   * Get proof of delivery
   * @requirement DEL-007 View proof of delivery
   */
  async getDeliveryPod(id) {
    const response = await api.get(`/admin/deliveries/${id}/pod`);
    return response.data;
  },

  /**
   * Download POD as PDF
   * @requirement DEL-008 Download POD document
   */
  async downloadDeliveryPod(id) {
    const response = await api.get(`/admin/deliveries/${id}/pod/download`, {
      responseType: "blob",
    });
    return response.data;
  },

  /**
   * Resolve delivery issue
   * @requirement DEL-009 Handle delivery issues
   */
  async resolveDeliveryIssue(id, data) {
    const response = await api.put(`/admin/deliveries/${id}/issue`, data);
    return response.data;
  },

  /**
   * Get all delivery zones
   * @requirement DEL-010 Create delivery zones management
   */
  async getDeliveryZones(params = {}) {
    const response = await api.get("/admin/delivery-zones", { params });
    return response.data;
  },

  /**
   * Create new delivery zone
   * @requirement DEL-011 Add delivery zone
   */
  async createDeliveryZone(data) {
    const response = await api.post("/admin/delivery-zones", data);
    return response.data;
  },

  /**
   * Update delivery zone
   * @requirement DEL-012 Edit delivery zone
   */
  async updateDeliveryZone(id, data) {
    const response = await api.put(`/admin/delivery-zones/${id}`, data);
    return response.data;
  },

  /**
   * Delete delivery zone
   * @requirement DEL-013 Delete delivery zone
   */
  async deleteDeliveryZone(id) {
    const response = await api.delete(`/admin/delivery-zones/${id}`);
    return response.data;
  },

  /**
   * Get delivery settings
   * @requirement DEL-014 Configure delivery fees
   */
  async getDeliverySettings() {
    const response = await api.get("/admin/delivery-settings");
    return response.data;
  },

  /**
   * Update delivery settings
   * @requirement DEL-014 Configure delivery fees
   */
  async updateDeliverySettings(data) {
    const response = await api.put("/admin/delivery-settings", data);
    return response.data;
  },

  /**
   * Get delivery map data
   * @requirement DEL-015 View delivery map
   */
  async getDeliveryMapData(params = {}) {
    const response = await api.get("/admin/deliveries/map", { params });
    return response.data;
  },

  /**
   * Get delivery report
   * @requirement DEL-016 Generate delivery report
   */
  async getDeliveryReport(params = {}) {
    const response = await api.get("/admin/deliveries/report", { params });
    return response.data;
  },

  /**
   * Export deliveries data
   * @requirement DEL-017 Export delivery data
   */
  async exportDeliveries(params = {}) {
    const response = await api.get("/admin/deliveries/export", {
      params,
      responseType: params.format === "pdf" ? "blob" : "json",
    });
    return response.data;
  },

  // ==================== SETTINGS ====================

  /**
   * Get all settings
   * @requirement SET-001 Create settings dashboard
   */
  async getSettings() {
    const response = await api.get("/admin/settings");
    return response.data;
  },

  /**
   * Get settings by group
   * @requirement SET-001 Create settings dashboard
   */
  async getSettingsGroup(group) {
    const response = await api.get(`/admin/settings/group/${group}`);
    return response.data;
  },

  /**
   * Update settings group
   * @requirement SET-002 to SET-025 Configure various settings
   */
  async updateSettingsGroup(group, data) {
    const response = await api.put(`/admin/settings/group/${group}`, data);
    return response.data;
  },

  /**
   * Upload store logo
   * @requirement SET-003 Configure store logo
   */
  async uploadLogo(formData) {
    const response = await api.post("/admin/settings/logo", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return response.data;
  },

  /**
   * Get email templates
   * @requirement SET-014 Manage email templates
   */
  async getEmailTemplates() {
    const response = await api.get("/admin/settings/email-templates");
    return response.data;
  },

  /**
   * Update email template
   * @requirement SET-014 Manage email templates
   */
  async updateEmailTemplate(name, data) {
    const response = await api.put(
      `/admin/settings/email-templates/${name}`,
      data
    );
    return response.data;
  },

  /**
   * Send test email
   * @requirement SET-012 Configure SMTP settings
   */
  async sendTestEmail(email) {
    const response = await api.post("/admin/settings/email-test", { email });
    return response.data;
  },

  /**
   * Export settings
   * @requirement SET-026 Import/export settings
   */
  async exportSettings() {
    const response = await api.post("/admin/settings/export");
    return response.data;
  },

  /**
   * Import settings
   * @requirement SET-026 Import/export settings
   */
  async importSettings(settings) {
    const response = await api.post("/admin/settings/import", { settings });
    return response.data;
  },

  /**
   * Get settings change history
   * @requirement SET-027 View settings change history
   */
  async getSettingsHistory(params = {}) {
    const response = await api.get("/admin/settings/history", { params });
    return response.data;
  },

  /**
   * Get public settings (no auth required)
   * @requirement SET-001 Create settings dashboard
   */
  async getPublicSettings() {
    const response = await api.get("/settings/public");
    return response.data;
  },

  // ==================== INVOICES ====================

  /**
   * Get all invoices with filters and pagination
   */
  async getInvoices(params = {}) {
    const response = await api.get("/admin/invoices", { params });
    return response.data;
  },

  /**
   * Get invoice details
   */
  async getInvoice(id) {
    const response = await api.get(`/admin/invoices/${id}`);
    return response.data;
  },

  /**
   * Update invoice status
   */
  async updateInvoiceStatus(id, status) {
    const response = await api.put(`/admin/invoices/${id}/status`, { status });
    return response.data;
  },

  /**
   * Get invoice statistics
   */
  async getInvoiceStats() {
    const response = await api.get("/admin/invoices/stats");
    return response.data;
  },
};

export default {
  customer: customerDashboard,
  staff: staffDashboard,
  admin: adminDashboard,
};
