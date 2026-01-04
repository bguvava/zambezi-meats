# Inventory Management Module

## Overview

The Inventory Management Module (INV-001 to INV-018) provides comprehensive stock tracking, waste management, and inventory reporting capabilities for administrators.

## Requirements Coverage

| ID      | Requirement                     | Status | Implementation                       |
| ------- | ------------------------------- | ------ | ------------------------------------ |
| INV-001 | Create inventory dashboard      | ✅     | `InventoryPage.vue` dashboard tab    |
| INV-002 | Display stock levels table      | ✅     | Stock Levels tab with sortable table |
| INV-003 | Implement stock filtering       | ✅     | Category, status, search filters     |
| INV-004 | Create stock receive form       | ✅     | Receive Stock modal                  |
| INV-005 | Create stock adjustment form    | ✅     | Adjust Stock modal                   |
| INV-006 | Auto-deduct stock on order      | ✅     | Backend OrderController handles      |
| INV-007 | Restore stock on order cancel   | ✅     | Backend OrderController handles      |
| INV-008 | Set minimum stock thresholds    | ✅     | Min Stock modal per product          |
| INV-009 | Create low stock alerts         | ✅     | Alerts tab - Low Stock section       |
| INV-010 | Create out of stock alerts      | ✅     | Alerts tab - Out of Stock section    |
| INV-011 | Display inventory history       | ✅     | History tab with logs                |
| INV-012 | View product inventory detail   | ✅     | Product detail modal                 |
| INV-013 | Create waste management section | ✅     | Waste tab                            |
| INV-014 | Review and approve waste logs   | ✅     | Approve/Reject buttons               |
| INV-015 | Generate inventory report       | ✅     | `getInventoryReport` API             |
| INV-016 | Export inventory data           | ✅     | Export button (JSON format)          |
| INV-017 | Create inventory API endpoints  | ✅     | `InventoryController.php`            |
| INV-018 | Backend and frontend tests      | ✅     | 38 backend + 45 frontend tests       |

## Architecture

### Backend

#### Controller

- **File**: `backend/app/Http/Controllers/Api/V1/InventoryController.php`
- **Routes**: Protected under `/api/v1/admin/inventory/*`

#### Models

- `Product` - Main inventory items with `stock`, `meta.min_stock`
- `InventoryLog` - Stock movement history
- `WasteLog` - Waste tracking with approval workflow

#### Database Tables

- `products` - Stock levels and thresholds
- `inventory_logs` - Movement history
- `waste_logs` - Waste entries with approval

### Frontend

#### Store

- **File**: `frontend/src/stores/adminInventory.js`
- **Actions**: Dashboard, inventory, history, alerts, waste, reports

#### Page

- **File**: `frontend/src/pages/admin/InventoryPage.vue`
- **Route**: `/admin/inventory`

#### Tabs

1. **Dashboard** - Overview stats, recent movements
2. **Stock Levels** - Full inventory with filters
3. **History** - All stock movements
4. **Alerts** - Low stock and out of stock alerts
5. **Waste** - Waste entries with approval

## API Endpoints

### Dashboard & Overview

```
GET /api/v1/admin/inventory/dashboard
```

Returns total products, low/out of stock counts, waste summary, recent movements.

### Inventory List

```
GET /api/v1/admin/inventory
Query: category_id, status, search, sort_by, sort_dir, per_page, page
```

### Product Detail

```
GET /api/v1/admin/inventory/{productId}
```

Returns product info, history, and waste logs.

### Stock Operations

```
POST /api/v1/admin/inventory/receive
Body: { product_id, quantity, supplier?, notes? }

POST /api/v1/admin/inventory/adjust
Body: { product_id, new_quantity, reason, notes? }

PUT /api/v1/admin/inventory/{productId}/min-stock
Body: { min_stock }
```

### History & Alerts

```
GET /api/v1/admin/inventory/history
Query: product_id, type, start_date, end_date, user_id

GET /api/v1/admin/inventory/low-stock
GET /api/v1/admin/inventory/alerts
```

### Waste Management

```
GET /api/v1/admin/waste
Query: reason, product_id, approved, start_date, end_date

PUT /api/v1/admin/waste/{id}/approve
Body: { approved: boolean, notes? }
```

### Reports & Export

```
GET /api/v1/admin/inventory/report
Query: start_date, end_date

GET /api/v1/admin/inventory/export
```

## Stock Status Logic

| Status       | Condition                          |
| ------------ | ---------------------------------- |
| Out of Stock | `stock <= 0`                       |
| Low Stock    | `stock > 0 AND stock <= min_stock` |
| Normal       | `stock > min_stock`                |

Default `min_stock` threshold: **10 units**

## Inventory Log Types

- `addition` - Stock received from supplier
- `deduction` - Stock sold or used
- `adjustment` - Manual correction

## Waste Reasons

- `expired` - Past expiration date
- `damaged` - Physical damage
- `spoiled` - Quality degradation
- `quality` - Quality issue
- `other` - Other reasons

## Testing

### Backend Tests

```bash
cd backend
php artisan test --filter=InventoryControllerTest
# 38 tests, 159 assertions
```

### Frontend Tests

```bash
cd frontend
npm run test -- adminInventory
# 45+ test cases
```

## Usage Examples

### Receiving Stock

1. Click "Receive Stock" button
2. Select product
3. Enter quantity and optional supplier/notes
4. Submit

### Adjusting Stock

1. Find product in Stock Levels tab
2. Click Edit (pencil) icon
3. Enter new quantity
4. Select reason
5. Submit

### Setting Min Stock Threshold

1. Find product in Stock Levels tab
2. Click Alert (triangle) icon
3. Enter new threshold
4. Submit

### Approving Waste

1. Go to Waste tab
2. Find pending entry
3. Click checkmark to approve or X to reject

## Security

- All endpoints require authentication
- Admin role required for all operations
- Actions logged to `inventory_logs` and `activity_logs`

## Color Reference

- Normal Stock: `bg-green-100 text-green-800`
- Low Stock: `bg-yellow-100 text-yellow-800`
- Out of Stock: `bg-red-100 text-red-800`
- Addition: `bg-green-100 text-green-800`
- Deduction: `bg-red-100 text-red-800`
- Adjustment: `bg-blue-100 text-blue-800`
