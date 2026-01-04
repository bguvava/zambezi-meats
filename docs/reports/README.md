# Reports & Analytics Module

## Overview

Module for comprehensive business reporting and analytics covering requirements RPT-001 to RPT-022. This module provides real-time insights into sales, revenue, customer behavior, staff performance, inventory levels, and financial metrics through an intuitive dashboard interface with export capabilities.

## Requirements Coverage

| Requirement | Description                                 | Status         |
| ----------- | ------------------------------------------- | -------------- |
| RPT-001     | Reports dashboard with quick stats overview | ✅ Implemented |
| RPT-002     | Sales summary report with period comparison | ✅ Implemented |
| RPT-003     | Revenue analytics by configurable periods   | ✅ Implemented |
| RPT-004     | Orders breakdown by status                  | ✅ Implemented |
| RPT-005     | Product sales performance tracking          | ✅ Implemented |
| RPT-006     | Category-based sales analysis               | ✅ Implemented |
| RPT-007     | Top selling products identification         | ✅ Implemented |
| RPT-008     | Low performing products detection           | ✅ Implemented |
| RPT-009     | Customer analytics and behavior insights    | ✅ Implemented |
| RPT-010     | Customer acquisition tracking over time     | ✅ Implemented |
| RPT-011     | Staff performance metrics                   | ✅ Implemented |
| RPT-012     | Delivery performance analytics              | ✅ Implemented |
| RPT-013     | Inventory status and turnover report        | ✅ Implemented |
| RPT-014     | Financial summary with profit margins       | ✅ Implemented |
| RPT-015     | Payment methods distribution analysis       | ✅ Implemented |
| RPT-016     | PDF export functionality for all reports    | ✅ Implemented |
| RPT-017     | Date range filtering with presets           | ✅ Implemented |
| RPT-018     | Data grouping by day/week/month             | ✅ Implemented |
| RPT-019     | Real-time dashboard updates                 | ✅ Implemented |
| RPT-020     | Chart visualizations for trends             | ✅ Implemented |
| RPT-021     | Comparison with previous periods            | ✅ Implemented |
| RPT-022     | Role-based report access control            | ✅ Implemented |

## API Endpoints

| Method | Endpoint                                   | Description                        |
| ------ | ------------------------------------------ | ---------------------------------- |
| GET    | /api/v1/admin/reports/dashboard            | Reports dashboard with quick stats |
| GET    | /api/v1/admin/reports/sales-summary        | Sales summary report               |
| GET    | /api/v1/admin/reports/revenue              | Revenue by period                  |
| GET    | /api/v1/admin/reports/orders               | Orders by status                   |
| GET    | /api/v1/admin/reports/products             | Product sales                      |
| GET    | /api/v1/admin/reports/categories           | Category sales                     |
| GET    | /api/v1/admin/reports/top-products         | Top selling products               |
| GET    | /api/v1/admin/reports/low-performing       | Low performing products            |
| GET    | /api/v1/admin/reports/customers            | Customer analytics                 |
| GET    | /api/v1/admin/reports/customer-acquisition | Customer acquisition               |
| GET    | /api/v1/admin/reports/staff                | Staff performance                  |
| GET    | /api/v1/admin/reports/deliveries           | Delivery performance               |
| GET    | /api/v1/admin/reports/inventory            | Inventory report                   |
| GET    | /api/v1/admin/reports/financial            | Financial summary                  |
| GET    | /api/v1/admin/reports/payment-methods      | Payment methods                    |
| GET    | /api/v1/admin/reports/{type}/export        | Export as PDF                      |

## Query Parameters

### Date Filtering

- `preset` - Quick date selection (see Date Presets below)
- `start_date` - Custom start date in YYYY-MM-DD format
- `end_date` - Custom end date in YYYY-MM-DD format

### Data Grouping

- `group_by` - Aggregate data by period: `day`, `week`, or `month` (applies to revenue and acquisition reports)

### Export Options

- `action` - Export behavior: `view` (opens in browser) or `download` (saves file)

### Pagination/Limits

- `limit` - Number of results to return (for top-products and low-performing endpoints)

## Date Presets

| Preset       | Description                            |
| ------------ | -------------------------------------- |
| `today`      | Current day from midnight to now       |
| `yesterday`  | Previous day (full 24 hours)           |
| `week`       | Current week starting from Monday      |
| `last_week`  | Previous full week (Monday to Sunday)  |
| `month`      | Current calendar month                 |
| `last_month` | Previous full calendar month           |
| `year`       | Current calendar year from January 1st |
| `last_year`  | Previous full calendar year            |

## Frontend Components

### ReportsPage.vue

Main reports dashboard component providing:

- Quick stats cards with key metrics
- Interactive date range picker with presets
- Tab-based navigation between report types
- Chart visualizations for trend analysis
- Export buttons for PDF generation

### State Management

Uses `useAdminReportsStore` (Pinia store) for centralized state management including:

- Report data caching
- Loading states per report type
- Error handling
- Date range persistence

### Visualizations

Chart.js integration for:

- Line charts (revenue trends, customer acquisition)
- Bar charts (product sales, category comparison)
- Pie charts (payment methods, order status distribution)
- Doughnut charts (category breakdown)

## Store Actions

| Action                     | Description                                   |
| -------------------------- | --------------------------------------------- |
| `fetchDashboard`           | Load dashboard quick stats and overview data  |
| `fetchSalesSummary`        | Retrieve sales summary with period comparison |
| `fetchRevenue`             | Get revenue data grouped by specified period  |
| `fetchOrders`              | Load orders breakdown by status               |
| `fetchProducts`            | Fetch product sales performance data          |
| `fetchCategories`          | Get category-based sales analysis             |
| `fetchTopProducts`         | Retrieve best-selling products list           |
| `fetchLowPerforming`       | Get underperforming products list             |
| `fetchCustomers`           | Load customer analytics and insights          |
| `fetchCustomerAcquisition` | Get new customer registration trends          |
| `fetchStaff`               | Retrieve staff performance metrics            |
| `fetchDeliveries`          | Load delivery performance data                |
| `fetchInventory`           | Get current inventory status report           |
| `fetchFinancial`           | Retrieve financial summary with margins       |
| `fetchPaymentMethods`      | Get payment method distribution               |
| `exportReport`             | Generate and export PDF for any report type   |
| `setDateRange`             | Update the current date filter range          |
| `setPreset`                | Apply a date preset and refresh data          |
| `clearCache`               | Clear cached report data for fresh fetch      |

## Export Options

### View Mode (`action=view`)

- Opens generated PDF in a new browser tab
- Ideal for quick review before printing
- Uses browser's native PDF viewer

### Download Mode (`action=download`)

- Downloads PDF file directly to user's device
- Filename format: `{report-type}-report-{YYYY-MM-DD}.pdf`
- Includes company branding and timestamp

### Supported Export Types

All report endpoints support PDF export via `/api/v1/admin/reports/{type}/export`:

- `dashboard`, `sales-summary`, `revenue`, `orders`
- `products`, `categories`, `top-products`, `low-performing`
- `customers`, `customer-acquisition`, `staff`, `deliveries`
- `inventory`, `financial`, `payment-methods`

## Test Coverage

### Backend Tests

- **Total Tests:** 41
- **Total Assertions:** 221
- **Coverage Areas:**
  - Report data accuracy
  - Date range filtering
  - Grouping functionality
  - Export generation
  - Authorization checks
  - Edge cases and error handling

### Frontend Tests

- **Total Tests:** 50+
- **Coverage Areas:**
  - Store actions and mutations
  - Component rendering
  - User interactions
  - Date preset calculations
  - Chart data transformations
  - Export functionality
  - Loading and error states

## Related Documentation

- [Admin Dashboard](../dashboard/README.md)
- [API Endpoints](../deployment/api-endpoints.md)
- [User Management](../user-management/README.md)
