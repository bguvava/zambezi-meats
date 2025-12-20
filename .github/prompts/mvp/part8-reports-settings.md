# Zambezi Meats MVP - Part 8: Reports & Settings

## Module Overview

| Field             | Value                                 |
| ----------------- | ------------------------------------- |
| **Module Names**  | REPORTS, SETTINGS                     |
| **Priority**      | P1/P2                                 |
| **Dependencies**  | All data modules, ADMIN               |
| **Documentation** | `/docs/reports/`, `/docs/settings/`   |
| **Tests**         | `/tests/reports/`, `/tests/settings/` |

This module combines:

- Reports & Analytics (REPORTS)
- System Settings (SETTINGS)

**Total Requirements: 52**

---

## 8.1 Reports & Analytics

### Objectives

1. Provide comprehensive sales and revenue analytics
2. Generate product performance reports
3. Track customer analytics and behavior
4. Create staff performance reports
5. Export all reports as PDF with view/download options

### Success Criteria

| Criteria               | Target          |
| ---------------------- | --------------- |
| Report generation time | < 5 seconds     |
| Data accuracy          | 100%            |
| Export functionality   | View + Download |
| Date range flexibility | Custom ranges   |
| Test coverage          | 100%            |

### Document Actions Policy

> **All reports must support:**
>
> - **View**: Opens PDF in new browser tab
> - **Download**: Downloads PDF file directly

### Requirements

| Requirement ID | Description                        | User Story                                 | Expected Outcome                                             | Role      |
| -------------- | ---------------------------------- | ------------------------------------------ | ------------------------------------------------------------ | --------- |
| RPT-001        | Create reports dashboard           | As an admin, I want a reports overview     | Dashboard with report categories and quick access            | Admin     |
| RPT-002        | Create sales summary report        | As an admin, I want to see sales overview  | Report: total revenue, orders, avg order value by period     | Admin     |
| RPT-003        | Create revenue by period report    | As an admin, I want revenue breakdown      | Daily/weekly/monthly revenue with charts                     | Admin     |
| RPT-004        | Create orders by status report     | As an admin, I want order status breakdown | Pie chart + table: pending, processing, delivered, cancelled | Admin     |
| RPT-005        | Create product sales report        | As an admin, I want product performance    | Table: product, qty sold, revenue, % of total                | Admin     |
| RPT-006        | Create category sales report       | As an admin, I want category performance   | Chart + table: category, qty sold, revenue                   | Admin     |
| RPT-007        | Create top products report         | As an admin, I want bestsellers list       | Top 10 products by revenue and quantity                      | Admin     |
| RPT-008        | Create low performing products     | As an admin, I want underperforming items  | Products with low/no sales in period                         | Admin     |
| RPT-009        | Create customer report             | As an admin, I want customer analytics     | New vs returning, top customers, avg spend                   | Admin     |
| RPT-010        | Create customer acquisition report | As an admin, I want growth metrics         | New customers by period, source tracking                     | Admin     |
| RPT-011        | Create staff performance report    | As an admin, I want staff productivity     | Orders processed, deliveries, ratings by staff               | Admin     |
| RPT-012        | Create delivery performance report | As an admin, I want delivery metrics       | On-time rate, issues, by zone                                | Admin     |
| RPT-013        | Create inventory report            | As an admin, I want stock analytics        | Stock levels, turnover, waste summary                        | Admin     |
| RPT-014        | Create financial summary report    | As an admin, I want financial overview     | Revenue, fees, refunds, net income                           | Admin     |
| RPT-015        | Create payment methods report      | As an admin, I want payment breakdown      | Orders by payment method, success rates                      | Admin     |
| RPT-016        | Implement date range selector      | As an admin, I want custom date ranges     | Presets (today, week, month, year) + custom picker           | Admin     |
| RPT-017        | Implement report charts            | As an admin, I want visual analytics       | Line, bar, pie charts using Chart.js                         | Admin     |
| RPT-018        | Export report to PDF (View)        | As an admin, I want to view report         | "View" button opens PDF in new tab                           | Admin     |
| RPT-019        | Export report to PDF (Download)    | As an admin, I want to download report     | "Download" button downloads PDF file                         | Admin     |
| RPT-020        | Schedule automated reports         | As an admin, I want regular reports        | Configure weekly/monthly email reports                       | Admin     |
| RPT-021        | Create reports API endpoints       | As a developer, I need report APIs         | Endpoints for each report type                               | Developer |
| RPT-022        | Write reports module tests         | As a developer, I need 100% test coverage  | Unit tests for calculations, integration tests               | Developer |

### Reports API Endpoints

| Method | Endpoint                              | Description       | Auth        |
| ------ | ------------------------------------- | ----------------- | ----------- |
| GET    | `/api/v1/admin/reports/sales-summary` | Sales summary     | Yes (Admin) |
| GET    | `/api/v1/admin/reports/revenue`       | Revenue by period | Yes (Admin) |
| GET    | `/api/v1/admin/reports/orders`        | Orders report     | Yes (Admin) |
| GET    | `/api/v1/admin/reports/products`      | Product sales     | Yes (Admin) |
| GET    | `/api/v1/admin/reports/categories`    | Category sales    | Yes (Admin) |
| GET    | `/api/v1/admin/reports/customers`     | Customer report   | Yes (Admin) |
| GET    | `/api/v1/admin/reports/staff`         | Staff performance | Yes (Admin) |
| GET    | `/api/v1/admin/reports/deliveries`    | Delivery report   | Yes (Admin) |
| GET    | `/api/v1/admin/reports/inventory`     | Inventory report  | Yes (Admin) |
| GET    | `/api/v1/admin/reports/financial`     | Financial summary | Yes (Admin) |
| GET    | `/api/v1/admin/reports/{type}/export` | Export as PDF     | Yes (Admin) |

### Reports Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  REPORTS & ANALYTICS                                                                │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  Date Range: [Today ▼] [Dec 1] - [Dec 12]                           [Apply]        │
│                                                                                     │
│  QUICK STATS                                                                        │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐                   │
│  │ 💰 Revenue  │ │ 📦 Orders   │ │ 👥 Customers│ │ 📊 Avg Order│                   │
│  │  $12,450    │ │     156     │ │     89      │ │   $79.81    │                   │
│  │   ▲ 15%    │ │    ▲ 12%   │ │    ▲ 8%    │ │    ▲ 3%    │                   │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘                   │
│                                                                                     │
│  REVENUE TREND                                                    [View] [Download] │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │     $2k ┤                                              ╭─╮                  │   │
│  │         │                                        ╭─╮  │ │  ╭─╮             │   │
│  │     $1k ┤        ╭─╮   ╭─╮   ╭─╮   ╭─╮   ╭─╮   │ │  │ │  │ │             │   │
│  │         │  ╭─╮  │ │  │ │  │ │  │ │  │ │  │ │  │ │  │ │             │   │
│  │       0 ┼──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴─────────│   │
│  │           1    2    3    4    5    6    7    8    9   10   11   12     │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  AVAILABLE REPORTS                                                                  │
│  ┌───────────────────────┐  ┌───────────────────────┐  ┌───────────────────────┐   │
│  │ 📊 Sales Summary      │  │ 📦 Product Sales      │  │ 👥 Customer Analytics │   │
│  │ [View] [Download]     │  │ [View] [Download]     │  │ [View] [Download]     │   │
│  └───────────────────────┘  └───────────────────────┘  └───────────────────────┘   │
│  ┌───────────────────────┐  ┌───────────────────────┐  ┌───────────────────────┐   │
│  │ 👷 Staff Performance  │  │ 🚚 Delivery Report    │  │ 📦 Inventory Report   │   │
│  │ [View] [Download]     │  │ [View] [Download]     │  │ [View] [Download]     │   │
│  └───────────────────────┘  └───────────────────────┘  └───────────────────────┘   │
│  ┌───────────────────────┐  ┌───────────────────────┐                              │
│  │ 💳 Payment Methods    │  │ 💰 Financial Summary  │                              │
│  │ [View] [Download]     │  │ [View] [Download]     │                              │
│  └───────────────────────┘  └───────────────────────┘                              │
│                                                                                     │
│  TOP PRODUCTS THIS PERIOD                          TOP CUSTOMERS                    │
│  ┌───────────────────────────────────┐            ┌───────────────────────────┐    │
│  │ 1. Chicken Breast    $1,245 (85) │            │ 1. John S.      $456.00  │    │
│  │ 2. Ribeye Steak      $1,102 (24) │            │ 2. Mary K.      $389.50  │    │
│  │ 3. Beef Mince        $890 (52)   │            │ 3. Peter L.     $345.00  │    │
│  │ 4. Lamb Chops        $756 (23)   │            │ 4. Lisa M.      $298.00  │    │
│  │ 5. Pork Ribs         $623 (31)   │            │ 5. Tom R.       $267.50  │    │
│  └───────────────────────────────────┘            └───────────────────────────┘    │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 8.2 System Settings

### Objectives

1. Provide centralized system configuration management
2. Configure store information, operating hours, and branding
3. Manage payment gateway settings and credentials
4. Configure email templates and notification preferences
5. Manage currency exchange rate settings
6. Control application-wide feature toggles

### Success Criteria

| Criteria              | Target               |
| --------------------- | -------------------- |
| All settings editable | Admin only           |
| Changes take effect   | Immediate            |
| Validation            | All fields validated |
| Audit logging         | All changes logged   |
| Test coverage         | 100%                 |

### Settings Categories

| Category              | Description                           |
| --------------------- | ------------------------------------- |
| Store Information     | Business name, address, contact, logo |
| Operating Hours       | Daily hours, holiday schedules        |
| Payment Gateways      | Stripe, PayPal, Afterpay credentials  |
| Email Configuration   | SMTP settings, email templates        |
| Currency Settings     | Default currency, exchange rate API   |
| Notification Settings | Email/SMS triggers, recipients        |
| Delivery Settings     | Zones, fees, minimum order            |
| Security Settings     | Session timeout, password policies    |

### Requirements

| Requirement ID | Description                       | User Story                                        | Expected Outcome                                           | Role      |
| -------------- | --------------------------------- | ------------------------------------------------- | ---------------------------------------------------------- | --------- |
| SET-001        | Create settings dashboard         | As an admin, I want centralized configuration     | Settings page with categorized sections                    | Admin     |
| SET-002        | Configure store name and tagline  | As an admin, I want to set business identity      | Store name appears in header, emails, invoices             | Admin     |
| SET-003        | Configure store logo              | As an admin, I want to upload business logo       | Logo upload with preview, used across application          | Admin     |
| SET-004        | Configure store address           | As an admin, I want to set business address       | Address displayed on landing page, invoices, emails        | Admin     |
| SET-005        | Configure contact information     | As an admin, I want to set contact details        | Phone, email, social media links configured                | Admin     |
| SET-006        | Configure operating hours         | As an admin, I want to set business hours         | Daily hours displayed on landing page, delivery scheduling | Admin     |
| SET-007        | Configure holiday schedules       | As an admin, I want to mark closed days           | Holiday dates prevent delivery scheduling                  | Admin     |
| SET-008        | Configure Stripe credentials      | As an admin, I want to set up card payments       | Stripe API keys (test/live), webhook secret                | Admin     |
| SET-009        | Configure PayPal credentials      | As an admin, I want to enable PayPal              | PayPal client ID/secret (sandbox/live)                     | Admin     |
| SET-010        | Configure Afterpay credentials    | As an admin, I want to enable Afterpay            | Afterpay merchant ID/secret                                | Admin     |
| SET-011        | Enable/disable payment methods    | As an admin, I want to control payment options    | Toggle each payment method on/off                          | Admin     |
| SET-012        | Configure SMTP settings           | As an admin, I want email delivery working        | SMTP host, port, username, password, encryption            | Admin     |
| SET-013        | Configure sender email            | As an admin, I want branded email sender          | From name and email address for all emails                 | Admin     |
| SET-014        | Manage email templates            | As an admin, I want to customize emails           | Edit templates: order confirmation, password reset, etc.   | Admin     |
| SET-015        | Configure default currency        | As an admin, I want to set base currency          | Default currency (AU$) for all transactions                | Admin     |
| SET-016        | Configure exchange rate API       | As an admin, I want automatic currency conversion | ExchangeRate-API key, update frequency                     | Admin     |
| SET-017        | Set exchange rate manually        | As an admin, I want to override exchange rate     | Manual AU$/US$ rate override option                        | Admin     |
| SET-018        | Configure minimum order amount    | As an admin, I want to set order minimum          | Minimum order value for delivery (e.g., $50)               | Admin     |
| SET-019        | Configure free delivery threshold | As an admin, I want to set free delivery minimum  | Free delivery for orders over (e.g., $100)                 | Admin     |
| SET-020        | Configure session timeout         | As an admin, I want to set security timeout       | Session timeout duration (default 5 minutes)               | Admin     |
| SET-021        | Configure password policies       | As an admin, I want secure passwords              | Min length, complexity requirements                        | Admin     |
| SET-022        | Configure notification recipients | As an admin, I want order notifications           | Email addresses for new order alerts                       | Admin     |
| SET-023        | Enable/disable features           | As an admin, I want to toggle features            | Feature flags: wishlist, reviews, guest checkout           | Admin     |
| SET-024        | Configure SEO meta defaults       | As an admin, I want SEO configuration             | Default title, description, keywords                       | Admin     |
| SET-025        | Configure social media links      | As an admin, I want social presence               | Facebook, Instagram, Twitter URLs                          | Admin     |
| SET-026        | Import/export settings            | As an admin, I want settings backup               | Export settings to JSON, import from file                  | Admin     |
| SET-027        | View settings change history      | As an admin, I want to audit changes              | Log of all settings changes with user, timestamp           | Admin     |
| SET-028        | Create settings API endpoints     | As a developer, I need settings CRUD APIs         | Endpoints for reading/updating settings                    | Developer |
| SET-029        | Cache settings for performance    | As a developer, I need fast settings access       | Settings cached in Redis/file, invalidate on change        | Developer |
| SET-030        | Write settings module tests       | As a developer, I need 100% test coverage         | Unit tests for validation, integration tests for APIs      | Developer |

### Settings API Endpoints

| Method | Endpoint                                        | Description           | Auth        |
| ------ | ----------------------------------------------- | --------------------- | ----------- |
| GET    | `/api/v1/admin/settings`                        | Get all settings      | Yes (Admin) |
| GET    | `/api/v1/admin/settings/{group}`                | Get settings by group | Yes (Admin) |
| PUT    | `/api/v1/admin/settings/{group}`                | Update settings group | Yes (Admin) |
| POST   | `/api/v1/admin/settings/logo`                   | Upload store logo     | Yes (Admin) |
| GET    | `/api/v1/admin/settings/email-templates`        | Get email templates   | Yes (Admin) |
| PUT    | `/api/v1/admin/settings/email-templates/{name}` | Update email template | Yes (Admin) |
| POST   | `/api/v1/admin/settings/email-test`             | Send test email       | Yes (Admin) |
| POST   | `/api/v1/admin/settings/export`                 | Export settings       | Yes (Admin) |
| POST   | `/api/v1/admin/settings/import`                 | Import settings       | Yes (Admin) |
| GET    | `/api/v1/admin/settings/history`                | Get change history    | Yes (Admin) |
| GET    | `/api/v1/settings/public`                       | Get public settings   | No          |

### Settings Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  SYSTEM SETTINGS                                                                    │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  ┌──────────────┐  ┌──────────────────────────────────────────────────────────────┐ │
│  │ CATEGORIES   │  │  STORE INFORMATION                              [Save]      │ │
│  │              │  │  ────────────────────────────────────────────────────────── │ │
│  │ ▶ Store Info │  │                                                             │ │
│  │   Operating  │  │  Store Name:    [Zambezi Meats                         ]   │ │
│  │   Payments   │  │  Tagline:       [Premium Quality Meats                 ]   │ │
│  │   Email      │  │                                                             │ │
│  │   Currency   │  │  Logo:          ┌─────────┐  [Upload New]                  │ │
│  │   Delivery   │  │                 │  LOGO   │  (.github/official_logo.png)   │ │
│  │   Security   │  │                 │  [IMG]  │                                │ │
│  │   Features   │  │                 └─────────┘                                │ │
│  │   SEO        │  │                                                             │ │
│  └──────────────┘  │  Address:       [6/1053 Old Princes Highway            ]   │ │
│                    │  Suburb:        [Engadine                              ]   │ │
│                    │  State:         [NSW                 ] Postcode: [2233]   │ │
│                    │                                                             │ │
│                    │  Phone:         [XXXX XXX XXX                          ]   │ │
│                    │  Email:         [info@zambezimeats.com                 ]   │ │
│                    │  ABN:           [XX XXX XXX XXX                        ]   │ │
│                    │                                                             │ │
│                    │  Operating Hours: 7am - 6pm (Mon-Sun)                       │ │
│                    │                                                             │ │
│                    │  ────────────────────────────────────────────────────────── │ │
│                    │                                                             │ │
│                    │  SOCIAL MEDIA                                               │ │
│                    │  Facebook:      [https://facebook.com/zambezimeats     ]   │ │
│                    │  Instagram:     [https://instagram.com/zambezimeats    ]   │ │
│                    │                                                             │ │
│                    └──────────────────────────────────────────────────────────────┘ │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 8 Summary

| Section             | Requirements | IDs                |
| ------------------- | ------------ | ------------------ |
| Reports & Analytics | 22           | RPT-001 to RPT-022 |
| System Settings     | 30           | SET-001 to SET-030 |
| **Total**           | **52**       |                    |

---

**Previous:** [Part 7: Inventory & Delivery Management](part7-inventory-delivery.md)

**Next:** [Part 9: Deployment & Final Checklist](part9-deployment-checklist.md)
