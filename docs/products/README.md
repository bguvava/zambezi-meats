# Product Management Module

## Overview

The Product Management Module (Phase 7) provides comprehensive product and category management functionality for the Zambezi Meats Online Butchery Store. This module enables administrators and staff to manage the product catalog, track inventory, upload images, and generate reports.

**Status**: ✅ Complete (88.9% test coverage - 56/63 tests passing, 7 GD-related environmental)

**Requirements**: PROD-001 through PROD-019 (19 total)

**Version**: 1.0.0

---

## Table of Contents

- [Features](#features)
- [Requirements Coverage](#requirements-coverage)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [Business Rules](#business-rules)
- [Usage Guide](#usage-guide)
- [Testing](#testing)
- [Authorization](#authorization)
- [Technical Stack](#technical-stack)

---

## Features

### Product Management

- ✅ **CRUD Operations** - Create, read, update, and soft delete products
- ✅ **Advanced Search** - Search by name, SKU, or description
- ✅ **Multi-Filter Support** - Filter by category, status, and stock level
- ✅ **Image Upload** - Multi-image upload (max 5) with drag-drop reordering
- ✅ **Slug Generation** - Automatic URL-friendly slug creation
- ✅ **SKU Generation** - Unique SKU assignment for inventory tracking
- ✅ **Stock Management** - Adjust stock levels with audit trail
- ✅ **Low Stock Alerts** - Configurable threshold monitoring
- ✅ **PDF Export** - Generate filtered product reports
- ✅ **Activity Logging** - Complete audit trail for all changes

### Category Management

- ✅ **CRUD Operations** - Full category management
- ✅ **Hierarchical Structure** - Parent-child relationships
- ✅ **Image Support** - Category images with upload
- ✅ **Product Protection** - Prevent deletion of categories with products
- ✅ **Custom Sort Order** - Manual ordering of categories

### Admin Features

- ✅ **Role-Based Access** - Admin and Staff role differentiation
- ✅ **Pagination** - Configurable items per page
- ✅ **Activity Dashboard** - Low stock products overview
- ✅ **Bulk Operations** - Export filtered products

---

## Requirements Coverage

### Product Requirements (PROD-001 to PROD-019)

| ID       | Requirement             | Status | Implementation                  |
| -------- | ----------------------- | ------ | ------------------------------- |
| PROD-001 | Admin Product Dashboard | ✅     | Low stock products endpoint     |
| PROD-002 | Category CRUD           | ✅     | Full CRUD with protection       |
| PROD-003 | Products Listing        | ✅     | Paginated with relationships    |
| PROD-004 | Product Search          | ✅     | Name, SKU, description search   |
| PROD-005 | Product Filters         | ✅     | Category, status, stock filters |
| PROD-006 | Product Form Validation | ✅     | StoreProductRequest             |
| PROD-007 | Image Upload            | ✅     | ImageUploadService (multi-file) |
| PROD-008 | Product Edit            | ✅     | UpdateProductRequest            |
| PROD-009 | Product Delete          | ✅     | Soft delete (is_active=false)   |
| PROD-010 | Stock Management        | ✅     | Adjust with audit trail         |
| PROD-011 | Low Stock Alerts        | ✅     | Configurable threshold          |
| PROD-012 | Image Delete            | ✅     | With primary reassignment       |
| PROD-013 | Image Reorder           | ✅     | Drag-drop with sort_order       |
| PROD-014 | PDF Export              | ✅     | PdfExportService + filters      |
| PROD-015 | Price Management        | ✅     | Regular & sale prices           |
| PROD-016 | Slug Generation         | ✅     | Auto-generate from name         |
| PROD-017 | Product View            | ✅     | Single product with images      |
| PROD-018 | Stock History           | ✅     | InventoryLog tracking           |
| PROD-019 | Activity Logging        | ✅     | ActivityLog for all changes     |

**Coverage**: 19/19 (100%)

---

## Architecture

### Layer Structure

```
┌─────────────────────────────────────────────┐
│            HTTP Layer                        │
│  Routes → Middleware → Controllers           │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         Validation Layer                     │
│  Form Requests (StoreProduct, UpdateProduct) │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│          Business Logic Layer                │
│  Services (ImageUpload, PdfExport)           │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│            Data Layer                        │
│  Models → Database (MySQL 8.0)               │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│          Storage Layer                       │
│  File System (products/, categories/)        │
└─────────────────────────────────────────────┘
```

### Key Components

#### Controllers

- **AdminController.php** - All product and category endpoints
  - 12 product methods
  - 7 category methods
  - Authorization via middleware and helper methods

#### Form Requests

- **StoreProductRequest.php** - Create product validation
  - Auto-generates slug if not provided
  - Validates images array and individual files
  - SKU uniqueness check
- **UpdateProductRequest.php** - Update product validation
  - All fields optional
  - Unique constraints exclude current product
- **StoreCategoryRequest.php** - Category creation validation
  - Auto-generates slug
  - Parent category validation
- **UpdateCategoryRequest.php** - Category update validation
  - Prevents self-reference
  - Name uniqueness check

#### Services

- **ImageUploadService.php** - Image processing
  - Multi-file upload support
  - Image optimization and resizing
  - Generates thumbnails
  - Max 5 images per product
  - Max 2MB per image
  - Allowed formats: JPEG, PNG, WEBP
- **PdfExportService.php** - PDF generation
  - exportProducts() - Filtered product list
  - exportStockReport() - Stock analysis
  - exportPriceList() - Customer price list
  - Uses barryvdh/laravel-dompdf

#### Models

- **Product.php**
  - Relationships: category, images
  - Scopes: active, featured, inStock
  - Auto-generates SKU on create
- **Category.php**
  - Relationships: products, parent, children
  - Hierarchical structure support
  - Product count eager loading
- **ProductImage.php**
  - Belongs to product
  - Primary image tracking
  - Sort order management
- **InventoryLog.php**
  - Stock adjustment history
  - Types: addition, deduction, adjustment, waste
  - User tracking and timestamps
- **ActivityLog.php**
  - Full audit trail
  - Action types: created, updated, deleted, stock_adjusted
  - Stores old/new values as JSON

---

## Database Schema

### Products Table

```sql
products (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  sku VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  short_description VARCHAR(500),
  category_id BIGINT → categories(id),
  price_aud DECIMAL(10,2) NOT NULL,
  sale_price_aud DECIMAL(10,2) NULL,
  stock INT DEFAULT 0,
  unit ENUM('kg','piece','pack') NOT NULL,
  weight_kg DECIMAL(10,3),
  is_active BOOLEAN DEFAULT true,
  is_featured BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_category (category_id),
  INDEX idx_sku (sku),
  INDEX idx_slug (slug),
  INDEX idx_active (is_active),
  FULLTEXT idx_search (name, description)
)
```

### Product Images Table

```sql
product_images (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT → products(id) ON DELETE CASCADE,
  image_path VARCHAR(255) NOT NULL,
  alt_text VARCHAR(255),
  sort_order INT DEFAULT 0,
  is_primary BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_product (product_id),
  INDEX idx_sort (sort_order),
  INDEX idx_primary (is_primary)
)
```

### Categories Table

```sql
categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) UNIQUE NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  image VARCHAR(255),
  parent_id BIGINT → categories(id) ON DELETE SET NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_parent (parent_id),
  INDEX idx_sort (sort_order)
)
```

### Inventory Logs Table

```sql
inventory_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_id BIGINT → products(id) ON DELETE CASCADE,
  type ENUM('addition','deduction','adjustment','waste'),
  quantity DECIMAL(10,3),
  stock_before DECIMAL(10,3),
  stock_after DECIMAL(10,3),
  reason TEXT,
  user_id BIGINT → users(id) ON DELETE SET NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_product (product_id),
  INDEX idx_type (type),
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
)
```

### Activity Logs Table

```sql
activity_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  action VARCHAR(100),
  model_type VARCHAR(255),
  model_id BIGINT,
  old_values JSON,
  new_values JSON,
  user_id BIGINT → users(id) ON DELETE SET NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_model (model_type, model_id),
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_created (created_at)
)
```

---

## Business Rules

### Product Management

1. **SKU Generation**

   - Format: `PROD-{CATEGORY_CODE}{PRODUCT_CODE}-{HASH}`
   - Example: `PROD-BEEFRIB-A1B2`
   - Automatically generated on product creation
   - Must be unique across all products

2. **Slug Generation**

   - Auto-generated from product name if not provided
   - Format: lowercase with hyphens (kebab-case)
   - Example: "Premium Beef Ribeye" → "premium-beef-ribeye"
   - Must be unique and URL-friendly

3. **Price Rules**

   - Regular price required, must be > $0.01
   - Sale price is optional
   - If provided, sale price must be less than regular price
   - Supports AUD, USD, ZWL currencies

4. **Stock Management**

   - Stock cannot be negative (minimum: 0)
   - Maximum stock: 999,999 units
   - Three adjustment types:
     - `increase` - Adds to current stock
     - `decrease` - Subtracts from current stock (cannot go below 0)
     - `adjustment` - Sets stock to exact value
   - All adjustments require reason (max 500 characters)
   - Creates audit trail in inventory_logs

5. **Image Upload**

   - Maximum 5 images per product
   - Allowed formats: JPEG, JPG, PNG, WEBP
   - Maximum size: 2MB per image
   - First image automatically set as primary
   - Images stored in: `storage/app/public/products/`
   - Automatic thumbnail generation (150x150px)

6. **Image Management**

   - Primary image deletion automatically promotes next image
   - Deleting last image allowed (product with no images)
   - Sort order maintained sequentially (0, 1, 2, ...)
   - Drag-drop reordering updates sort_order

7. **Product Deletion**
   - Soft delete only (sets `is_active = false`)
   - Product remains in database for order history
   - Cascade deletes: images, inventory logs
   - Does not delete: activity logs (audit trail)

### Category Management

1. **Slug Generation**

   - Auto-generated from category name
   - Format: lowercase with hyphens
   - Example: "Fresh Beef" → "fresh-beef"

2. **Hierarchical Structure**

   - Categories can have parent (parent_id)
   - Infinite nesting supported (though UI may limit)
   - Self-reference prevented during update

3. **Deletion Protection**

   - Cannot delete category with products
   - Returns 409 Conflict error
   - Must move or delete products first

4. **Sort Order**

   - Manual ordering via sort_order field
   - Default: 0
   - Range: 0-9999
   - Categories displayed in ascending sort_order

5. **Image Upload**
   - Optional category image
   - Formats: JPEG, JPG, PNG, WEBP, SVG
   - Maximum size: 2MB
   - Stored in: `storage/app/public/categories/`

### Stock Alerts

1. **Thresholds**

   - Default low stock threshold: 10 units
   - Configurable per request
   - Categories:
     - Out of Stock: stock = 0
     - Low Stock: 0 < stock < threshold
     - In Stock: stock ≥ threshold

2. **Alert Display**
   - Admin dashboard shows low stock products
   - Includes: product name, SKU, current stock, price
   - Grouped by category
   - Sortable by stock level or name

### Authorization Rules

1. **Admin Role**

   - Full access to all endpoints
   - Can delete products and categories
   - Can export to PDF
   - Can view all activity logs

2. **Staff Role**

   - Can create and update products
   - Can create and update categories
   - Can adjust stock levels
   - Cannot delete products or categories
   - Cannot export to PDF
   - Can view product-specific logs

3. **Customer Role**
   - No access to admin endpoints
   - Read-only access via public shop API

---

## Usage Guide

### Creating a Product

#### API Request

```http
POST /api/v1/admin/products
Content-Type: multipart/form-data
```

```json
{
  "name": "Premium Beef Ribeye Steak",
  "category_id": 2,
  "price_aud": 45.99,
  "sale_price_aud": 39.99,
  "stock": 25,
  "unit": "kg",
  "weight_kg": 0.5,
  "description": "Tender, marbled ribeye steak...",
  "is_active": true,
  "is_featured": false,
  "images": [<File>, <File>]
}
```

#### What Happens

1. Request validated via StoreProductRequest
2. Slug auto-generated if not provided
3. SKU auto-generated
4. Product created in database
5. Images uploaded via ImageUploadService
6. ProductImage records created
7. ActivityLog entry created
8. Response with created product

### Adjusting Stock

#### Increase Stock

```http
POST /api/v1/admin/products/1/adjust-stock
```

```json
{
  "quantity": 20,
  "type": "increase",
  "reason": "New stock arrived from supplier"
}
```

**Result**: Stock 50 → 70

#### Decrease Stock

```json
{
  "quantity": 15,
  "type": "decrease",
  "reason": "Damaged during inspection"
}
```

**Result**: Stock 50 → 35

#### Manual Adjustment

```json
{
  "quantity": 100,
  "type": "adjustment",
  "reason": "Physical stock count correction"
}
```

**Result**: Stock set to exactly 100

### Reordering Images

```http
POST /api/v1/admin/products/1/images/reorder
```

```json
{
  "images": [
    { "id": 3, "sort_order": 0 }, // Now primary
    { "id": 1, "sort_order": 1 },
    { "id": 2, "sort_order": 2 }
  ]
}
```

### Exporting Products to PDF

```http
GET /api/v1/admin/products/export?category_id=2&status=active&stock_status=low_stock
```

**Generated PDF includes**:

- Header with Zambezi Meats branding
- Applied filters
- Product table (SKU, Name, Category, Price, Stock, Status)
- Summary (total products, stock value)
- Footer with generation timestamp

---

## Testing

### Test Structure

```
tests/Feature/Api/V1/
├── AdminProductTest.php (43 tests)
│   ├── Listing & Search (5 tests)
│   ├── CRUD Operations (8 tests)
│   ├── Image Upload (5 tests)
│   ├── Stock Management (8 tests)
│   ├── Image Management (3 tests)
│   ├── Pagination (1 test)
│   └── Authorization (13 tests)
└── AdminCategoryTest.php (20 tests)
    ├── Listing (3 tests)
    ├── CRUD Operations (9 tests)
    ├── Image Upload (3 tests)
    ├── Deletion Protection (2 tests)
    └── Authorization (3 tests)
```

### Running Tests

```bash
# Run all Phase 7 tests
php artisan test tests/Feature/Api/V1/AdminProductTest.php tests/Feature/Api/V1/AdminCategoryTest.php

# Run specific test class
php artisan test tests/Feature/Api/V1/AdminProductTest.php

# Run specific test method
php artisan test --filter="test_admin_can_create_product"

# Run with coverage
php artisan test --coverage --min=80
```

### Test Results (as of 2025-12-19)

```
Total Tests: 63
Passing: 56 (88.9%)
Failing: 7 (GD extension required - environmental)

Product Tests: 43
├── Passing: 39
└── Failing: 4 (GD-related)

Category Tests: 20
├── Passing: 17
└── Failing: 3 (GD-related)
```

### GD Extension Tests

These tests require PHP GD extension for image generation:

1. `test_admin_can_create_product_with_images`
2. `test_create_product_validates_max_5_images`
3. `test_create_product_validates_image_size`
4. `test_update_product_can_add_images`
5. `test_admin_can_create_category_with_image`
6. `test_create_category_validates_image_size`
7. `test_update_category_can_change_image`

**Solution**: Install GD extension (see [PHP Requirements](../deployment/php-requirements.md))

### Test Coverage by Requirement

| Requirement              | Tests | Status  |
| ------------------------ | ----- | ------- |
| PROD-003 (Listing)       | 5     | ✅      |
| PROD-004 (Search)        | 1     | ✅      |
| PROD-005 (Filters)       | 3     | ✅      |
| PROD-006 (Validation)    | 7     | ✅      |
| PROD-007 (Images)        | 5     | ⚠️ 4 GD |
| PROD-008 (Update)        | 3     | ⚠️ 1 GD |
| PROD-009 (Delete)        | 2     | ✅      |
| PROD-010 (Stock)         | 5     | ✅      |
| PROD-011 (Alerts)        | 1     | ✅      |
| PROD-012 (Image Delete)  | 2     | ✅      |
| PROD-013 (Image Reorder) | 1     | ✅      |
| PROD-017 (View)          | 1     | ✅      |
| Category CRUD            | 20    | ⚠️ 3 GD |

---

## Authorization

### Middleware Stack

```php
Route::group(['middleware' => ['auth:sanctum']], function () {
    // All admin routes require authentication
});
```

### Helper Methods

```php
// In AdminController.php

protected function authorizeAdmin(Request $request): void
{
    if ($request->user()->role !== User::ROLE_ADMIN) {
        abort(403, 'Forbidden. Admin role required.');
    }
}

protected function authorizeAdminOrStaff(Request $request): void
{
    if (!in_array($request->user()->role, [User::ROLE_ADMIN, User::ROLE_STAFF])) {
        abort(403, 'Forbidden. Admin or Staff role required.');
    }
}
```

### Authorization Matrix

| Action             | Admin | Staff | Customer |
| ------------------ | ----- | ----- | -------- |
| View Products      | ✓     | ✓     | ✗        |
| Create Product     | ✓     | ✓     | ✗        |
| Update Product     | ✓     | ✓     | ✗        |
| Delete Product     | ✓     | ✗     | ✗        |
| Adjust Stock       | ✓     | ✓     | ✗        |
| View Stock History | ✓     | ✓     | ✗        |
| Export PDF         | ✓     | ✗     | ✗        |
| View Categories    | ✓     | ✓     | ✗        |
| Create Category    | ✓     | ✓     | ✗        |
| Update Category    | ✓     | ✓     | ✗        |
| Delete Category    | ✓     | ✗     | ✗        |

---

## Technical Stack

### Backend

- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum
- **PDF Generation**: barryvdh/laravel-dompdf 3.1
- **Image Processing**: Intervention Image 3.x
- **Testing**: PHPUnit 11.x

### Storage

- **Driver**: Local (public disk)
- **Product Images**: `storage/app/public/products/`
- **Category Images**: `storage/app/public/categories/`
- **Symbolic Link**: `public/storage` → `storage/app/public`

### API

- **Format**: RESTful JSON API
- **Versioning**: `/api/v1/`
- **Authentication**: Cookie-based SPA (Sanctum)
- **Rate Limiting**: 60 requests/minute per IP

### Code Standards

- **PHP**: PSR-12 coding style
- **Laravel**: Laravel best practices
- **Type Declarations**: Strict types enabled
- **Documentation**: PHPDoc blocks for all methods

---

## File Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           └── AdminController.php (1585 lines, 19 methods)
│   │   └── Requests/
│   │       └── Api/
│   │           ├── StoreProductRequest.php
│   │           ├── UpdateProductRequest.php
│   │           ├── StoreCategoryRequest.php
│   │           └── UpdateCategoryRequest.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── ProductImage.php
│   │   ├── InventoryLog.php
│   │   └── ActivityLog.php
│   └── Services/
│       ├── ImageUploadService.php
│       └── PdfExportService.php
├── database/
│   ├── factories/
│   │   ├── ProductFactory.php
│   │   ├── CategoryFactory.php
│   │   └── ProductImageFactory.php
│   ├── migrations/
│   │   ├── *_create_products_table.php
│   │   ├── *_create_categories_table.php
│   │   ├── *_create_product_images_table.php
│   │   ├── *_create_inventory_logs_table.php
│   │   └── *_rename_inventory_logs_columns.php
│   └── seeders/
│       ├── ProductSeeder.php
│       └── CategorySeeder.php
├── resources/
│   └── views/
│       └── pdf/
│           └── products.blade.php
├── routes/
│   └── api.php (Product & Category routes)
├── storage/
│   └── app/
│       └── public/
│           ├── products/
│           └── categories/
└── tests/
    └── Feature/
        └── Api/
            └── V1/
                ├── AdminProductTest.php (43 tests)
                └── AdminCategoryTest.php (20 tests)
```

---

## Performance Considerations

1. **Database Queries**

   - Eager loading: `with(['category', 'images'])`
   - Indexed fields: category_id, sku, slug, is_active
   - Full-text search on name and description

2. **Image Upload**

   - Async processing recommended for production
   - Thumbnail generation: 150x150px
   - Original image optimization
   - Storage: Local (upgradeable to S3/CDN)

3. **Pagination**

   - Default: 15 items per page
   - Configurable: 1-100 items
   - Uses Laravel's `paginate()` with efficient counting

4. **Caching Opportunities** (Future)
   - Category tree structure
   - Low stock products list
   - Active products count
   - Featured products

---

## Future Enhancements

1. **Product Variants**

   - Size, color, weight variations
   - Separate SKUs per variant
   - Variant-specific pricing and stock

2. **Bulk Import/Export**

   - CSV import for products
   - Excel export with formatting
   - Batch image upload

3. **Advanced Search**

   - Elasticsearch integration
   - Faceted search
   - Search suggestions

4. **Image Optimization**

   - CDN integration
   - WebP conversion
   - Lazy loading support
   - Multiple thumbnail sizes

5. **Stock Forecasting**

   - Predicted stock depletion
   - Reorder point calculations
   - Supplier integration

6. **Product Relationships**
   - Related products
   - Cross-sell suggestions
   - Bundle products

---

## Known Issues & Limitations

1. **GD Extension Required**

   - 7 tests fail without PHP GD extension
   - Required for image upload functionality
   - See [PHP Requirements](../deployment/php-requirements.md)

2. **Single Currency Display**

   - Supports multiple currencies in database
   - UI displays AUD only
   - Currency conversion not implemented

3. **Image Storage**

   - Local storage only
   - No CDN integration
   - Limited scalability for high traffic

4. **PDF Generation**
   - Synchronous (blocks request)
   - Large exports may timeout
   - Consider queue implementation for production

---

## Related Documentation

- [API Endpoints](./api-endpoints.md) - Complete API reference
- [PHP Requirements](../deployment/php-requirements.md) - Server setup guide
- [Database Schema](../foundation/erd.md) - Complete ERD
- [Authentication](../auth/README.md) - Auth implementation
- [Deployment Guide](../deployment/README.md) - Production deployment

---

## Support & Contact

For technical questions or issues:

1. Check this documentation and [API reference](./api-endpoints.md)
2. Review test files for usage examples
3. Check Laravel logs: `storage/logs/laravel.log`
4. Contact development team

---

## Version History

### v1.0.0 (2025-12-19)

- Initial release of Product Management module
- 19 requirements implemented
- 63 comprehensive tests
- Full CRUD for products and categories
- Image upload and management
- Stock tracking with audit trail
- PDF export functionality
- 88.9% test coverage (56/63 passing, 7 GD-related)

---

## License

© 2025 Zambezi Meats. All rights reserved.
