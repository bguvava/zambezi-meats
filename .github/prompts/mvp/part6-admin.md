# Zambezi Meats MVP - Part 6: Admin Dashboard

## Module Overview

| Field | Value |
|-------|-------|
| **Module Name** | ADMIN |
| **Priority** | P0 - Critical |
| **Dependencies** | All previous modules |
| **Documentation** | `/docs/admin/` |
| **Tests** | `/tests/admin/` |

**Total Requirements: 28**

---

## 6.1 Admin Dashboard Module

### Objectives

1. Create comprehensive admin dashboard for business management
2. Implement full CRUD for products, categories, users
3. Provide order lifecycle management with all actions
4. Display business analytics and KPIs
5. Manage system settings and configurations

### Success Criteria

| Criteria | Target |
|----------|--------|
| Dashboard load time | < 2 seconds |
| CRUD operations | All successful |
| Report generation | < 5 seconds |
| Real-time updates | SSE within 2 seconds |
| Test coverage | 100% |

### Bulk Operations Policy

> **Important:** Bulk operations are restricted to Activity Logs only.

| Module | Bulk Delete | Bulk Save | Bulk Export |
|--------|:-----------:|:---------:|:-----------:|
| Products | ❌ | ❌ | ❌ |
| Orders | ❌ | ❌ | ❌ |
| Customers | ❌ | ❌ | ❌ |
| Activity Logs | ✅ | ✅ | ❌ |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| ADMIN-001 | Create admin dashboard layout | As an admin, I want a comprehensive management interface | Sidebar: Overview, Orders, Products, Inventory, Customers, Staff, Deliveries, Finance, Reports, Promotions, Settings, Logs | Admin |
| ADMIN-002 | Create dashboard overview with KPIs | As an admin, I want to see business performance at a glance | Cards: Today's revenue, orders, new customers, pending orders + charts | Admin |
| ADMIN-003 | Display real-time order alerts | As an admin, I want immediate notification of new orders | SSE alerts with sound, badge count, quick action buttons | Admin |
| ADMIN-004 | Create revenue chart (7/30 days) | As an admin, I want to visualize revenue trends | Line/bar chart showing daily revenue, compare periods | Admin |
| ADMIN-005 | Create orders management page | As an admin, I want to manage all orders | Table: order#, customer, status, total, date, actions | Admin |
| ADMIN-006 | Implement order filtering | As an admin, I want to find specific orders | Filters: status, date range, customer (limited filters per policy) | Admin |
| ADMIN-007 | Create order detail view | As an admin, I want full order information | Complete order data with edit, status change, refund actions | Admin |
| ADMIN-008 | Implement order actions | As an admin, I want to manage order lifecycle | Actions: Accept, Reject, Assign Staff, Edit, Cancel, Refund | Admin |
| ADMIN-009 | Assign orders to staff | As an admin, I want to delegate order processing | Dropdown to select staff member, notification sent | Admin |
| ADMIN-010 | Process refunds | As an admin, I want to refund orders | Refund modal: full/partial amount, reason, confirm | Admin |
| ADMIN-011 | Create products management page | As an admin, I want to manage product catalog | Table: image, name, category, price, stock, status, actions | Admin |
| ADMIN-012 | Create add/edit product form | As an admin, I want to create and update products | Modal form: name, category, price, description, images, stock, status | Admin |
| ADMIN-013 | Implement product image upload | As an admin, I want to add product photos | Multi-image upload with drag-drop, reorder, delete | Admin |
| ADMIN-014 | Create categories management | As an admin, I want to organize products | CRUD for categories: name, slug, image, parent, sort order | Admin |
| ADMIN-015 | Delete products (single) | As an admin, I want to remove products | Delete with confirmation, soft delete to preserve order history | Admin |
| ADMIN-016 | Create customers management page | As an admin, I want to manage customer accounts | Table: name, email, phone, orders count, total spent, actions | Admin |
| ADMIN-017 | View customer details | As an admin, I want to see customer information | Profile, order history, addresses, support tickets | Admin |
| ADMIN-018 | Create staff management page | As an admin, I want to manage staff accounts | Table: name, email, role, status, activity, actions | Admin |
| ADMIN-019 | Create/edit staff accounts | As an admin, I want to add staff members | Form: name, email, phone, password, role | Admin |
| ADMIN-020 | Activate/deactivate staff | As an admin, I want to control staff access | Toggle active status, deactivated staff cannot login | Admin |
| ADMIN-021 | View staff activity | As an admin, I want to monitor staff performance | Activity log: orders processed, deliveries, login times | Admin |
| ADMIN-022 | Create promotions management | As an admin, I want to create discounts | CRUD: promo code, type (% or $), value, min order, dates, status | Admin |
| ADMIN-023 | Create audit/activity logs page | As an admin, I want to see system activity | Log table: user, action, details, IP, timestamp | Admin |
| ADMIN-024 | Implement bulk delete for activity logs | As an admin, I want to clean old logs | Select multiple logs, bulk delete with date range option | Admin |
| ADMIN-025 | Create admin API endpoints | As a developer, I need admin CRUD APIs | Full CRUD endpoints for products, categories, users, orders, promotions | Developer |
| ADMIN-026 | Implement admin middleware | As a developer, I need admin-only route protection | Middleware verifies admin role, returns 403 for non-admins | Developer |
| ADMIN-027 | Create admin Pinia stores | As a developer, I need state management | Stores for orders, products, customers, staff, promotions | Developer |
| ADMIN-028 | Write admin module tests | As a developer, I need 100% test coverage | Unit, integration, E2E tests for all admin features | Developer |

### Admin API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/admin/dashboard` | Get dashboard stats | Yes (Admin) |
| GET | `/api/v1/admin/orders` | List all orders | Yes (Admin) |
| GET | `/api/v1/admin/orders/{id}` | Get order details | Yes (Admin) |
| PUT | `/api/v1/admin/orders/{id}` | Update order | Yes (Admin) |
| PUT | `/api/v1/admin/orders/{id}/status` | Change order status | Yes (Admin) |
| PUT | `/api/v1/admin/orders/{id}/assign` | Assign to staff | Yes (Admin) |
| POST | `/api/v1/admin/orders/{id}/refund` | Process refund | Yes (Admin) |
| GET | `/api/v1/admin/products` | List products | Yes (Admin) |
| POST | `/api/v1/admin/products` | Create product | Yes (Admin) |
| PUT | `/api/v1/admin/products/{id}` | Update product | Yes (Admin) |
| DELETE | `/api/v1/admin/products/{id}` | Delete product | Yes (Admin) |
| GET | `/api/v1/admin/categories` | List categories | Yes (Admin) |
| POST | `/api/v1/admin/categories` | Create category | Yes (Admin) |
| PUT | `/api/v1/admin/categories/{id}` | Update category | Yes (Admin) |
| DELETE | `/api/v1/admin/categories/{id}` | Delete category | Yes (Admin) |
| GET | `/api/v1/admin/customers` | List customers | Yes (Admin) |
| GET | `/api/v1/admin/customers/{id}` | Get customer details | Yes (Admin) |
| PUT | `/api/v1/admin/customers/{id}` | Update customer | Yes (Admin) |
| GET | `/api/v1/admin/staff` | List staff | Yes (Admin) |
| POST | `/api/v1/admin/staff` | Create staff account | Yes (Admin) |
| PUT | `/api/v1/admin/staff/{id}` | Update staff | Yes (Admin) |
| DELETE | `/api/v1/admin/staff/{id}` | Delete staff | Yes (Admin) |
| GET | `/api/v1/admin/promotions` | List promotions | Yes (Admin) |
| POST | `/api/v1/admin/promotions` | Create promotion | Yes (Admin) |
| PUT | `/api/v1/admin/promotions/{id}` | Update promotion | Yes (Admin) |
| DELETE | `/api/v1/admin/promotions/{id}` | Delete promotion | Yes (Admin) |
| GET | `/api/v1/admin/activity-logs` | List activity logs | Yes (Admin) |
| DELETE | `/api/v1/admin/activity-logs/bulk` | Bulk delete logs | Yes (Admin) |

### Admin Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  ADMIN DASHBOARD                                     🔔(5)  👤 Admin ▼   [Logout]   │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  ADMIN MENU    │  DASHBOARD OVERVIEW                              Today: Dec 12     │
│                │                                                                    │
│  ┌──────────┐  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ Overview │  │  │ 💰 Revenue  │ │ 📦 Orders   │ │ 👥 New      │ │ ⏳ Pending  │  │
│  ├──────────┤  │  │  $2,450.00  │ │     18      │ │ Customers 3 │ │      5      │  │
│  │ Orders   │  │  │   ▲ 12%    │ │    ▲ 8%    │ │    ▲ 2     │ │    ▼ 2     │  │
│  ├──────────┤  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  │ Products │  │                                                                    │
│  ├──────────┤  │  REVENUE CHART (Last 7 Days)                                       │
│  │ Inventory│  │  ┌────────────────────────────────────────────────────────────┐   │
│  ├──────────┤  │  │     $3k ┤                                    ╭─╮            │   │
│  │ Customers│  │  │         │                              ╭─╮  │ │            │   │
│  ├──────────┤  │  │     $2k ┤              ╭─╮   ╭─╮      │ │  │ │  ╭─╮       │   │
│  │ Staff    │  │  │         │        ╭─╮  │ │  │ │  ╭─╮  │ │  │ │  │ │       │   │
│  ├──────────┤  │  │     $1k ┤  ╭─╮  │ │  │ │  │ │  │ │  │ │  │ │  │ │       │   │
│  │Deliveries│  │  │         │  │ │  │ │  │ │  │ │  │ │  │ │  │ │  │ │       │   │
│  ├──────────┤  │  │       0 ┼──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴────────│   │
│  │ Finance  │  │  │          Mon   Tue   Wed   Thu   Fri   Sat   Sun         │   │
│  ├──────────┤  │  └────────────────────────────────────────────────────────────┘   │
│  │ Reports  │  │                                                                    │
│  ├──────────┤  │  RECENT ORDERS                                     [View All →]   │
│  │Promotions│  │  ┌────────────────────────────────────────────────────────────┐   │
│  ├──────────┤  │  │ #ZM-0024 │ John S.  │ $156.99 │ 🟡 Pending  │ [View][✓][✗]│   │
│  │ Settings │  │  │ #ZM-0023 │ Mary K.  │ $89.50  │ 🟢 Preparing│ [View]      │   │
│  ├──────────┤  │  │ #ZM-0022 │ Peter L. │ $234.00 │ 🔵 Delivery │ [View]      │   │
│  │ Logs     │  │  └────────────────────────────────────────────────────────────┘   │
│  └──────────┘  │                                                                    │
│                │  LOW STOCK ALERTS                    TOP PRODUCTS TODAY            │
│  ───────────── │  ┌───────────────────────┐          ┌───────────────────────┐     │
│  Developed     │  │ ⚠️ Ribeye (5 left)    │          │ 1. Chicken Breast (8) │     │
│  with ❤️ by    │  │ ⚠️ Lamb Chops (3)     │          │ 2. Ribeye Steak (6)   │     │
│  bguvava       │  │ ⚠️ Pork Ribs (2)      │          │ 3. Beef Mince (5)     │     │
│                │  └───────────────────────┘          └───────────────────────┘     │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

### Product Management Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  PRODUCTS MANAGEMENT                                                [+ Add Product] │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  🔍 Search products...                    Category: [All ▼]    Status: [All ▼]     │
│                                                                                     │
│  ┌──────────────────────────────────────────────────────────────────────────────┐  │
│  │ ☐ │ Image │ Product Name     │ Category │ Price/kg │ Stock │ Status │Actions│  │
│  ├──────────────────────────────────────────────────────────────────────────────┤  │
│  │ ☐ │ [IMG] │ Ribeye Steak     │ Beef     │ $45.99   │  5 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Chicken Breast   │ Chicken  │ $18.99   │  45   │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Lamb Chops       │ Lamb     │ $32.99   │  3 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Pork Ribs        │ Pork     │ $24.99   │  2 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Beef Mince       │ Beef     │ $16.99   │  30   │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ T-Bone Steak     │ Beef     │ $38.99   │  0    │Inactive│ ✏️ 🗑️ │  │
│  └──────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                     │
│  Showing 1-6 of 45 products                              ◀  1  2  3  ...  8  ▶     │
│                                                                                     │
│  Note: Bulk operations not available for products. Delete items individually.       │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 6 Summary

| Section | Requirements | IDs |
|---------|--------------|-----|
| Admin Dashboard | 28 | ADMIN-001 to ADMIN-028 |
| **Total** | **28** | |

---

**Previous:** [Part 5: Customer & Staff Dashboards](part5-customer-staff.md)

**Next:** [Part 7: Inventory & Delivery Management](part7-inventory-delivery.md)
