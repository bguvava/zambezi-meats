# Product Management API Endpoints

## Overview

This document describes all API endpoints for the Product Management module (Phase 7). The module provides comprehensive product and category management functionality including CRUD operations, stock management, image uploads, and PDF export capabilities.

**Base URL**: `/api/v1/admin`

**Total Endpoints**: 19 (12 product + 7 category)

**Authentication**: Laravel Sanctum (cookie-based SPA authentication)

**Response Format**: Custom JSON structure

```json
{
  "success": true|false,
  "message": "Human-readable message",
  "data": {...},
  "error": {
    "code": "ERROR_CODE",
    "errors": {
      "field": ["Error message 1", "Error message 2"]
    }
  }
}
```

---

## Table of Contents

- [Product Endpoints](#product-endpoints)
  - [Listing & Search](#1-product-listing--search)
  - [CRUD Operations](#2-product-crud-operations)
  - [Stock Management](#3-stock-management)
  - [Image Management](#4-image-management)
  - [Export](#5-export)
- [Category Endpoints](#category-endpoints)
- [Authentication & Authorization](#authentication--authorization)
- [Validation Rules](#validation-rules)
- [Error Codes](#error-codes)
- [Filter Options](#filter-options)

---

## Product Endpoints

### 1. Product Listing & Search

#### GET /products

**Requirement**: PROD-003, PROD-004, PROD-005

Get paginated list of products with search and filters.

**Authorization**: Admin, Staff

**Query Parameters**:
| Parameter | Type | Description | Values |
|-----------|------|-------------|--------|
| search | string | Search in name, SKU, description | Any text |
| category_id | integer | Filter by category | Category ID or 'all' |
| status | string | Filter by active status | 'active', 'inactive', 'all' |
| stock_status | string | Filter by stock level | 'in_stock', 'low_stock', 'out_of_stock' |
| per_page | integer | Items per page (default: 15) | 1-100 |

**Success Response (200)**:

```json
{
  "success": true,
  "products": [
    {
      "id": 1,
      "name": "Premium Beef Ribeye Steak",
      "slug": "premium-beef-ribeye-steak",
      "sku": "PROD-BEEF-A1B2",
      "description": "Tender, marbled ribeye steak...",
      "short_description": "Premium quality ribeye",
      "category_id": 2,
      "price_aud": 45.99,
      "sale_price_aud": null,
      "stock": 25,
      "unit": "kg",
      "weight_kg": 0.5,
      "is_active": true,
      "is_featured": false,
      "created_at": "2025-12-19T10:00:00Z",
      "updated_at": "2025-12-19T10:00:00Z",
      "category": {
        "id": 2,
        "name": "Beef",
        "slug": "beef"
      },
      "images": [
        {
          "id": 1,
          "product_id": 1,
          "image_path": "products/ribeye-abc123.jpg",
          "alt_text": "Premium Beef Ribeye Steak",
          "sort_order": 0,
          "is_primary": true
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  }
}
```

---

#### GET /products/low-stock

**Requirement**: PROD-011

Get products with low stock levels for alerts.

**Authorization**: Admin, Staff

**Query Parameters**:
| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| threshold | integer | Stock level threshold | 10 |

**Success Response (200)**:

```json
{
  "success": true,
  "products": [
    {
      "id": 1,
      "name": "Lamb Chops",
      "sku": "PROD-LAMB-X1Y2",
      "stock": 5,
      "price_aud": 32.99,
      "category": {
        "name": "Lamb"
      }
    }
  ]
}
```

---

### 2. Product CRUD Operations

#### POST /products

**Requirement**: PROD-006, PROD-007, PROD-016

Create a new product with optional images.

**Authorization**: Admin, Staff

**Content-Type**: `multipart/form-data` (for image uploads) or `application/json`

**Request Body**:

```json
{
  "name": "Premium Beef Ribeye Steak",
  "slug": "premium-beef-ribeye-steak",
  "description": "Tender, marbled ribeye steak from premium cattle...",
  "short_description": "Premium quality ribeye steak",
  "category_id": 2,
  "price_aud": 45.99,
  "sale_price_aud": 39.99,
  "stock": 25,
  "sku": "PROD-BEEF-A1B2",
  "unit": "kg",
  "weight_kg": 0.5,
  "is_active": true,
  "is_featured": false,
  "images": ["<UploadedFile>", "<UploadedFile>"]
}
```

**Validation Rules**:
| Field | Rules |
|-------|-------|
| name | required, string, min:3, max:255 |
| slug | required, unique, regex:/^[a-z0-9]+(?:-[a-z0-9]+)_$/ |
| category_id | required, exists:categories |
| price_aud | required, numeric, min:0.01, max:99999.99 |
| sale_price_aud | nullable, numeric, lt:price_aud |
| stock | required, integer, min:0, max:999999 |
| sku | required, unique, max:100 |
| unit | required, in:kg,piece,pack |
| images | nullable, array, max:5 |
| images._ | image, mimes:jpeg,jpg,png,webp, max:2048KB |

**Success Response (201)**:

```json
{
  "success": true,
  "message": "Product created successfully.",
  "product": {
    "id": 1,
    "name": "Premium Beef Ribeye Steak",
    ...
  }
}
```

**Error Response (422)**:

```json
{
  "success": false,
  "message": "Validation failed.",
  "error": {
    "code": "VALIDATION_ERROR",
    "errors": {
      "name": ["The name field is required."],
      "price_aud": ["The price must be at least $0.01."],
      "images.0": ["Each image must not exceed 2MB."]
    }
  }
}
```

---

#### PUT /products/{id}

**Requirement**: PROD-008

Update an existing product.

**Authorization**: Admin, Staff

**Content-Type**: `multipart/form-data` or `application/json`

**Request Body**: (All fields optional)

```json
{
  "name": "Updated Product Name",
  "price_aud": 49.99,
  "stock": 30,
  "images": ["<UploadedFile>"]
}
```

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Product updated successfully.",
  "product": {...}
}
```

---

#### GET /products/{id}

**Requirement**: PROD-017

Get single product details.

**Authorization**: Admin, Staff

**Success Response (200)**:

```json
{
  "success": true,
  "product": {
    "id": 1,
    "name": "Premium Beef Ribeye Steak",
    ...
    "category": {...},
    "images": [...]
  }
}
```

**Error Response (404)**:

```json
{
  "success": false,
  "message": "Product not found."
}
```

---

#### DELETE /products/{id}

**Requirement**: PROD-009

Soft delete a product (marks as inactive).

**Authorization**: Admin only

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Product deleted successfully."
}
```

---

### 3. Stock Management

#### POST /products/{id}/adjust-stock

**Requirement**: PROD-010

Adjust product stock levels with reason tracking.

**Authorization**: Admin, Staff

**Request Body**:

```json
{
  "quantity": 20,
  "type": "increase",
  "reason": "New stock arrived from supplier"
}
```

**Validation Rules**:
| Field | Rules | Values |
|-------|-------|--------|
| quantity | required, integer, not_in:0 | Any non-zero integer |
| type | required, in:increase,decrease,adjustment | Stock adjustment type |
| reason | required, string, max:500 | Reason for adjustment |

**Type Behavior**:

- `increase`: Adds quantity to current stock (quantity=20 means +20)
- `decrease`: Subtracts quantity from current stock (quantity=15 means -15)
- `adjustment`: Sets stock to exact quantity (quantity=100 sets stock to 100)

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Stock adjusted successfully.",
  "product": {...},
  "stock_before": 50,
  "stock_after": 70
}
```

**Database Records Created**:

1. **inventory_logs** table - Full audit trail

   - type: Maps to `addition`/`deduction`/`adjustment` (database enum)
   - quantity, stock_before, stock_after, reason, user_id

2. **activity_logs** table - Activity tracking
   - action: `stock_adjusted`
   - old_values, new_values, user_id

---

#### GET /products/{id}/stock-history

**Requirement**: PROD-010

Get stock adjustment history for a product.

**Authorization**: Admin, Staff

**Success Response (200)**:

```json
{
  "success": true,
  "history": [
    {
      "id": 1,
      "product_id": 1,
      "type": "addition",
      "quantity": 20,
      "stock_before": 50,
      "stock_after": 70,
      "reason": "New stock arrived",
      "user_id": 1,
      "created_at": "2025-12-19T10:00:00Z",
      "user": {
        "name": "Admin User"
      }
    }
  ]
}
```

---

### 4. Image Management

#### DELETE /products/{id}/images/{imageId}

**Requirement**: PROD-012

Delete a product image.

**Authorization**: Admin, Staff

**Behavior**:

- If deleting primary image, automatically sets next image as primary
- Reorders remaining images (sort_order)

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Image deleted successfully."
}
```

---

#### POST /products/{id}/images/reorder

**Requirement**: PROD-013

Reorder product images via drag-and-drop.

**Authorization**: Admin, Staff

**Request Body**:

```json
{
  "images": [
    { "id": 3, "sort_order": 0 },
    { "id": 1, "sort_order": 1 },
    { "id": 2, "sort_order": 2 }
  ]
}
```

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Images reordered successfully."
}
```

---

### 5. Export

#### GET /products/export

**Requirement**: PROD-014

Export products to PDF with applied filters.

**Authorization**: Admin only

**Query Parameters**: Same as GET /products (search, category_id, status, stock_status)

**Success Response (200)**:

- **Content-Type**: `application/pdf`
- **Content-Disposition**: `attachment; filename="products-export-2025-12-19-143025.pdf"`

**PDF Content**:

- Header: Zambezi Meats branding
- Meta Info: Generated date, total products, stock value
- Filters: Applied filters displayed
- Product Table: SKU, Name, Category, Price, Sale Price, Stock, Unit, Status
- Summary: Total products, total stock value
- Footer: Copyright, generation timestamp

---

## Category Endpoints

### GET /categories

**Requirement**: ADMIN-014

Get all categories with product counts.

**Authorization**: Admin, Staff

**Success Response (200)**:

```json
{
  "success": true,
  "categories": [
    {
      "id": 1,
      "name": "Beef",
      "slug": "beef",
      "description": "Premium beef products",
      "image": "categories/beef-abc123.jpg",
      "parent_id": null,
      "sort_order": 0,
      "products_count": 25
    }
  ]
}
```

---

### POST /categories

**Requirement**: PROD-002, PROD-016

Create a new category.

**Authorization**: Admin, Staff

**Content-Type**: `multipart/form-data`

**Request Body**:

```json
{
  "name": "Beef",
  "slug": "beef",
  "description": "Premium beef products",
  "image": "<UploadedFile>",
  "parent_id": null,
  "sort_order": 0
}
```

**Validation Rules**:
| Field | Rules |
|-------|-------|
| name | required, unique, min:2, max:255 |
| slug | required, unique, regex:/^[a-z0-9]+(?:-[a-z0-9]+)\*$/ |
| description | nullable, max:1000 |
| image | nullable, image, mimes:jpeg,jpg,png,webp,svg, max:2048KB |
| parent_id | nullable, exists:categories |
| sort_order | integer, min:0, max:9999 |

**Success Response (201)**:

```json
{
  "success": true,
  "message": "Category created successfully.",
  "category": {...}
}
```

---

### PUT /categories/{id}

**Requirement**: PROD-002

Update a category.

**Authorization**: Admin, Staff

**Request Body**: (All fields optional)

```json
{
  "name": "Updated Category Name",
  "description": "Updated description",
  "sort_order": 5
}
```

**Validation**:

- Prevents self-reference (parent_id cannot be same as id)
- Name must be unique (except for current category)

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Category updated successfully.",
  "category": {...}
}
```

---

### DELETE /categories/{id}

**Requirement**: PROD-002

Delete a category.

**Authorization**: Admin only

**Protection**:

- Cannot delete category with products
- Returns 409 Conflict if category has products

**Success Response (200)**:

```json
{
  "success": true,
  "message": "Category deleted successfully."
}
```

**Error Response (409)**:

```json
{
  "success": false,
  "message": "Cannot delete category with products. Please move or delete the products first."
}
```

---

## Authentication & Authorization

### Authentication Method

- **Type**: Laravel Sanctum (cookie-based SPA authentication)
- **Cookie**: XSRF-TOKEN
- **Header**: X-XSRF-TOKEN (automatically sent by Axios)

### Authorization Roles

| Role         | Permissions                                                |
| ------------ | ---------------------------------------------------------- |
| **Admin**    | Full access to all endpoints                               |
| **Staff**    | CRUD operations on products and categories (except delete) |
| **Customer** | No access to admin endpoints                               |

### Authorization Matrix

| Endpoint                         | Admin | Staff | Customer |
| -------------------------------- | ----- | ----- | -------- |
| GET /products                    | ✓     | ✓     | ✗        |
| POST /products                   | ✓     | ✓     | ✗        |
| PUT /products/{id}               | ✓     | ✓     | ✗        |
| DELETE /products/{id}            | ✓     | ✗     | ✗        |
| POST /products/{id}/adjust-stock | ✓     | ✓     | ✗        |
| GET /products/export             | ✓     | ✗     | ✗        |
| POST /categories                 | ✓     | ✓     | ✗        |
| DELETE /categories/{id}          | ✓     | ✗     | ✗        |

### Error Response - Unauthorized (403)

```json
{
  "success": false,
  "message": "Forbidden. Admin role required."
}
```

### Error Response - Unauthenticated (401)

```json
{
  "message": "Unauthenticated."
}
```

---

## Validation Rules

### Common Product Validations

```php
'name' => ['required', 'string', 'min:3', 'max:255']
'slug' => ['required', 'unique:products', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/']
'category_id' => ['required', 'exists:categories,id']
'price_aud' => ['required', 'numeric', 'min:0.01', 'max:99999.99']
'sale_price_aud' => ['nullable', 'numeric', 'min:0', 'lt:price_aud']
'stock' => ['required', 'integer', 'min:0', 'max:999999']
'sku' => ['required', 'unique:products', 'max:100']
'unit' => ['required', 'in:kg,piece,pack']
'images' => ['nullable', 'array', 'max:5']
'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048']
```

### Common Category Validations

```php
'name' => ['required', 'unique:categories', 'min:2', 'max:255']
'slug' => ['required', 'unique:categories', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/']
'description' => ['nullable', 'string', 'max:1000']
'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048']
'parent_id' => ['nullable', 'exists:categories,id']
'sort_order' => ['integer', 'min:0', 'max:9999']
```

---

## Error Codes

| Code             | HTTP Status | Description                                     |
| ---------------- | ----------- | ----------------------------------------------- |
| VALIDATION_ERROR | 422         | Request validation failed                       |
| FORBIDDEN        | 403         | Insufficient permissions                        |
| NOT_FOUND        | 404         | Resource not found                              |
| CONFLICT         | 409         | Resource conflict (e.g., category has products) |
| SERVER_ERROR     | 500         | Internal server error                           |

---

## Filter Options

### Product Filters

```
GET /api/v1/admin/products?category_id=2&status=active&stock_status=low_stock&search=ribeye&per_page=20
```

**Available Filters**:

- `search` - Searches name, SKU, description
- `category_id` - Filter by category (integer or 'all')
- `status` - active | inactive | all
- `stock_status` - in_stock (≥10) | low_stock (1-9) | out_of_stock (0)
- `per_page` - Pagination limit (1-100, default: 15)

### Category Filters

Categories are always returned ordered by `sort_order`.

---

## Testing

### Run All Phase 7 Tests

```bash
php artisan test tests/Feature/Api/V1/AdminProductTest.php tests/Feature/Api/V1/AdminCategoryTest.php
```

### Test Results (as of 2025-12-19)

- **Total Tests**: 63 (43 product + 20 category)
- **Passing**: 56 (88.9%)
- **Failing**: 7 (all due to missing GD extension - environmental issue)
- **Coverage**: All 19 requirements tested

### GD Extension Note

7 tests require the PHP GD extension for image processing:

- `test_admin_can_create_product_with_images`
- `test_create_product_validates_max_5_images`
- `test_create_product_validates_image_size`
- `test_update_product_can_add_images`
- `test_admin_can_create_category_with_image`
- `test_create_category_validates_image_size`
- `test_update_category_can_change_image`

See [PHP Requirements](../deployment/php-requirements.md) for installation instructions.

---

## Rate Limiting

All API endpoints are protected by Laravel's default rate limiting:

- **Limit**: 60 requests per minute per IP
- **Header**: `X-RateLimit-Remaining`

---

## Examples

### cURL Examples

#### Create Product

```bash
curl -X POST http://localhost/api/v1/admin/products \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: <token>" \
  --cookie "laravel_session=<session>" \
  -d '{
    "name": "Premium Beef Ribeye",
    "category_id": 2,
    "price_aud": 45.99,
    "stock": 25,
    "unit": "kg"
  }'
```

#### Search Products

```bash
curl "http://localhost/api/v1/admin/products?search=beef&status=active" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: <token>" \
  --cookie "laravel_session=<session>"
```

#### Adjust Stock

```bash
curl -X POST http://localhost/api/v1/admin/products/1/adjust-stock \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: <token>" \
  --cookie "laravel_session=<session>" \
  -d '{
    "quantity": 20,
    "type": "increase",
    "reason": "New stock arrived"
  }'
```

#### Export Products to PDF

```bash
curl "http://localhost/api/v1/admin/products/export?status=active" \
  -H "X-XSRF-TOKEN: <token>" \
  --cookie "laravel_session=<session>" \
  -o products-export.pdf
```

### JavaScript (Axios) Examples

#### Create Product with Images

```javascript
const formData = new FormData();
formData.append("name", "Premium Beef Ribeye");
formData.append("category_id", 2);
formData.append("price_aud", 45.99);
formData.append("stock", 25);
formData.append("unit", "kg");
formData.append("images[]", imageFile1);
formData.append("images[]", imageFile2);

const response = await axios.post("/api/v1/admin/products", formData, {
  headers: {
    "Content-Type": "multipart/form-data",
  },
});
```

#### Get Products with Filters

```javascript
const response = await axios.get("/api/v1/admin/products", {
  params: {
    search: "beef",
    category_id: 2,
    status: "active",
    stock_status: "in_stock",
    per_page: 20,
  },
});
```

---

## Support

For issues or questions:

1. Check the [Module Documentation](./README.md)
2. Review [PHP Requirements](../deployment/php-requirements.md)
3. Check Laravel logs: `storage/logs/laravel.log`
4. Contact development team

---

## Version History

- **v1.0.0** (2025-12-19): Initial API documentation for Phase 7
  - 12 product endpoints
  - 7 category endpoints
  - Full CRUD operations
  - Stock management
  - Image upload and management
  - PDF export functionality

---

## Related Documentation

- [Module Overview](./README.md)
- [PHP Requirements](../deployment/php-requirements.md)
- [Database Schema](../foundation/erd.md)
- [Authentication](../auth/README.md)
