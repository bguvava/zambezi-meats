# Delivery Management Module

## Overview

The Delivery Management Module provides comprehensive tools for managing deliveries, delivery zones, and delivery settings for Zambezi Meats. This module covers the entire delivery lifecycle from assignment to proof of delivery.

## Requirements Coverage

| Req ID  | Requirement            | Status              |
| ------- | ---------------------- | ------------------- |
| DEL-001 | Dashboard Overview     | ✅ Complete         |
| DEL-002 | All Deliveries List    | ✅ Complete         |
| DEL-003 | Delivery Filters       | ✅ Complete         |
| DEL-004 | Delivery Detail View   | ✅ Complete         |
| DEL-005 | Delivery Assignment    | ✅ Complete         |
| DEL-006 | Delivery Reassignment  | ✅ Complete         |
| DEL-007 | Proof of Delivery View | ✅ Complete         |
| DEL-008 | POD Download           | ✅ Complete         |
| DEL-009 | Issue Resolution       | ✅ Complete         |
| DEL-010 | Zone Management List   | ✅ Complete         |
| DEL-011 | Create Delivery Zone   | ✅ Complete         |
| DEL-012 | Update Delivery Zone   | ✅ Complete         |
| DEL-013 | Delete Delivery Zone   | ✅ Complete         |
| DEL-014 | Delivery Settings      | ✅ Complete         |
| DEL-015 | Map View               | ✅ Backend Complete |
| DEL-016 | Delivery Reports       | ✅ Backend Complete |
| DEL-017 | Export Deliveries      | ✅ Complete         |
| DEL-018 | API Endpoints          | ✅ Complete         |
| DEL-019 | Tests                  | ✅ Complete         |

## Architecture

### Backend Components

#### Controller

- **File**: `backend/app/Http/Controllers/Api/V1/DeliveryController.php`
- **Endpoints**:
  - `GET /api/v1/admin/deliveries/dashboard` - Dashboard stats
  - `GET /api/v1/admin/deliveries` - List deliveries with filters
  - `GET /api/v1/admin/deliveries/{id}` - Single delivery details
  - `POST /api/v1/admin/deliveries/{id}/assign` - Assign/reassign delivery
  - `GET /api/v1/admin/deliveries/{id}/pod` - Get proof of delivery
  - `GET /api/v1/admin/deliveries/{id}/pod/download` - Download POD PDF
  - `POST /api/v1/admin/deliveries/{id}/resolve-issue` - Resolve delivery issue
  - `GET /api/v1/admin/delivery-zones` - List zones
  - `POST /api/v1/admin/delivery-zones` - Create zone
  - `PUT /api/v1/admin/delivery-zones/{id}` - Update zone
  - `DELETE /api/v1/admin/delivery-zones/{id}` - Delete zone
  - `GET /api/v1/admin/delivery-settings` - Get settings
  - `PUT /api/v1/admin/delivery-settings` - Update settings
  - `GET /api/v1/admin/deliveries/map-data` - Get map data
  - `GET /api/v1/admin/deliveries/report` - Get delivery report
  - `GET /api/v1/admin/deliveries/export` - Export deliveries

#### Tests

- **File**: `backend/tests/Feature/Api/V1/DeliveryControllerTest.php`
- **Coverage**: 39 tests, 156 assertions
- **Status**: All passing

### Frontend Components

#### Store

- **File**: `frontend/src/stores/adminDelivery.js`
- **State**:
  - `dashboard` - Dashboard statistics
  - `deliveries` - List of deliveries
  - `pagination` - Pagination state
  - `filters` - Active filters
  - `currentDelivery` - Selected delivery details
  - `proofOfDelivery` - POD data for current delivery
  - `zones` - Delivery zones list
  - `settings` - Delivery fee settings
  - `mapData` - Map visualization data
  - `report` - Report data
  - `staffList` - Available delivery staff
  - `loading` - Loading states
  - `error` - Error message

#### Page Component

- **File**: `frontend/src/pages/admin/DeliveryPage.vue`
- **Tabs**:
  1. **Dashboard** - Stats cards and recent deliveries
  2. **All Deliveries** - Filterable deliveries table
  3. **Zones** - Zone management CRUD
  4. **Settings** - Delivery fee configuration

#### Service Layer

- **File**: `frontend/src/services/dashboard.js`
- **Methods**:
  - `getDeliveryDashboard()` - Fetch dashboard data
  - `getDeliveries(params)` - Fetch deliveries list
  - `getDelivery(id)` - Fetch single delivery
  - `assignDelivery(id, data)` - Assign delivery to staff
  - `getDeliveryPod(id)` - Fetch proof of delivery
  - `downloadDeliveryPod(id)` - Download POD as PDF
  - `resolveDeliveryIssue(id, data)` - Mark issue resolved
  - `getDeliveryZones()` - Fetch all zones
  - `createDeliveryZone(data)` - Create new zone
  - `updateDeliveryZone(id, data)` - Update zone
  - `deleteDeliveryZone(id)` - Delete zone
  - `getDeliverySettings()` - Fetch settings
  - `updateDeliverySettings(data)` - Update settings
  - `getDeliveryMapData(params)` - Fetch map data
  - `getDeliveryReport(params)` - Fetch report
  - `exportDeliveries(params)` - Export data

#### Tests

- **File**: `frontend/src/stores/__tests__/adminDelivery.test.js`
- **Coverage**: Full store testing with 50+ test cases

## Data Models

### Delivery

```javascript
{
  id: number,
  order_id: number,
  order_number: string,
  customer_name: string,
  customer_phone: string,
  address: string,
  postcode: string,
  zone_id: number | null,
  assigned_staff_id: number | null,
  assigned_staff: {
    id: number,
    name: string
  } | null,
  status: 'pending' | 'assigned' | 'out_for_delivery' | 'delivered' | 'failed' | 'cancelled',
  scheduled_date: string,
  delivered_at: string | null,
  has_pod: boolean,
  has_issue: boolean,
  issue_description: string | null,
  notes: string | null,
  created_at: string,
  updated_at: string
}
```

### Delivery Zone

```javascript
{
  id: number,
  name: string,
  postcodes: string[],
  min_order: number,
  delivery_fee: number,
  is_active: boolean,
  created_at: string,
  updated_at: string
}
```

### Delivery Settings

```javascript
{
  free_delivery_threshold: number,  // Default: 100
  per_km_rate: number,              // Default: 0.15
  base_delivery_fee: number,        // Default: 9.95
  max_delivery_distance: number     // Default: 50 km
}
```

### Proof of Delivery

```javascript
{
  id: number,
  delivery_id: number,
  signature_url: string | null,
  photo_url: string | null,
  receiver_name: string | null,
  delivered_at: string,
  notes: string | null
}
```

## UI Features

### Dashboard Tab (DEL-001)

- **Stats Cards**: Today's deliveries, Pending, In Progress, Completed, Issues
- **Recent Deliveries Table**: Last 10 deliveries with quick status view

### Deliveries Tab (DEL-002, DEL-003)

- **Search**: Full-text search across order numbers and customer names
- **Status Filter**: Filter by delivery status
- **Date Range Filter**: Filter by scheduled date range
- **Table Actions**:
  - View details (modal)
  - Assign/reassign delivery
  - View proof of delivery
  - Resolve issue

### Zones Tab (DEL-010 to DEL-013)

- **Zone Table**: List all zones with postcodes, fees, status
- **Add Zone Modal**: Create new zone with postcodes
- **Edit Zone Modal**: Modify zone details
- **Delete Action**: Remove zone with confirmation

### Settings Tab (DEL-014)

- **Free Delivery Threshold**: Minimum order for free delivery
- **Base Delivery Fee**: Default fee for orders below threshold
- **Per-Kilometer Rate**: Fee for out-of-zone deliveries
- **Maximum Distance**: Delivery radius limit

## Modals

### Delivery Detail Modal

- Full delivery information
- Order and customer details
- Assigned staff
- Notes and status history

### Assignment Modal

- Staff selection dropdown
- Optional reason field (for reassignments)

### Proof of Delivery Modal

- Signature display
- Photo display
- Receiver name
- Delivery timestamp
- Download PDF button

### Issue Resolution Modal

- Resolution text field
- Status selection (resolved, rescheduled, refunded)

### Zone Form Modal

- Zone name
- Postcodes (comma-separated)
- Minimum order amount
- Delivery fee
- Active status toggle

## Status Colors

| Status           | Color  |
| ---------------- | ------ |
| Pending          | Yellow |
| Assigned         | Blue   |
| Out for Delivery | Purple |
| Delivered        | Green  |
| Failed           | Red    |
| Cancelled        | Gray   |

## Delivery Fee Calculation

1. **Within Zone**: Zone-specific fee or free if above threshold
2. **Outside All Zones**: Base fee + (distance × per-km rate)
3. **Free Delivery**: When order total ≥ free delivery threshold AND within zone

### Example

```
Order Total: $150
Zone: Engadine Local (min: $100, fee: $0)
Result: FREE DELIVERY

Order Total: $80
Zone: Extended Area (min: $100, fee: $9.95)
Result: $9.95 delivery fee

Order Total: $200
Location: 30km from store (outside zones)
Result: Base ($9.95) + 30km × $0.15 = $14.45
```

## API Endpoint Details

### GET /api/v1/admin/deliveries

Query parameters:

- `page` (number) - Page number
- `per_page` (number) - Items per page
- `search` (string) - Search term
- `status` (string) - Status filter
- `staff_id` (number) - Staff filter
- `zone_id` (number) - Zone filter
- `date_from` (string) - Start date
- `date_to` (string) - End date

### POST /api/v1/admin/deliveries/{id}/assign

Request body:

```json
{
  "staff_id": 5,
  "reason": "Original staff unavailable",
  "is_reassignment": true
}
```

### POST /api/v1/admin/delivery-zones

Request body:

```json
{
  "name": "Engadine Local",
  "postcodes": ["2233", "2234", "2232"],
  "min_order": 100,
  "delivery_fee": 0,
  "is_active": true
}
```

## Testing

### Backend Tests

```bash
cd backend
php artisan test --filter=DeliveryControllerTest
```

Result: 39 passed, 156 assertions

### Frontend Tests

```bash
cd frontend
npm run test -- src/stores/__tests__/adminDelivery.test.js
```

## File Structure

```
backend/
├── app/Http/Controllers/Api/V1/
│   └── DeliveryController.php
├── tests/Feature/Api/V1/
│   └── DeliveryControllerTest.php
└── database/migrations/
    ├── create_deliveries_table.php
    └── create_delivery_zones_table.php

frontend/
├── src/
│   ├── pages/admin/
│   │   └── DeliveryPage.vue
│   ├── stores/
│   │   ├── adminDelivery.js
│   │   └── __tests__/
│   │       └── adminDelivery.test.js
│   ├── services/
│   │   └── dashboard.js (delivery methods added)
│   └── layouts/
│       └── AdminLayout.vue (navigation updated)
└── docs/delivery/
    └── README.md

docs/
└── delivery/
    └── README.md
```

## Navigation

Admin sidebar includes:

- Dashboard
- Users
- Products
- Categories
- Orders
- Inventory
- **Deliveries** (New)
- Reports
- Settings

Route: `/admin/deliveries`

## Future Enhancements

1. **Real-time Tracking**: WebSocket integration for live delivery status
2. **Route Optimization**: Integration with mapping API for optimal routes
3. **Driver App**: Mobile app for delivery staff with POD capture
4. **SMS Notifications**: Customer notifications on status changes
5. **Delivery Windows**: Time slot selection for customers
6. **Auto-Assignment**: Algorithm-based staff assignment
