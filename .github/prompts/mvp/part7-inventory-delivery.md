# Zambezi Meats MVP - Part 7: Inventory & Delivery Management

## Module Overview

| Field | Value |
|-------|-------|
| **Module Names** | INVENTORY, DELIVERY |
| **Priority** | P1 - High |
| **Dependencies** | ADMIN, DATABASE |
| **Documentation** | `/docs/inventory/`, `/docs/delivery/` |
| **Tests** | `/tests/inventory/`, `/tests/delivery/` |

This module combines:
- Inventory Management (INVENTORY)
- Delivery Management (DELIVERY)

**Total Requirements: 37**

---

## 7.1 Inventory Management

### Objectives

1. Provide real-time stock level tracking and management
2. Implement stock receive, adjust, and deduct operations
3. Create low stock alerts and notifications
4. Track inventory history with full audit trail
5. Manage waste/damaged goods logging

### Success Criteria

| Criteria | Target |
|----------|--------|
| Stock accuracy | 99.9% |
| Real-time sync | < 1 second |
| Low stock alerts | Triggered automatically |
| Audit trail | 100% of changes logged |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| INV-001 | Create inventory dashboard | As an admin, I want an overview of stock levels | Dashboard with: total products, low stock count, out of stock count, recent movements | Admin |
| INV-002 | Display stock levels table | As an admin, I want to see all product stock | Table: product, category, current stock, min stock, status, last updated | Admin |
| INV-003 | Implement stock filtering | As an admin, I want to find specific stock items | Filters: category, stock status (low/out/normal), search by name | Admin |
| INV-004 | Create stock receive form | As an admin, I want to add incoming stock | Form: product, quantity, supplier (optional), notes, date | Admin |
| INV-005 | Create stock adjustment form | As an admin, I want to correct stock discrepancies | Form: product, current qty, new qty, reason, notes | Admin |
| INV-006 | Auto-deduct stock on order | As an admin, I want stock to decrease on orders | Stock automatically reduced when order status changes to "Preparing" | Admin |
| INV-007 | Restore stock on order cancel | As an admin, I want stock restored for cancelled orders | Stock automatically returned when order is cancelled/refunded | Admin |
| INV-008 | Set minimum stock thresholds | As an admin, I want to define low stock levels | Per-product min_stock field, triggers alert when reached | Admin |
| INV-009 | Create low stock alerts | As an admin, I want notifications for low stock | Dashboard alert + email when stock falls below minimum | Admin |
| INV-010 | Create out of stock alerts | As an admin, I want to know when items sell out | Dashboard alert + email when stock reaches zero | Admin |
| INV-011 | Display inventory history | As an admin, I want to see stock movements | Log table: date, product, type, quantity, user, reason | Admin |
| INV-012 | View product inventory detail | As an admin, I want to see single product history | Product-specific view with all stock movements | Admin |
| INV-013 | Create waste management section | As an admin, I want to track damaged/expired goods | List of waste entries with: product, qty, reason, date, logged by | Admin |
| INV-014 | Review and approve waste logs | As an admin, I want to verify waste entries | Approve/reject waste entries from staff | Admin |
| INV-015 | Generate inventory report | As an admin, I want stock reports | Report: current levels, movements, waste summary | Admin |
| INV-016 | Export inventory data | As an admin, I want to download inventory | Export to PDF with view in new tab / download option | Admin |
| INV-017 | Create inventory API endpoints | As a developer, I need inventory APIs | CRUD endpoints for stock operations | Developer |
| INV-018 | Write inventory module tests | As a developer, I need 100% test coverage | Unit tests for calculations, integration tests for auto-deduct | Developer |

### Inventory API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/admin/inventory` | List all inventory | Yes (Admin) |
| GET | `/api/v1/admin/inventory/{productId}` | Get product inventory | Yes (Admin) |
| POST | `/api/v1/admin/inventory/receive` | Receive stock | Yes (Admin) |
| POST | `/api/v1/admin/inventory/adjust` | Adjust stock | Yes (Admin) |
| GET | `/api/v1/admin/inventory/history` | Get inventory history | Yes (Admin) |
| GET | `/api/v1/admin/inventory/low-stock` | Get low stock items | Yes (Admin) |
| GET | `/api/v1/admin/inventory/alerts` | Get inventory alerts | Yes (Admin) |
| GET | `/api/v1/admin/waste` | List waste entries | Yes (Admin) |
| PUT | `/api/v1/admin/waste/{id}/approve` | Approve waste entry | Yes (Admin) |
| GET | `/api/v1/admin/inventory/export` | Export inventory PDF | Yes (Admin) |

### Inventory Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  INVENTORY MANAGEMENT                                                               │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐                   │
│  │ 📦 Total    │ │ ⚠️ Low Stock│ │ ❌ Out of   │ │ 🗑️ Waste    │                   │
│  │ Products    │ │             │ │   Stock     │ │  This Month │                   │
│  │     45      │ │      8      │ │      3      │ │    12 kg    │                   │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘                   │
│                                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │ 🔍 Search...        Category: [All ▼]    Status: [All ▼]    [+ Receive]     │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  ┌──────────────────────────────────────────────────────────────────────────────┐  │
│  │ Product          │ Category │ Stock  │ Min │ Status      │ Last Update │ Act │  │
│  ├──────────────────────────────────────────────────────────────────────────────┤  │
│  │ Ribeye Steak     │ Beef     │  5 kg  │ 10  │ ⚠️ Low      │ 2h ago      │ ✏️  │  │
│  │ Chicken Breast   │ Chicken  │ 45 kg  │ 20  │ ✅ Normal   │ 1d ago      │ ✏️  │  │
│  │ Lamb Chops       │ Lamb     │  3 kg  │ 15  │ ⚠️ Low      │ 3h ago      │ ✏️  │  │
│  │ Pork Ribs        │ Pork     │  0 kg  │ 10  │ ❌ Out      │ 5h ago      │ ✏️  │  │
│  │ Beef Mince       │ Beef     │ 30 kg  │ 25  │ ✅ Normal   │ 12h ago     │ ✏️  │  │
│  │ T-Bone Steak     │ Beef     │  0 kg  │  8  │ ❌ Out      │ 1d ago      │ ✏️  │  │
│  └──────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                     │
│  RECENT MOVEMENTS                                               [View All] [Export] │
│  ┌──────────────────────────────────────────────────────────────────────────────┐  │
│  │ Dec 12, 10:30 │ Ribeye Steak  │ -2 kg  │ Order #ZM-0024  │ System        │     │  │
│  │ Dec 12, 09:15 │ Chicken       │ +50 kg │ Stock Receive   │ Admin         │     │  │
│  │ Dec 12, 08:00 │ Lamb Chops    │ -1 kg  │ Waste (expired) │ Sarah (Staff) │     │  │
│  └──────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 7.2 Delivery Management

### Objectives

1. Manage delivery zones and fee calculations
2. Track all deliveries with status updates
3. View and manage proof of delivery records
4. Handle delivery issues and exceptions
5. Provide delivery analytics and reporting

### Success Criteria

| Criteria | Target |
|----------|--------|
| Delivery success rate | > 98% |
| POD capture rate | 100% |
| Fee calculation accuracy | 100% |
| Zone coverage | All service areas |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| DEL-001 | Create delivery dashboard | As an admin, I want an overview of deliveries | Stats: today's deliveries, pending, in progress, completed, issues | Admin |
| DEL-002 | Display all deliveries list | As an admin, I want to see all deliveries | Table: order#, customer, address, status, assigned staff, time | Admin |
| DEL-003 | Filter deliveries | As an admin, I want to find specific deliveries | Filters: status, date range, staff member | Admin |
| DEL-004 | View delivery detail | As an admin, I want full delivery information | Order details, customer info, POD, timeline, notes | Admin |
| DEL-005 | Assign delivery to staff | As an admin, I want to delegate deliveries | Dropdown to select staff, notification sent to staff | Admin |
| DEL-006 | Reassign delivery | As an admin, I want to change assigned staff | Change staff assignment with reason | Admin |
| DEL-007 | View proof of delivery | As an admin, I want to see delivery confirmation | Display signature, photo, notes, timestamp | Admin |
| DEL-008 | Download POD document | As an admin, I want to save POD | Download POD as PDF with all details | Admin |
| DEL-009 | Handle delivery issues | As an admin, I want to resolve problems | View reported issues, add resolution, update status | Admin |
| DEL-010 | Create delivery zones management | As an admin, I want to manage delivery areas | CRUD for zones: name, postcodes, free delivery threshold, fee | Admin |
| DEL-011 | Add delivery zone | As an admin, I want to create new zones | Form: zone name, postcodes (comma-separated), min order, delivery fee | Admin |
| DEL-012 | Edit delivery zone | As an admin, I want to update zones | Modify zone settings, postcodes, fees | Admin |
| DEL-013 | Delete delivery zone | As an admin, I want to remove zones | Delete with confirmation, check for active orders | Admin |
| DEL-014 | Configure delivery fees | As an admin, I want to set delivery pricing | Base fee, per-km rate, free delivery threshold | Admin |
| DEL-015 | View delivery map | As an admin, I want to see delivery locations | Google Maps with all delivery pins for the day | Admin |
| DEL-016 | Generate delivery report | As an admin, I want delivery analytics | Report: deliveries by zone, staff performance, issues | Admin |
| DEL-017 | Export delivery data | As an admin, I want to download delivery info | Export to PDF with view/download options | Admin |
| DEL-018 | Create delivery API endpoints | As a developer, I need delivery APIs | Endpoints for zones, assignments, POD, reports | Developer |
| DEL-019 | Write delivery module tests | As a developer, I need 100% test coverage | Unit, integration tests for fee calculation, zones | Developer |

### Delivery API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/admin/deliveries` | List all deliveries | Yes (Admin) |
| GET | `/api/v1/admin/deliveries/{id}` | Get delivery details | Yes (Admin) |
| PUT | `/api/v1/admin/deliveries/{id}/assign` | Assign to staff | Yes (Admin) |
| GET | `/api/v1/admin/deliveries/{id}/pod` | Get proof of delivery | Yes (Admin) |
| PUT | `/api/v1/admin/deliveries/{id}/issue` | Update issue status | Yes (Admin) |
| GET | `/api/v1/admin/delivery-zones` | List delivery zones | Yes (Admin) |
| POST | `/api/v1/admin/delivery-zones` | Create zone | Yes (Admin) |
| PUT | `/api/v1/admin/delivery-zones/{id}` | Update zone | Yes (Admin) |
| DELETE | `/api/v1/admin/delivery-zones/{id}` | Delete zone | Yes (Admin) |
| GET | `/api/v1/admin/delivery-settings` | Get delivery settings | Yes (Admin) |
| PUT | `/api/v1/admin/delivery-settings` | Update settings | Yes (Admin) |
| GET | `/api/v1/admin/deliveries/export` | Export deliveries PDF | Yes (Admin) |

### Delivery Zones Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  DELIVERY ZONES MANAGEMENT                                         [+ Add Zone]    │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  DELIVERY SETTINGS                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │ Free Delivery Threshold: AU$ [100.00]    Per-KM Rate: AU$ [0.15]    [Save]  │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  DELIVERY ZONES                                                                     │
│  ┌──────────────────────────────────────────────────────────────────────────────┐  │
│  │ Zone Name       │ Postcodes              │ Min Order │ Fee     │ Actions    │  │
│  ├──────────────────────────────────────────────────────────────────────────────┤  │
│  │ Engadine Local  │ 2233, 2234             │ $100      │ FREE    │ ✏️  🗑️     │  │
│  │ Sutherland      │ 2232, 2230, 2231       │ $100      │ FREE    │ ✏️  🗑️     │  │
│  │ Campbelltown    │ 2560, 2565, 2566       │ $100      │ $8.00   │ ✏️  🗑️     │  │
│  │ Sydney CBD      │ 2000, 2001, 2010       │ $150      │ $15.00  │ ✏️  🗑️     │  │
│  │ Extended Sydney │ (Per-KM calculation)   │ $100      │ $0.15/km│ ✏️  🗑️     │  │
│  └──────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                     │
│  Note: Orders outside defined zones use per-kilometer rate from store location.    │
│  Store Address: 6/1053 Old Princes Highway, Engadine, NSW 2233                     │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 7 Summary

| Section | Requirements | IDs |
|---------|--------------|-----|
| Inventory Management | 18 | INV-001 to INV-018 |
| Delivery Management | 19 | DEL-001 to DEL-019 |
| **Total** | **37** | |

---

**Previous:** [Part 6: Admin Dashboard](part6-admin.md)

**Next:** [Part 8: Reports & Settings](part8-reports-settings.md)
