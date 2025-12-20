# Zambezi Meats MVP - Part 5: Customer & Staff Dashboards

## Module Overview

| Field | Value |
|-------|-------|
| **Module Names** | CUSTOMER, STAFF |
| **Priority** | P1 - High |
| **Dependencies** | AUTH, CHECKOUT |
| **Documentation** | `/docs/customer/`, `/docs/staff/` |
| **Tests** | `/tests/customer/`, `/tests/staff/` |

This module combines:
- Customer Dashboard (CUSTOMER)
- Staff Dashboard (STAFF)

**Total Requirements: 47**

---

## 5.1 Customer Dashboard

### Objectives

1. Create personalized customer dashboard
2. Display order history with status tracking
3. Manage delivery addresses and preferences
4. Implement wishlist functionality
5. Provide reorder capability for past purchases
6. Handle support ticket submission

### Success Criteria

| Criteria | Target |
|----------|--------|
| Dashboard load time | < 1 second |
| Order history pagination | 10 per page |
| Address management | CRUD operations |
| Wishlist sync | Real-time |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| CUST-001 | Create customer dashboard layout | As a customer, I want a personal dashboard | Sidebar navigation with Overview, Orders, Profile, Addresses, Wishlist, Support | Customer |
| CUST-002 | Create dashboard overview page | As a customer, I want a summary view | Quick stats: recent orders, pending deliveries, wishlist count, quick actions | Customer |
| CUST-003 | Create order history page | As a customer, I want to see all my orders | List of orders with: order number, date, status, total, view details link | Customer |
| CUST-004 | Create order detail view | As a customer, I want to see order details | Full order info: items, totals, delivery address, status timeline, tracking | Customer |
| CUST-005 | Implement order status timeline | As a customer, I want to track order progress | Visual timeline: Pending → Accepted → Preparing → Out for Delivery → Delivered | Customer |
| CUST-006 | Create "Reorder" functionality | As a customer, I want to quickly reorder | "Reorder" button adds all items from past order to cart | Customer |
| CUST-007 | Implement order filtering | As a customer, I want to filter my orders | Filter by: status, date range | Customer |
| CUST-008 | Create profile management page | As a customer, I want to update my info | Edit: name, email, phone, password, currency preference | Customer |
| CUST-009 | Implement password change | As a customer, I want to change my password | Current password verification, new password with confirmation | Customer |
| CUST-010 | Create address management page | As a customer, I want to manage addresses | List of saved addresses with add, edit, delete, set default | Customer |
| CUST-011 | Create add/edit address modal | As a customer, I want to add new addresses | Form modal for address details with autocomplete | Customer |
| CUST-012 | Create wishlist page | As a customer, I want to view saved products | Grid of wishlist items with "Add to Cart" and "Remove" options | Customer |
| CUST-013 | Implement wishlist to cart | As a customer, I want to buy wishlist items | "Add to Cart" button moves item to cart with quantity selector | Customer |
| CUST-014 | Create notifications page | As a customer, I want to see my notifications | List of notifications: order updates, promotions, sorted by date | Customer |
| CUST-015 | Implement notification preferences | As a customer, I want to control notifications | Toggle: email notifications, order updates, promotional emails | Customer |
| CUST-016 | Create support/help page | As a customer, I want to get help | FAQ section, contact info, ticket submission form | Customer |
| CUST-017 | Create support ticket submission | As a customer, I want to submit a support request | Form: subject, order (optional), message, attachment | Customer |
| CUST-018 | View support ticket history | As a customer, I want to see my tickets | List of submitted tickets with status and replies | Customer |
| CUST-019 | Implement currency preference | As a customer, I want to set my default currency | Dropdown to set AU$ or US$ as default, saved to profile | Customer |
| CUST-020 | Create order invoice download | As a customer, I want to download invoices | "Download Invoice" button generates PDF, opens in new tab | Customer |
| CUST-021 | Create customer API endpoints | As a developer, I need customer APIs | Endpoints for profile, addresses, orders, wishlist, tickets | Developer |
| CUST-022 | Create customer Pinia stores | As a developer, I need state management | Stores for profile, orders, addresses, wishlist, notifications | Developer |
| CUST-023 | Write customer module tests | As a developer, I need 100% test coverage | Unit, integration, E2E tests for all customer features | Developer |

### Customer API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/customer/profile` | Get customer profile | Yes |
| PUT | `/api/v1/customer/profile` | Update profile | Yes |
| PUT | `/api/v1/customer/password` | Change password | Yes |
| GET | `/api/v1/customer/orders` | List customer orders | Yes |
| GET | `/api/v1/customer/orders/{id}` | Get order details | Yes |
| POST | `/api/v1/customer/orders/{id}/reorder` | Reorder past order | Yes |
| GET | `/api/v1/customer/orders/{id}/invoice` | Download invoice PDF | Yes |
| GET | `/api/v1/customer/addresses` | List addresses | Yes |
| POST | `/api/v1/customer/addresses` | Add address | Yes |
| PUT | `/api/v1/customer/addresses/{id}` | Update address | Yes |
| DELETE | `/api/v1/customer/addresses/{id}` | Delete address | Yes |
| GET | `/api/v1/customer/wishlist` | Get wishlist | Yes |
| POST | `/api/v1/customer/wishlist` | Add to wishlist | Yes |
| DELETE | `/api/v1/customer/wishlist/{productId}` | Remove from wishlist | Yes |
| GET | `/api/v1/customer/notifications` | Get notifications | Yes |
| PUT | `/api/v1/customer/notifications/{id}/read` | Mark as read | Yes |
| GET | `/api/v1/customer/tickets` | List support tickets | Yes |
| POST | `/api/v1/customer/tickets` | Create ticket | Yes |
| GET | `/api/v1/customer/tickets/{id}` | Get ticket details | Yes |

### Customer Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER                                              AU$/US$  🔔(2)  👤 John ▼     │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  MY ACCOUNT    │  DASHBOARD OVERVIEW                                                │
│                │                                                                    │
│  ┌──────────┐  │  Welcome back, John!                                              │
│  │ Overview │  │                                                                    │
│  ├──────────┤  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ My Orders│  │  │  📦 Orders  │ │ 🚚 Pending  │ │ ❤️ Wishlist │ │ 🎫 Tickets  │  │
│  ├──────────┤  │  │     12      │ │      2      │ │      5      │ │      1      │  │
│  │ Profile  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  ├──────────┤  │                                                                    │
│  │ Addresses│  │  RECENT ORDERS                                      [View All →]  │
│  ├──────────┤  │  ┌────────────────────────────────────────────────────────────┐   │
│  │ Wishlist │  │  │ #ZM-20251210-0023  │  Dec 10, 2025  │  $156.99  │ Delivered │   │
│  ├──────────┤  │  │ #ZM-20251208-0019  │  Dec 8, 2025   │  $89.50   │ Preparing │   │
│  │ Support  │  │  │ #ZM-20251205-0015  │  Dec 5, 2025   │  $234.00  │ Delivered │   │
│  ├──────────┤  │  └────────────────────────────────────────────────────────────┘   │
│  │ Settings │  │                                                                    │
│  └──────────┘  │  QUICK ACTIONS                                                     │
│                │  ┌────────────────┐ ┌────────────────┐ ┌────────────────┐          │
│  ───────────── │  │  🛒 Shop Now   │ │ 📍 Addresses   │ │ 🔄 Reorder     │          │
│  [Logout]      │  └────────────────┘ └────────────────┘ └────────────────┘          │
│                │                                                                    │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

---

## 5.2 Staff Dashboard

### Objectives

1. Create unified staff interface for order processing and delivery
2. Display assigned orders queue with real-time updates
3. Enable order status updates throughout fulfillment process
4. Implement proof of delivery capture (signature/photo)
5. Provide delivery route optimization and navigation

### Success Criteria

| Criteria | Target |
|----------|--------|
| Dashboard load time | < 1 second |
| Real-time order updates | SSE within 2 seconds |
| POD capture success | 100% |
| Status update response | < 500ms |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| STAFF-001 | Create staff dashboard layout | As a staff member, I want a focused work interface | Sidebar: Overview, Orders Queue, My Deliveries, Stock Check, Waste Log, My Activity | Staff |
| STAFF-002 | Create dashboard overview | As a staff member, I want to see today's workload | Stats: orders to prepare, deliveries assigned, completed today, pending | Staff |
| STAFF-003 | Create orders queue page | As a staff member, I want to see orders I need to process | List of assigned orders sorted by priority/time, with status indicators | Staff |
| STAFF-004 | Display order details for processing | As a staff member, I want to see what to pack | Order items list with: product, quantity, packing checklist | Staff |
| STAFF-005 | Implement order status updates | As a staff member, I want to update order progress | Buttons: "Start Preparing" → "Mark Packed" → "Out for Delivery" → "Delivered" | Staff |
| STAFF-006 | Create packing checklist | As a staff member, I want to confirm all items packed | Checkbox list of order items, all must be checked to mark packed | Staff |
| STAFF-007 | Print packing slip | As a staff member, I want to print order details | "Print" button generates packing slip PDF, opens in new tab | Staff |
| STAFF-008 | Create my deliveries page | As a staff member, I want to see my delivery route | List of deliveries with addresses, map view, optimized route | Staff |
| STAFF-009 | Display delivery route map | As a staff member, I want navigation help | Google Maps integration showing delivery stops, turn-by-turn option | Staff |
| STAFF-010 | Implement proof of delivery capture | As a staff member, I want to record delivery completion | Modal: signature pad + photo upload + notes | Staff |
| STAFF-011 | Capture customer signature | As a staff member, I want customer confirmation | Canvas signature pad, saves as image | Staff |
| STAFF-012 | Capture delivery photo | As a staff member, I want proof of delivery location | Camera access or file upload for delivery photo | Staff |
| STAFF-013 | Add delivery notes | As a staff member, I want to record delivery details | Text field for notes (e.g., "Left at door", "Handed to recipient") | Staff |
| STAFF-014 | Create stock check page | As a staff member, I want to verify inventory | Search products, view current stock levels, low stock alerts | Staff |
| STAFF-015 | Create waste log page | As a staff member, I want to record damaged/expired items | Form: product selection, quantity, reason (damaged/expired/other), notes | Staff |
| STAFF-016 | Submit waste log entry | As a staff member, I want to save waste records | Entry saved, stock automatically adjusted, notification to admin | Staff |
| STAFF-017 | Create my activity page | As a staff member, I want to see my work history | Log of: orders processed, deliveries completed, waste logged | Staff |
| STAFF-018 | Implement real-time order notifications | As a staff member, I want alerts for new assignments | SSE notifications when new order assigned, audio alert option | Staff |
| STAFF-019 | View customer delivery info | As a staff member, I want customer contact for delivery | Display: customer name, phone, address, delivery notes | Staff |
| STAFF-020 | Handle delivery issues | As a staff member, I want to report problems | "Report Issue" button: customer not home, wrong address, refused delivery | Staff |
| STAFF-021 | Create staff API endpoints | As a developer, I need staff-specific APIs | Endpoints for queue, status updates, POD, waste logging | Developer |
| STAFF-022 | Implement SSE for real-time updates | As a developer, I need push notifications | Server-Sent Events for order assignments and status changes | Developer |
| STAFF-023 | Create staff Pinia stores | As a developer, I need state management | Stores for orders queue, deliveries, activity | Developer |
| STAFF-024 | Write staff module tests | As a developer, I need 100% test coverage | Unit, integration, E2E tests for all staff features | Developer |

### Staff API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/staff/dashboard` | Get dashboard stats | Yes (Staff) |
| GET | `/api/v1/staff/orders` | Get assigned orders | Yes (Staff) |
| GET | `/api/v1/staff/orders/{id}` | Get order details | Yes (Staff) |
| PUT | `/api/v1/staff/orders/{id}/status` | Update order status | Yes (Staff) |
| GET | `/api/v1/staff/deliveries` | Get assigned deliveries | Yes (Staff) |
| POST | `/api/v1/staff/deliveries/{id}/pod` | Submit proof of delivery | Yes (Staff) |
| POST | `/api/v1/staff/deliveries/{id}/issue` | Report delivery issue | Yes (Staff) |
| GET | `/api/v1/staff/stock` | Search stock levels | Yes (Staff) |
| POST | `/api/v1/staff/waste` | Log waste entry | Yes (Staff) |
| GET | `/api/v1/staff/activity` | Get personal activity log | Yes (Staff) |
| GET | `/api/v1/staff/notifications/stream` | SSE notification stream | Yes (Staff) |

### Staff Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  STAFF DASHBOARD                                         🔔(3)  👤 Sarah (Staff) ▼ │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  STAFF MENU    │  TODAY'S OVERVIEW                                                  │
│                │                                                                    │
│  ┌──────────┐  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ Overview │  │  │ 📋 To Pack  │ │ 🚚 Deliver  │ │ ✅ Done     │ │ ⚠️ Issues   │  │
│  ├──────────┤  │  │      5      │ │      3      │ │     12      │ │      0      │  │
│  │ Orders   │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  │ Queue    │  │                                                                    │
│  ├──────────┤  │  ORDERS QUEUE                                          [Refresh]  │
│  │ My       │  │  ┌────────────────────────────────────────────────────────────┐   │
│  │Deliveries│  │  │ 🔴 #ZM-0024  │ John S.    │ 4 items │ URGENT   │ [Process] │   │
│  ├──────────┤  │  │ 🟡 #ZM-0025  │ Mary K.    │ 2 items │ Normal   │ [Process] │   │
│  │ Stock    │  │  │ 🟢 #ZM-0026  │ Peter L.   │ 6 items │ Normal   │ [Process] │   │
│  │ Check    │  │  │ 🟢 #ZM-0027  │ Lisa M.    │ 3 items │ Normal   │ [Process] │   │
│  ├──────────┤  │  │ 🟢 #ZM-0028  │ Tom R.     │ 1 item  │ Normal   │ [Process] │   │
│  │ Waste    │  │  └────────────────────────────────────────────────────────────┘   │
│  │ Log      │  │                                                                    │
│  ├──────────┤  │  MY DELIVERIES TODAY                                   [View Map] │
│  │ My       │  │  ┌────────────────────────────────────────────────────────────┐   │
│  │ Activity │  │  │ 1. #ZM-0020 │ Engadine    │ 12:30 PM │ En Route  │ [POD]   │   │
│  └──────────┘  │  │ 2. #ZM-0021 │ Menai       │ 1:00 PM  │ Pending   │         │   │
│                │  │ 3. #ZM-0022 │ Sutherland  │ 1:30 PM  │ Pending   │         │   │
│  ───────────── │  └────────────────────────────────────────────────────────────┘   │
│  [Logout]      │                                                                    │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

### Proof of Delivery Modal

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  PROOF OF DELIVERY - Order #ZM-0024                                          [X]   │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  Customer: John Smith                                                               │
│  Address: 123 Main St, Engadine 2233                                                │
│                                                                                     │
│  CUSTOMER SIGNATURE                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                             │   │
│  │                        [Signature Canvas Area]                              │   │
│  │                                                                             │   │
│  │                            ~~~~signature~~~~                                │   │
│  │                                                                             │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                            [Clear Signature]        │
│                                                                                     │
│  DELIVERY PHOTO                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                             │   │
│  │                    📷 [Take Photo] or [Upload Photo]                        │   │
│  │                                                                             │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  DELIVERY NOTES                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │ Handed to customer directly                                                 │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                        ✅ COMPLETE DELIVERY                                 │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 5 Summary

| Section | Requirements | IDs |
|---------|--------------|-----|
| Customer Dashboard | 23 | CUST-001 to CUST-023 |
| Staff Dashboard | 24 | STAFF-001 to STAFF-024 |
| **Total** | **47** | |

---

**Previous:** [Part 4: Checkout Module](part4-checkout.md)

**Next:** [Part 6: Admin Dashboard](part6-admin.md)
