# Zambezi Meats - API Endpoints Reference

## Overview

Base URL: `https://zambezimeats.com.au/api/v1`

All API endpoints are versioned under `/api/v1/`. Authentication uses Laravel Sanctum with cookie-based SPA authentication.

---

## Health Check Endpoints

### Check Application Health

```
GET /health
```

Returns basic health status. Used by load balancers and monitoring.

**Response:**

```json
{
  "status": "healthy",
  "timestamp": "2024-12-13T10:00:00+11:00",
  "version": "1.0.0"
}
```

### Detailed Health Check

```
GET /health/detailed
```

Returns detailed status of all components.

**Response:**

```json
{
  "status": "healthy",
  "timestamp": "2024-12-13T10:00:00+11:00",
  "checks": {
    "application": { "status": "healthy", "maintenance_mode": false },
    "database": { "status": "healthy", "response_time_ms": 1.5 },
    "cache": { "status": "healthy", "driver": "redis" },
    "storage": { "status": "healthy" },
    "queue": { "status": "healthy", "driver": "redis" }
  },
  "environment": "production",
  "php_version": "8.2.0",
  "laravel_version": "11.0.0"
}
```

### Kubernetes Probes

```
GET /health/ready   # Readiness probe
GET /health/live    # Liveness probe
```

---

## Public Endpoints (No Authentication)

### Products

| Method | Endpoint                   | Description                      |
| ------ | -------------------------- | -------------------------------- |
| GET    | `/products`                | List all products with filtering |
| GET    | `/products/featured`       | Get featured products            |
| GET    | `/products/search`         | Search products                  |
| GET    | `/products/{slug}`         | Get product details              |
| GET    | `/products/{slug}/related` | Get related products             |

### Categories

| Method | Endpoint                      | Description              |
| ------ | ----------------------------- | ------------------------ |
| GET    | `/categories`                 | List all categories      |
| GET    | `/categories/{slug}`          | Get category details     |
| GET    | `/categories/{slug}/products` | Get products in category |

### Checkout (Validation)

| Method | Endpoint                     | Description                   |
| ------ | ---------------------------- | ----------------------------- |
| POST   | `/checkout/validate-address` | Validate delivery address     |
| POST   | `/checkout/calculate-fee`    | Calculate delivery fee        |
| POST   | `/checkout/validate-promo`   | Validate promo code           |
| GET    | `/checkout/payment-methods`  | Get available payment methods |

### Settings

| Method | Endpoint           | Description               |
| ------ | ------------------ | ------------------------- |
| GET    | `/settings/public` | Get public store settings |

---

## Authentication Endpoints

| Method | Endpoint                | Description                      |
| ------ | ----------------------- | -------------------------------- |
| POST   | `/auth/register`        | Register new customer            |
| POST   | `/auth/login`           | Login                            |
| POST   | `/auth/logout`          | Logout (auth required)           |
| GET    | `/auth/user`            | Get current user (auth required) |
| POST   | `/auth/refresh`         | Refresh session (auth required)  |
| POST   | `/auth/forgot-password` | Request password reset           |
| POST   | `/auth/reset-password`  | Reset password with token        |

---

## Customer Endpoints (Auth Required)

### Cart

| Method | Endpoint           | Description                 |
| ------ | ------------------ | --------------------------- |
| GET    | `/cart`            | Get cart contents           |
| POST   | `/cart/items`      | Add item to cart            |
| PUT    | `/cart/items/{id}` | Update cart item            |
| DELETE | `/cart/items/{id}` | Remove cart item            |
| DELETE | `/cart`            | Clear cart                  |
| POST   | `/cart/validate`   | Validate cart items         |
| POST   | `/cart/sync`       | Sync cart from localStorage |

### Checkout

| Method | Endpoint                     | Description              |
| ------ | ---------------------------- | ------------------------ |
| GET    | `/checkout/session`          | Get checkout session     |
| POST   | `/checkout/create-order`     | Create order             |
| POST   | `/checkout/payment/stripe`   | Process Stripe payment   |
| POST   | `/checkout/payment/paypal`   | Process PayPal payment   |
| POST   | `/checkout/payment/afterpay` | Process Afterpay payment |
| POST   | `/checkout/payment/cod`      | Process Cash on Delivery |

### Customer Dashboard

| Method | Endpoint                        | Description          |
| ------ | ------------------------------- | -------------------- |
| GET    | `/customer/dashboard`           | Dashboard overview   |
| GET    | `/customer/profile`             | Get profile          |
| PUT    | `/customer/profile`             | Update profile       |
| PUT    | `/customer/password`            | Change password      |
| GET    | `/customer/orders`              | List orders          |
| GET    | `/customer/orders/{id}`         | Get order details    |
| POST   | `/customer/orders/{id}/reorder` | Reorder              |
| GET    | `/customer/addresses`           | List addresses       |
| POST   | `/customer/addresses`           | Add address          |
| PUT    | `/customer/addresses/{id}`      | Update address       |
| DELETE | `/customer/addresses/{id}`      | Delete address       |
| GET    | `/customer/wishlist`            | Get wishlist         |
| POST   | `/customer/wishlist`            | Add to wishlist      |
| DELETE | `/customer/wishlist/{id}`       | Remove from wishlist |
| GET    | `/customer/notifications`       | Get notifications    |
| GET    | `/customer/tickets`             | Get support tickets  |
| POST   | `/customer/tickets`             | Create ticket        |

---

## Staff Endpoints (Staff Role Required)

| Method | Endpoint                               | Description           |
| ------ | -------------------------------------- | --------------------- |
| GET    | `/staff/dashboard`                     | Staff dashboard       |
| GET    | `/staff/orders`                        | Order queue           |
| GET    | `/staff/orders/{id}`                   | Order details         |
| PUT    | `/staff/orders/{id}/status`            | Update order status   |
| PUT    | `/staff/orders/{id}/out-for-delivery`  | Mark out for delivery |
| POST   | `/staff/orders/{id}/proof-of-delivery` | Upload POD            |
| GET    | `/staff/deliveries/today`              | Today's deliveries    |
| GET    | `/staff/pickups/today`                 | Today's pickups       |
| POST   | `/staff/waste`                         | Log waste             |
| GET    | `/staff/stock`                         | Stock check           |
| PUT    | `/staff/stock/{id}`                    | Update stock          |

---

## Admin Endpoints (Admin Role Required)

### Dashboard & Orders

| Method | Endpoint                    | Description               |
| ------ | --------------------------- | ------------------------- |
| GET    | `/admin/dashboard`          | Admin dashboard with KPIs |
| GET    | `/admin/orders`             | List all orders           |
| GET    | `/admin/orders/{id}`        | Order details             |
| PUT    | `/admin/orders/{id}`        | Update order              |
| POST   | `/admin/orders/{id}/assign` | Assign to staff           |
| POST   | `/admin/orders/{id}/refund` | Refund order              |

### Products & Categories

| Method | Endpoint                 | Description     |
| ------ | ------------------------ | --------------- |
| GET    | `/admin/products`        | List products   |
| POST   | `/admin/products`        | Create product  |
| PUT    | `/admin/products/{id}`   | Update product  |
| DELETE | `/admin/products/{id}`   | Delete product  |
| GET    | `/admin/categories`      | List categories |
| POST   | `/admin/categories`      | Create category |
| PUT    | `/admin/categories/{id}` | Update category |
| DELETE | `/admin/categories/{id}` | Delete category |

### User Management

| Method | Endpoint                | Description      |
| ------ | ----------------------- | ---------------- |
| GET    | `/admin/customers`      | List customers   |
| GET    | `/admin/customers/{id}` | Customer details |
| PUT    | `/admin/customers/{id}` | Update customer  |
| GET    | `/admin/staff`          | List staff       |
| POST   | `/admin/staff`          | Create staff     |
| PUT    | `/admin/staff/{id}`     | Update staff     |
| DELETE | `/admin/staff/{id}`     | Delete staff     |

### Inventory

| Method | Endpoint                     | Description         |
| ------ | ---------------------------- | ------------------- |
| GET    | `/admin/inventory/dashboard` | Inventory dashboard |
| GET    | `/admin/inventory`           | List inventory      |
| GET    | `/admin/inventory/{id}`      | Product inventory   |
| POST   | `/admin/inventory/receive`   | Receive stock       |
| POST   | `/admin/inventory/adjust`    | Adjust stock        |
| GET    | `/admin/inventory/low-stock` | Low stock items     |
| GET    | `/admin/inventory/alerts`    | Stock alerts        |
| GET    | `/admin/waste`               | Waste entries       |
| PUT    | `/admin/waste/{id}/approve`  | Approve waste       |

### Deliveries

| Method | Endpoint                        | Description        |
| ------ | ------------------------------- | ------------------ |
| GET    | `/admin/deliveries/dashboard`   | Delivery dashboard |
| GET    | `/admin/deliveries`             | List deliveries    |
| GET    | `/admin/deliveries/{id}`        | Delivery details   |
| PUT    | `/admin/deliveries/{id}/assign` | Assign delivery    |
| GET    | `/admin/delivery-zones`         | List zones         |
| POST   | `/admin/delivery-zones`         | Create zone        |
| PUT    | `/admin/delivery-zones/{id}`    | Update zone        |
| DELETE | `/admin/delivery-zones/{id}`    | Delete zone        |

### Reports

| Method | Endpoint                       | Description       |
| ------ | ------------------------------ | ----------------- |
| GET    | `/admin/reports/dashboard`     | Reports dashboard |
| GET    | `/admin/reports/sales-summary` | Sales summary     |
| GET    | `/admin/reports/revenue`       | Revenue report    |
| GET    | `/admin/reports/orders`        | Orders report     |
| GET    | `/admin/reports/products`      | Products report   |
| GET    | `/admin/reports/categories`    | Categories report |
| GET    | `/admin/reports/top-products`  | Top products      |
| GET    | `/admin/reports/customers`     | Customers report  |
| GET    | `/admin/reports/staff`         | Staff report      |
| GET    | `/admin/reports/deliveries`    | Deliveries report |
| GET    | `/admin/reports/inventory`     | Inventory report  |
| GET    | `/admin/reports/financial`     | Financial report  |
| GET    | `/admin/reports/{type}/export` | Export report     |

### Settings

| Method | Endpoint                                 | Description           |
| ------ | ---------------------------------------- | --------------------- |
| GET    | `/admin/settings`                        | All settings          |
| GET    | `/admin/settings/group/{group}`          | Settings by group     |
| PUT    | `/admin/settings/group/{group}`          | Update settings group |
| POST   | `/admin/settings/logo`                   | Upload logo           |
| GET    | `/admin/settings/email-templates`        | Email templates       |
| PUT    | `/admin/settings/email-templates/{name}` | Update template       |
| POST   | `/admin/settings/email-test`             | Send test email       |
| POST   | `/admin/settings/export`                 | Export settings       |
| POST   | `/admin/settings/import`                 | Import settings       |
| GET    | `/admin/settings/history`                | Settings history      |

---

## Webhooks (No Auth - Signature Verified)

| Method | Endpoint           | Description            |
| ------ | ------------------ | ---------------------- |
| POST   | `/webhooks/stripe` | Stripe webhook handler |
| POST   | `/webhooks/paypal` | PayPal webhook handler |

---

## Common Query Parameters

### Pagination

```
?page=1&per_page=20
```

### Date Filtering

```
?start_date=2024-01-01&end_date=2024-12-31
?preset=today|yesterday|last_week|last_month|year
```

### Sorting

```
?sort_by=created_at&sort_order=desc
```

### Search

```
?search=keyword
?q=keyword
```

---

## Response Format

All responses follow this format:

**Success:**

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Paginated:**

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  }
}
```

**Error:**

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation message"]
  }
}
```

---

## HTTP Status Codes

| Code | Description         |
| ---- | ------------------- |
| 200  | Success             |
| 201  | Created             |
| 204  | No Content          |
| 400  | Bad Request         |
| 401  | Unauthorized        |
| 403  | Forbidden           |
| 404  | Not Found           |
| 422  | Validation Error    |
| 429  | Too Many Requests   |
| 500  | Server Error        |
| 503  | Service Unavailable |

---

## Rate Limiting

Default: 60 requests per minute per IP.

Rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1702434000
```
