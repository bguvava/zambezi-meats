# Admin Dashboard Module

## Overview

The Admin Dashboard Module provides comprehensive administrative functionality for managing the Zambezi Meats e-commerce platform. This module is accessible only to users with the `admin` role and provides full CRUD operations for all major entities.

## Requirements Coverage

| Requirement | Description                      | Status      |
| ----------- | -------------------------------- | ----------- |
| ADMIN-001   | Admin Dashboard with KPI widgets | ✅ Complete |
| ADMIN-002   | Revenue charts and analytics     | ✅ Complete |
| ADMIN-003   | Low stock alerts                 | ✅ Complete |
| ADMIN-004   | Recent orders widget             | ✅ Complete |
| ADMIN-005   | Order management page            | ✅ Complete |
| ADMIN-006   | Order filtering and search       | ✅ Complete |
| ADMIN-007   | Order status updates             | ✅ Complete |
| ADMIN-008   | Order assignment to staff        | ✅ Complete |
| ADMIN-009   | Refund processing                | ✅ Complete |
| ADMIN-010   | Product management page          | ✅ Complete |
| ADMIN-011   | Product CRUD operations          | ✅ Complete |
| ADMIN-012   | Product image upload             | ✅ Complete |
| ADMIN-013   | Category management page         | ✅ Complete |
| ADMIN-014   | Category CRUD operations         | ✅ Complete |
| ADMIN-015   | Category hierarchy support       | ✅ Complete |
| ADMIN-016   | User/Customer management         | ✅ Complete |
| ADMIN-017   | Customer search and filtering    | ✅ Complete |
| ADMIN-018   | Staff management page            | ✅ Complete |
| ADMIN-019   | Create/edit staff accounts       | ✅ Complete |
| ADMIN-020   | Activate/deactivate staff        | ✅ Complete |
| ADMIN-021   | View staff activity              | ✅ Complete |
| ADMIN-022   | Promotions management            | ✅ Complete |
| ADMIN-023   | Promotion CRUD operations        | ✅ Complete |
| ADMIN-024   | Activity logs with bulk delete   | ✅ Complete |
| ADMIN-025   | Activity log filtering           | ✅ Complete |
| ADMIN-026   | Inventory management page        | ✅ Complete |
| ADMIN-027   | Reports & analytics page         | ✅ Complete |
| ADMIN-028   | System settings page             | ✅ Complete |

## Architecture

### Backend (Laravel)

**Controller:** `app/Http/Controllers/Api/V1/AdminController.php` (2084 lines)

Key endpoints:

- `GET /api/v1/admin/dashboard` - Dashboard statistics and KPIs
- `GET/POST/PUT/DELETE /api/v1/admin/orders` - Order management
- `GET/POST/PUT/DELETE /api/v1/admin/products` - Product management
- `GET/POST/PUT/DELETE /api/v1/admin/categories` - Category management
- `GET/PUT /api/v1/admin/customers` - Customer management
- `GET/POST/PUT/DELETE /api/v1/admin/staff` - Staff management
- `GET/POST/PUT/DELETE /api/v1/admin/promotions` - Promotions
- `GET/DELETE /api/v1/admin/logs` - Activity logs

**Authorization:** All endpoints require `role:admin` middleware.

### Frontend (Vue.js 3)

#### Pinia Stores

Located in `frontend/src/stores/`:

| Store            | File                 | Tests    |
| ---------------- | -------------------- | -------- |
| Admin Customers  | `adminCustomers.js`  | 22 tests |
| Admin Categories | `adminCategories.js` | 16 tests |
| Admin Orders     | `adminOrders.js`     | 26 tests |
| Admin Products   | `adminProducts.js`   | 26 tests |
| Admin Staff      | `adminStaff.js`      | 26 tests |
| Admin Promotions | `adminPromotions.js` | 23 tests |
| Admin Logs       | `adminLogs.js`       | 36 tests |

**Total Frontend Tests:** 175 tests passing

#### Vue Pages

Located in `frontend/src/pages/admin/`:

| Page                 | Description          | Key Features                                |
| -------------------- | -------------------- | ------------------------------------------- |
| DashboardPage.vue    | Main admin dashboard | KPIs, charts, widgets, low stock alerts     |
| OrdersPage.vue       | Order management     | Status tabs, filtering, assignment, refunds |
| ProductsPage.vue     | Product catalog      | CRUD, image upload, category filtering      |
| CategoriesPage.vue   | Category management  | Hierarchy, parent/subcategory support       |
| UsersPage.vue        | User management      | Uses UsersIndex component                   |
| StaffPage.vue        | Staff management     | Create/edit accounts, view activity         |
| PromotionsPage.vue   | Promotions           | Discount codes, percentage/fixed types      |
| ActivityLogsPage.vue | Activity logs        | Bulk delete, filtering by action/user       |
| InventoryPage.vue    | Inventory            | Stock levels, alerts                        |
| ReportsPage.vue      | Reports              | Analytics and exports                       |
| SettingsPage.vue     | Settings             | System configuration                        |

#### Routes

Defined in `frontend/src/router/index.js`:

```javascript
{
  path: "/admin",
  component: AdminLayout,
  meta: { requiresAuth: true, role: "admin" },
  children: [
    { path: "", name: "admin-dashboard" },
    { path: "users", name: "admin-users" },
    { path: "products", name: "admin-products" },
    { path: "categories", name: "admin-categories" },
    { path: "orders", name: "admin-orders" },
    { path: "inventory", name: "admin-inventory" },
    { path: "promotions", name: "admin-promotions" },
    { path: "staff", name: "admin-staff" },
    { path: "activity-logs", name: "admin-activity-logs" },
    { path: "reports", name: "admin-reports" },
    { path: "settings", name: "admin-settings" }
  ]
}
```

## API Service

Located in `frontend/src/services/dashboard.js`:

```javascript
export const adminDashboard = {
  // Dashboard
  getDashboard(),

  // Orders
  getOrders(params),
  getOrder(id),
  updateOrder(id, data),
  updateOrderStatus(id, status, notes),
  assignOrder(id, staffId),
  refundOrder(id, data),

  // Products
  getProducts(params),
  createProduct(data),
  updateProduct(id, data),
  deleteProduct(id),

  // Categories
  getCategories(params),
  createCategory(data),
  updateCategory(id, data),
  deleteCategory(id),

  // Customers
  getCustomers(params),
  getCustomer(id),
  updateCustomer(id, data),

  // Staff
  getStaff(params),
  createStaff(data),
  updateStaff(id, data),
  deleteStaff(id),
  getStaffActivity(id),

  // Promotions
  getPromotions(params),
  createPromotion(data),
  updatePromotion(id, data),
  deletePromotion(id),

  // Logs
  getActivityLogs(params),
  bulkDeleteActivityLogs(data)
}
```

## UI Design

### Color Scheme

- Primary: `#CF0D0F` (Zambezi Red)
- Secondary: `#F6211F` (Lighter Red)
- Dark Gray: `#4D4B4C`
- Gray: `#6F6F6F`
- Light Gray: `#EFEFEF`

### Design Patterns

- Border highlight: `border-2 border-[#CF0D0F]` for key sections
- Modal overlay: Teleport to body with `z-50` and `z-60` for nested modals
- Loading states: Spinning loader with brand colors
- Error states: Red alert boxes with retry buttons
- Pagination: Chevron buttons with page indicators

## Testing

### Backend Tests

**File:** `backend/tests/Feature/Api/V1/AdminControllerTest.php`
**Tests:** 50 passing (170 assertions)

Test categories:

- Authorization (admin-only access)
- Dashboard statistics
- Order CRUD and workflows
- Product CRUD
- Category CRUD with hierarchy
- Customer management
- Staff management with activity tracking
- Promotions management
- Activity logs with bulk operations

### Frontend Tests

**Directory:** `frontend/src/tests/admin/`
**Tests:** 175 passing

Test files:

- `adminCustomersStore.spec.js` (22 tests)
- `adminCategoriesStore.spec.js` (16 tests)
- `adminOrdersStore.spec.js` (26 tests)
- `adminProductsStore.spec.js` (26 tests)
- `adminStaffStore.spec.js` (26 tests)
- `adminPromotionsStore.spec.js` (23 tests)
- `adminLogsStore.spec.js` (36 tests)

## Running Tests

### Backend

```bash
cd backend
php artisan test --filter=AdminControllerTest
```

### Frontend

```bash
cd frontend
npm test -- --run src/tests/admin/
```

## Bulk Operations

Per project requirements, bulk delete is only supported for Activity Logs (ADMIN-024). All other entities require individual deletion to prevent accidental data loss.

## Security Considerations

1. All admin routes require authenticated admin role
2. CSRF protection via Laravel Sanctum
3. Input validation on all endpoints
4. Activity logging for audit trails
5. Session timeout handling
6. Admin cannot delete their own account

## Dependencies

### Backend

- Laravel 11
- Laravel Sanctum (authentication)
- PHPUnit (testing)

### Frontend

- Vue.js 3 (Composition API)
- Pinia (state management)
- Vue Router (navigation)
- Lucide Vue (icons)
- Vitest (testing)
- Tailwind CSS (styling)
