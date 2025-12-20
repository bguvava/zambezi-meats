# Customer Dashboard Module Documentation

## Overview

The Customer Dashboard module provides a comprehensive self-service portal for authenticated customers of Zambezi Meats. This module covers order management, profile settings, address book, wishlist functionality, notifications, and support tickets.

## Requirements Coverage

| Requirement | Description                  | Status      |
| ----------- | ---------------------------- | ----------- |
| CUST-001    | Customer dashboard overview  | ✅ Complete |
| CUST-002    | Order summary widget         | ✅ Complete |
| CUST-003    | Recent orders list           | ✅ Complete |
| CUST-004    | Quick action buttons         | ✅ Complete |
| CUST-005    | Order list page with filters | ✅ Complete |
| CUST-006    | Order detail view            | ✅ Complete |
| CUST-007    | Reorder functionality        | ✅ Complete |
| CUST-008    | Profile information display  | ✅ Complete |
| CUST-009    | Profile edit form            | ✅ Complete |
| CUST-010    | Address book list            | ✅ Complete |
| CUST-011    | Address CRUD operations      | ✅ Complete |
| CUST-012    | Wishlist page                | ✅ Complete |
| CUST-013    | Add to cart from wishlist    | ✅ Complete |
| CUST-014    | Notifications page           | ✅ Complete |
| CUST-015    | Mark notifications as read   | ✅ Complete |
| CUST-016    | Support tickets page         | ✅ Complete |
| CUST-017    | Create support ticket        | ✅ Complete |
| CUST-018    | Reply to support ticket      | ✅ Complete |
| CUST-019    | Password change form         | ✅ Complete |
| CUST-020    | Currency preference setting  | ✅ Complete |
| CUST-021    | Order invoice download (PDF) | ✅ Complete |
| CUST-022    | Order tracking display       | ✅ Complete |
| CUST-023    | Test coverage                | ✅ Complete |

## Architecture

### Backend Components

#### Controller

- **Location:** `backend/app/Http/Controllers/Api/V1/CustomerController.php`
- **Responsibilities:** Handles all customer dashboard API endpoints

#### API Endpoints

| Method | Endpoint                                   | Description                   |
| ------ | ------------------------------------------ | ----------------------------- |
| GET    | `/api/v1/customer/dashboard`               | Dashboard overview with stats |
| GET    | `/api/v1/customer/orders`                  | List customer orders          |
| GET    | `/api/v1/customer/orders/{id}`             | Order detail                  |
| POST   | `/api/v1/customer/orders/{id}/reorder`     | Reorder previous order        |
| GET    | `/api/v1/customer/orders/{id}/invoice`     | Download order invoice (PDF)  |
| GET    | `/api/v1/customer/profile`                 | Get profile information       |
| PUT    | `/api/v1/customer/profile`                 | Update profile                |
| PUT    | `/api/v1/customer/password`                | Change password               |
| GET    | `/api/v1/customer/addresses`               | List addresses                |
| POST   | `/api/v1/customer/addresses`               | Create address                |
| PUT    | `/api/v1/customer/addresses/{id}`          | Update address                |
| DELETE | `/api/v1/customer/addresses/{id}`          | Delete address                |
| PUT    | `/api/v1/customer/addresses/{id}/default`  | Set default address           |
| GET    | `/api/v1/customer/wishlist`                | List wishlist items           |
| POST   | `/api/v1/customer/wishlist`                | Add to wishlist               |
| DELETE | `/api/v1/customer/wishlist/{id}`           | Remove from wishlist          |
| GET    | `/api/v1/customer/notifications`           | List notifications            |
| PUT    | `/api/v1/customer/notifications/{id}/read` | Mark as read                  |
| PUT    | `/api/v1/customer/notifications/read-all`  | Mark all as read              |
| GET    | `/api/v1/customer/tickets`                 | List support tickets          |
| POST   | `/api/v1/customer/tickets`                 | Create support ticket         |
| GET    | `/api/v1/customer/tickets/{id}`            | Get ticket detail             |
| POST   | `/api/v1/customer/tickets/{id}/reply`      | Reply to ticket               |

#### PDF Invoice Generation

- **Library:** DomPDF
- **Template:** `backend/resources/views/invoices/order.blade.php`
- **Features:**
  - Company branding with Zambezi Meats logo
  - Customer billing and shipping addresses
  - Order items with quantities and prices
  - Payment summary with subtotal, shipping, tax, and total
  - Professional footer with contact information

### Frontend Components

#### Pages

| Page               | Location                                             | Description                           |
| ------------------ | ---------------------------------------------------- | ------------------------------------- |
| DashboardPage      | `frontend/src/pages/customer/DashboardPage.vue`      | Overview with stats and recent orders |
| OrdersPage         | `frontend/src/pages/customer/OrdersPage.vue`         | Order list with filtering             |
| OrderDetailPage    | `frontend/src/pages/customer/OrderDetailPage.vue`    | Order details with tracking           |
| ProfilePage        | `frontend/src/pages/customer/ProfilePage.vue`        | Profile and password management       |
| AddressesPage      | `frontend/src/pages/customer/AddressesPage.vue`      | Address book management               |
| WishlistPage       | `frontend/src/pages/customer/WishlistPage.vue`       | Wishlist with cart integration        |
| NotificationsPage  | `frontend/src/pages/customer/NotificationsPage.vue`  | Notification center                   |
| SupportTicketsPage | `frontend/src/pages/customer/SupportTicketsPage.vue` | Support ticket management             |

#### Layout

- **CustomerLayout:** `frontend/src/layouts/CustomerLayout.vue`
  - Sidebar navigation with icons
  - Mobile-responsive hamburger menu
  - User welcome message
  - Logout functionality

#### Pinia Stores

| Store                    | Location                                       | Purpose                             |
| ------------------------ | ---------------------------------------------- | ----------------------------------- |
| profile.js               | `frontend/src/stores/profile.js`               | Profile and password management     |
| address.js               | `frontend/src/stores/address.js`               | Address CRUD operations             |
| wishlist.js              | `frontend/src/stores/wishlist.js`              | Wishlist state and cart integration |
| tickets.js               | `frontend/src/stores/tickets.js`               | Support ticket management           |
| customerNotifications.js | `frontend/src/stores/customerNotifications.js` | Notifications state                 |
| orders.js                | `frontend/src/stores/orders.js`                | Order management (existing)         |

## Routes

### Frontend Routes

```javascript
{
  path: '/customer',
  component: CustomerLayout,
  children: [
    { path: '', name: 'customer-dashboard', component: DashboardPage },
    { path: 'orders', name: 'customer-orders', component: OrdersPage },
    { path: 'orders/:id', name: 'customer-order-detail', component: OrderDetailPage },
    { path: 'profile', name: 'customer-profile', component: ProfilePage },
    { path: 'addresses', name: 'customer-addresses', component: AddressesPage },
    { path: 'wishlist', name: 'customer-wishlist', component: WishlistPage },
    { path: 'notifications', name: 'customer-notifications', component: NotificationsPage },
    { path: 'support', name: 'customer-support', component: SupportTicketsPage },
  ]
}
```

## Testing

### Backend Tests

- **Location:** `backend/tests/Feature/Api/V1/CustomerControllerTest.php`
- **Test Count:** 42 tests, 149 assertions
- **Coverage:**
  - Dashboard overview endpoint
  - Order list, detail, reorder, and invoice
  - Profile get/update and password change
  - Address CRUD and default setting
  - Wishlist add/remove
  - Notifications list and mark as read
  - Support tickets create, list, detail, and reply
  - Authentication and authorization checks

### Frontend Tests

- **Location:** `frontend/src/tests/customer/`
- **Test Files:**
  - `profileStore.spec.js` - 27 tests
  - `addressStore.spec.js` - 29 tests
  - `wishlistStore.spec.js` - 25 tests
  - `ticketsStore.spec.js` - 35 tests
  - `customerNotificationsStore.spec.js` - 30 tests
  - `customerComponents.spec.js` - 13 tests
- **Total Frontend Tests:** 159 tests

### Running Tests

```bash
# Backend Customer tests
cd backend && php vendor/bin/phpunit --filter CustomerControllerTest

# Frontend Customer tests
cd frontend && npx vitest run src/tests/customer

# All frontend tests
cd frontend && npx vitest run
```

## Test Summary

| Suite                        | Tests   | Pass Rate |
| ---------------------------- | ------- | --------- |
| Backend CustomerController   | 42      | 100%      |
| Frontend Profile Store       | 27      | 100%      |
| Frontend Address Store       | 29      | 100%      |
| Frontend Wishlist Store      | 25      | 100%      |
| Frontend Tickets Store       | 35      | 100%      |
| Frontend Notifications Store | 30      | 100%      |
| Frontend Component Imports   | 13      | 100%      |
| **Total**                    | **201** | **100%**  |

## Features Detail

### Dashboard (CUST-001 to CUST-004)

- Order statistics (total, pending, delivered)
- Wishlist count
- Open support tickets count
- Unread notifications count
- Recent orders quick view
- Quick action buttons (Shop Now, Track Order)

### Orders (CUST-005 to CUST-007, CUST-021, CUST-022)

- Filterable order list by status
- Order detail with all items
- Order status tracking display
- Reorder functionality (adds all items to cart)
- PDF invoice download

### Profile (CUST-008, CUST-009, CUST-019, CUST-020)

- View and edit personal information
- Change password with validation
- Currency preference setting (AUD/USD/ZWL)

### Addresses (CUST-010, CUST-011)

- Address list with default indicator
- Add new address form
- Edit existing addresses
- Delete addresses (with confirmation)
- Set default address

### Wishlist (CUST-012, CUST-013)

- Wishlist items display
- Remove from wishlist
- Add to cart (single item)
- Move to cart (add and remove)

### Notifications (CUST-014, CUST-015)

- Notification list with tabs (All/Unread)
- Notification types with icons:
  - Order status updates
  - Promotions
  - System messages
- Mark individual as read
- Mark all as read
- Relative time formatting

### Support Tickets (CUST-016, CUST-017, CUST-018)

- Ticket list with status filter tabs
- Create new ticket form
- Ticket detail with conversation history
- Reply to ticket
- Status and priority badges
- Ticket statuses: open, in_progress, resolved, closed
- Ticket priorities: low, medium, high, urgent

## Security

- All endpoints require authentication via Laravel Sanctum
- Customers can only access their own data
- Password change requires current password verification
- CSRF protection on all forms

## Dependencies

### Backend

- Laravel 11
- Laravel Sanctum (authentication)
- DomPDF (PDF invoice generation)

### Frontend

- Vue.js 3 (Composition API)
- Pinia (state management)
- Vue Router
- Axios (HTTP client)
- Lucide Vue Next (icons)
- Tailwind CSS (styling)

## Files Created/Modified This Session

### Created

- `backend/resources/views/invoices/order.blade.php` - PDF invoice template
- `frontend/src/stores/profile.js` - Profile store
- `frontend/src/stores/address.js` - Address store
- `frontend/src/stores/wishlist.js` - Wishlist store
- `frontend/src/stores/tickets.js` - Tickets store
- `frontend/src/stores/customerNotifications.js` - Notifications store
- `frontend/src/pages/customer/NotificationsPage.vue` - Notifications page
- `frontend/src/pages/customer/SupportTicketsPage.vue` - Support tickets page
- `frontend/src/tests/customer/profileStore.spec.js` - Profile tests
- `frontend/src/tests/customer/addressStore.spec.js` - Address tests
- `frontend/src/tests/customer/wishlistStore.spec.js` - Wishlist tests
- `frontend/src/tests/customer/ticketsStore.spec.js` - Tickets tests
- `frontend/src/tests/customer/customerNotificationsStore.spec.js` - Notifications tests
- `frontend/src/tests/customer/customerComponents.spec.js` - Component tests
- `docs/customer/README.md` - This documentation

### Modified

- `backend/tests/Feature/Api/V1/CustomerControllerTest.php` - Fixed invoice test
- `frontend/src/router/index.js` - Added notifications and support routes
- `frontend/src/layouts/CustomerLayout.vue` - Added navigation items
