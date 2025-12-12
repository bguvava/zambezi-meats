# Zambezi Meats - Online Butchery Store
## Project Description Document

---

## 1. Project Overview

### 1.1 Company Information
| Field | Details |
|-------|---------|
| **Company Name** | Zambezi Meats |
| **Address** | 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia |
| **Service Area** | Menangle Park, Engadine, City of Campbelltown, Sydney, Australia |
| **Website** | www.zambezimeats.com |
| **Developer** | bguvava (www.bguvava.com) |

### 1.2 Project Objective
To develop a **high-end, gourmet online butchery store** that provides a seamless, premium shopping experience for customers while offering robust business management tools for the store operations team. The system will be a **Single Page Application (SPA)** with a dashboard-centric architecture, optimized for deployment on CyberPanel hosting environment.

### 1.3 Project Goals

| # | Goal | Description |
|---|------|-------------|
| 1 | **Premium User Experience** | Deliver a visually stunning, fast, and intuitive interface that reflects the gourmet quality of products |
| 2 | **Operational Efficiency** | Streamline order processing, inventory management, and delivery logistics |
| 3 | **Real-Time Inventory Control** | Prevent overselling through live stock tracking and synchronization |
| 4 | **Scalable Architecture** | Build a maintainable codebase that can grow with the business |
| 5 | **Data-Driven Decisions** | Provide comprehensive analytics and reporting for business insights |
| 6 | **Secure Transactions** | Implement robust security measures for payments and customer data |
| 7 | **Compliance Ready** | Ensure food safety regulations and traceability requirements are met |

---

## 2. System Architecture

### 2.1 Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              ZAMBEZI MEATS SPA                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐  │
│  │   PUBLIC    │    │  CUSTOMER   │    │    STAFF    │    │    ADMIN    │  │
│  │  STOREFRONT │    │  DASHBOARD  │    │  DASHBOARD  │    │  DASHBOARD  │  │
│  └──────┬──────┘    └──────┬──────┘    └──────┬──────┘    └──────┬──────┘  │
│         │                  │                  │                  │         │
│         └──────────────────┴──────────────────┴──────────────────┘         │
│                                    │                                        │
│                          ┌─────────▼─────────┐                             │
│                          │   API GATEWAY     │                             │
│                          │   (REST API)      │                             │
│                          └─────────┬─────────┘                             │
│                                    │                                        │
└────────────────────────────────────┼────────────────────────────────────────┘
                                     │
┌────────────────────────────────────┼────────────────────────────────────────┐
│                           BACKEND SERVICES                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐     │
│  │  Auth    │  │ Products │  │  Orders  │  │ Inventory│  │ Delivery │     │
│  │ Service  │  │ Service  │  │ Service  │  │ Service  │  │ Service  │     │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘     │
│       └─────────────┴─────────────┴─────────────┴─────────────┘           │
│                                    │                                        │
│                          ┌─────────▼─────────┐                             │
│                          │   MySQL Database  │                             │
│                          └───────────────────┘                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Design Principles

| Principle | Implementation |
|-----------|----------------|
| **SPA Architecture** | Dashboard-based interface with child modal windows |
| **Responsive Design** | Mobile-first approach, adapts to all screen sizes |
| **Real-Time Sync** | Long-polling/SSE for real-time updates (WebSocket alternative) |
| **Headless Modals** | Minimize page navigation, use tabs and modal dialogs |
| **Performance First** | Lazy loading, image optimization, caching strategies |

---

## 3. Data & System Workflow

### 3.1 Customer Order Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                           CUSTOMER ORDER WORKFLOW                            │
└──────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐
  │ BROWSE  │────▶│  ADD TO │────▶│CHECKOUT │────▶│ PAYMENT │────▶│  ORDER  │
  │PRODUCTS │     │  CART   │     │ DETAILS │     │ PROCESS │     │CONFIRMED│
  └─────────┘     └─────────┘     └─────────┘     └─────────┘     └─────────┘
       │                               │               │               │
       ▼                               ▼               ▼               ▼
  ┌─────────┐                    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │ Filter  │                    │ Address │    │ Stripe  │    │  Email  │
  │ Search  │                    │Validate │    │ PayPal  │    │  Alert  │
  │ Compare │                    │Delivery │    │Afterpay │    │Dashboard│
  └─────────┘                    │  Fees   │    │  Cash   │    │  Alert  │
                                 └─────────┘    └─────────┘    └─────────┘
```

### 3.2 Order Processing Flow (Staff)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                          ORDER PROCESSING WORKFLOW                           │
└──────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐
  │  NEW    │────▶│ACCEPTED │────▶│PREPARING│────▶│  READY  │────▶│  OUT    │
  │  ORDER  │     │         │     │         │     │         │     │DELIVERY │
  └─────────┘     └─────────┘     └─────────┘     └─────────┘     └─────────┘
       │               │               │               │               │
       ▼               ▼               ▼               ▼               ▼
  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │Dashboard│    │ Stock   │    │Packaging│    │ Assign  │    │ Route   │
  │  Alert  │    │ Reserve │    │ Process │    │ Driver  │    │ Optimize│
  │  Email  │    │         │    │         │    │         │    │ Track   │
  └─────────┘    └─────────┘    └─────────┘    └─────────┘    └─────────┘
                                                                    │
                                                                    ▼
                                                              ┌─────────┐
                                                              │DELIVERED│
                                                              │  POD    │
                                                              │Complete │
                                                              └─────────┘
```

### 3.3 Inventory Management Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                         INVENTORY MANAGEMENT WORKFLOW                        │
└──────────────────────────────────────────────────────────────────────────────┘

  ┌─────────────┐         ┌─────────────┐         ┌─────────────┐
  │   STOCK     │────────▶│   STOCK     │────────▶│   STOCK     │
  │   RECEIVE   │         │   ADJUST    │         │   DEDUCT    │
  └─────────────┘         └─────────────┘         └─────────────┘
        │                       │                       │
        ▼                       ▼                       ▼
  ┌─────────────┐         ┌─────────────┐         ┌─────────────┐
  │ Add to      │         │ Manual      │         │ Order       │
  │ Inventory   │         │ Adjustment  │         │ Fulfillment │
  │ Set Expiry  │         │ Waste Log   │         │ Auto-Sync   │
  └─────────────┘         └─────────────┘         └─────────────┘
                                │
                    ┌───────────┼───────────┐
                    ▼           ▼           ▼
              ┌─────────┐ ┌─────────┐ ┌─────────┐
              │Low Stock│ │ Expiry  │ │ Waste   │
              │ Alert   │ │ Alert   │ │ Report  │
              └─────────┘ └─────────┘ └─────────┘
```

---

## 4. User Roles & Responsibilities

### 4.1 Role Definitions

| Role | Description | Access Level |
|------|-------------|--------------|
| **Guest** | Unauthenticated visitor browsing the storefront | Public only |
| **Customer** | Registered user who can place orders and track deliveries | Customer Dashboard |
| **Packer** | Staff responsible for preparing and packaging orders | Limited Staff Dashboard |
| **Driver** | Delivery personnel with route and delivery management | Limited Staff Dashboard |
| **Manager** | Store manager overseeing daily operations | Full Staff Dashboard |
| **Administrator** | System administrator with full access and configuration rights | Admin Dashboard |

### 4.2 Role Hierarchy

```
                    ┌─────────────────┐
                    │  ADMINISTRATOR  │
                    │   (Level 5)     │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │    MANAGER      │
                    │   (Level 4)     │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
     ┌────────▼────────┐     │     ┌────────▼────────┐
     │     PACKER      │     │     │     DRIVER      │
     │   (Level 2)     │     │     │   (Level 2)     │
     └─────────────────┘     │     └─────────────────┘
                             │
                    ┌────────▼────────┐
                    │    CUSTOMER     │
                    │   (Level 1)     │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │     GUEST       │
                    │   (Level 0)     │
                    └─────────────────┘
```

---

## 5. System Modules

### 5.1 Module Overview by Role

#### Guest Modules (Public Storefront)
| Module | Description |
|--------|-------------|
| **Home** | Landing page with featured products, promotions, and CTAs |
| **Shop** | Product catalog with search, filter, and category navigation |
| **Product Detail** | Individual product page with images, description, pricing |
| **Cart** | Shopping cart management |
| **Checkout** | Guest checkout process |
| **Contact** | Contact form and store information |
| **About** | Company information and story |

#### Customer Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Dashboard home with recent orders and recommendations |
| **My Orders** | Order history, tracking, and reorder functionality |
| **My Profile** | Personal information and preferences management |
| **Addresses** | Delivery address management |
| **Wishlist** | Saved products for future purchase |
| **Notifications** | Order updates and promotional alerts |
| **Support** | Help center and ticket submission |

#### Packer Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Daily tasks and pending orders summary |
| **Orders Queue** | List of orders to be prepared |
| **Order Processing** | Order details and packaging checklist |
| **Stock Check** | Quick inventory lookup |
| **Activity Log** | Personal activity history |

#### Driver Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Daily deliveries summary and route overview |
| **My Deliveries** | Assigned deliveries list |
| **Route Map** | Optimized delivery route with navigation |
| **Delivery Details** | Individual delivery information and POD capture |
| **Earnings** | Delivery earnings and history |
| **Activity Log** | Personal delivery history |

#### Manager Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Business metrics, alerts, and quick actions |
| **Orders** | Full order management (view, edit, cancel, refund) |
| **Products** | Product catalog management |
| **Inventory** | Stock levels, adjustments, expiry tracking |
| **Customers** | Customer database and CRM |
| **Deliveries** | Delivery scheduling and driver assignment |
| **Staff** | Packer and driver management |
| **Reports** | Sales, inventory, and performance reports |
| **Promotions** | Discounts, coupons, and promotional campaigns |
| **Settings** | Store settings (hours, delivery zones, fees) |

#### Administrator Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | System health, performance metrics, and alerts |
| **Users** | All user account management |
| **Roles & Permissions** | Role configuration and access control |
| **Products** | Full product and category management |
| **Orders** | Complete order lifecycle management |
| **Inventory** | Full inventory control and waste management |
| **Customers** | Complete CRM with data export |
| **Deliveries** | All delivery operations and logistics |
| **Finance** | Revenue, payments, and financial reports |
| **Reports** | Comprehensive analytics and custom reports |
| **Settings** | System-wide configuration |
| **Audit Logs** | Complete activity and change logs |
| **Integrations** | Third-party service management (payments, email) |
| **Backup & Maintenance** | Database backups and system maintenance |

---

## 6. Permissions Matrix

### 6.1 Storefront Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Products | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| View Product Details | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Add to Cart | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Checkout | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Prices | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Submit Contact Form | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create Account | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### 6.2 Customer Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Own Orders | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Track Own Orders | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Cancel Own Orders | ❌ | ✅* | ❌ | ❌ | ❌ | ❌ |
| Request Refund | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Own Profile | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Manage Own Addresses | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Order History | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Reorder Past Orders | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage Wishlist | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Submit Support Ticket | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |

*\* Only before order is processed*

### 6.3 Order Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View All Orders | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Assigned Orders | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Accept Orders | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Reject Orders | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Update Order Status | ❌ | ❌ | ✅* | ✅* | ✅ | ✅ |
| Assign Orders to Staff | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Edit Order Details | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Cancel Orders | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Process Refunds | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Order Analytics | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Export Orders | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

*\* Limited to their assigned workflow stage*

### 6.4 Product Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Products (Admin) | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Create Products | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Edit Products | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Delete Products | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage Categories | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Set Pricing | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Product Images | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Set Product Availability | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Bulk Import Products | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Export Product Data | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

### 6.5 Inventory Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Stock Levels | ❌ | ❌ | ✅ | ❌ | ✅ | ✅ |
| Receive Stock | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Adjust Stock | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Record Waste | ❌ | ❌ | ✅ | ❌ | ✅ | ✅ |
| Set Low Stock Alerts | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Expiry Dates | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Inventory Reports | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Export Inventory Data | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

### 6.6 Delivery Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View All Deliveries | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Assigned Deliveries | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Assign Drivers | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Update Delivery Status | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Capture POD | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Manage Delivery Zones | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Set Delivery Fees | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Route Analytics | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

### 6.7 Customer Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Customer List | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Customer Details | ❌ | ❌ | ❌ | ✅* | ✅ | ✅ |
| Edit Customer Info | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Delete Customer | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| View Purchase History | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Customer Notes | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Handle Complaints | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Export Customer Data | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

*\* Limited to delivery-relevant information only*

### 6.8 Staff Management Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Staff List | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Create Staff Account | ❌ | ❌ | ❌ | ❌ | ✅* | ✅ |
| Edit Staff Details | ❌ | ❌ | ❌ | ❌ | ✅* | ✅ |
| Deactivate Staff | ❌ | ❌ | ❌ | ❌ | ✅* | ✅ |
| Delete Staff Account | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Assign Roles | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| View Staff Activity | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Schedules | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

*\* Packer and Driver roles only*

### 6.9 Financial & Reports Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View Sales Reports | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Financial Summary | ❌ | ❌ | ❌ | ❌ | ✅* | ✅ |
| View Payment Records | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Process Manual Payments | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| View Tax Reports | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Export Financial Data | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Configure Payment Settings | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

*\* Limited view*

### 6.10 System Administration Permissions

| Permission | Guest | Customer | Packer | Driver | Manager | Admin |
|------------|:-----:|:--------:|:------:|:------:|:-------:|:-----:|
| View System Settings | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Edit Store Settings | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Integrations | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| View Audit Logs | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage Backups | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| System Maintenance | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Configure Email Templates | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Manage Promotions | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |

---

## 7. Technical Stack

### 7.1 Recommended Technology Stack

#### Frontend
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Framework** | Vue.js 3 + Vite | Lightweight, fast, SPA-optimized, excellent for dashboards |
| **UI Library** | Tailwind CSS + Headless UI | Modern, customizable, headless components for modals |
| **State Management** | Pinia | Official Vue.js store, lightweight and TypeScript-friendly |
| **HTTP Client** | Axios | Robust API communication with interceptors |
| **Charts** | Chart.js + Vue-ChartJS | Lightweight, responsive analytics visualization |
| **PDF Generation** | jsPDF + html2canvas | Client-side PDF export with consistent layout |
| **Form Validation** | VeeValidate + Yup | Comprehensive form handling and validation |
| **Icons** | Heroicons / Lucide | Modern, lightweight icon sets |
| **Animations** | GSAP / Vue Transitions | Smooth, performant animations |
| **Date Handling** | Day.js | Lightweight date manipulation |

#### Backend
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Runtime** | PHP 8.2+ | CyberPanel native support, mature ecosystem |
| **Framework** | Laravel 11 | Robust MVC, excellent ORM, built-in auth, API-ready |
| **API** | Laravel Sanctum | SPA authentication, token-based API security |
| **Database** | MySQL 8.0 | CyberPanel native, reliable, well-documented |
| **Caching** | Redis / File Cache | Session management and query optimization |
| **Queue** | Laravel Queue (Database) | Background job processing for emails, reports |
| **Real-Time** | Laravel Broadcasting (SSE) | Server-Sent Events for real-time updates |
| **File Storage** | Local + CDN | CyberPanel file system with optional CDN |
| **PDF Server-Side** | DomPDF / TCPDF | Server-side PDF generation for exports |

#### Third-Party Integrations
| Service | Provider | Purpose |
|---------|----------|---------|
| **Payments** | Stripe | Primary payment gateway |
| **Payments** | PayPal | Alternative payment option |
| **Payments** | Afterpay | Buy-now-pay-later option |
| **Email** | SMTP / SendGrid | Transactional emails |
| **SMS** | Twilio (optional) | Order notifications |
| **Maps** | Google Maps API | Delivery route optimization |
| **Address Validation** | Google Places API | Australian address autocomplete |

#### DevOps & Deployment
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Version Control** | Git + GitHub | Source control and collaboration |
| **Hosting** | CyberPanel (LiteSpeed) | As specified, optimized for PHP |
| **SSL** | Let's Encrypt | Free SSL certificates via CyberPanel |
| **CI/CD** | GitHub Actions | Automated testing and deployment |
| **Monitoring** | Laravel Telescope (Dev) | Debug and monitoring in development |

### 7.2 Directory Structure

```
zambezi-meats/
├── .github/
│   ├── workflows/          # GitHub Actions CI/CD
│   ├── prompts/            # AI prompts and documentation
│   └── user_requirements.txt
├── backend/                # Laravel Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── OrderController.php
│   │   │   │   │   ├── InventoryController.php
│   │   │   │   │   ├── CustomerController.php
│   │   │   │   │   ├── DeliveryController.php
│   │   │   │   │   ├── ReportController.php
│   │   │   │   │   └── SettingsController.php
│   │   │   │   └── Web/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Events/
│   │   ├── Listeners/
│   │   └── Exports/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── resources/
│   ├── storage/
│   ├── tests/
│   ├── .env.example
│   ├── composer.json
│   └── artisan
├── frontend/               # Vue.js Application
│   ├── src/
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   ├── images/
│   │   │   └── fonts/
│   │   ├── components/
│   │   │   ├── common/
│   │   │   ├── layout/
│   │   │   ├── storefront/
│   │   │   └── dashboard/
│   │   ├── composables/
│   │   ├── layouts/
│   │   ├── pages/
│   │   │   ├── public/
│   │   │   ├── customer/
│   │   │   ├── staff/
│   │   │   └── admin/
│   │   ├── router/
│   │   ├── stores/
│   │   ├── services/
│   │   ├── utils/
│   │   ├── App.vue
│   │   └── main.js
│   ├── public/
│   ├── index.html
│   ├── vite.config.js
│   ├── tailwind.config.js
│   └── package.json
├── docs/                   # Documentation
│   ├── api/
│   ├── guides/
│   └── database-schema.md
├── docker/                 # Docker configs (local dev)
├── .gitignore
├── README.md
└── docker-compose.yml
```

### 7.3 Database Schema Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATABASE SCHEMA OVERVIEW                          │
└─────────────────────────────────────────────────────────────────────────────┘

USERS & AUTH                    PRODUCTS & INVENTORY
┌──────────────┐               ┌──────────────┐
│    users     │               │  categories  │
├──────────────┤               ├──────────────┤
│ id           │               │ id           │
│ email        │               │ name         │
│ password     │               │ slug         │
│ role_id  ────┼──┐            │ parent_id    │
│ status       │  │            │ image        │
│ created_at   │  │            │ sort_order   │
└──────────────┘  │            └──────┬───────┘
                  │                   │
┌──────────────┐  │            ┌──────▼───────┐
│    roles     │◄─┘            │   products   │
├──────────────┤               ├──────────────┤
│ id           │               │ id           │
│ name         │               │ category_id ─┤
│ slug         │               │ name         │
│ permissions  │               │ slug         │
└──────────────┘               │ description  │
                               │ price_per_kg │
┌──────────────┐               │ min_weight   │
│  addresses   │               │ max_weight   │
├──────────────┤               │ images       │
│ id           │               │ is_featured  │
│ user_id  ────┼──────────┐    │ status       │
│ type         │          │    └──────┬───────┘
│ street       │          │           │
│ suburb       │          │    ┌──────▼───────┐
│ state        │          │    │  inventory   │
│ postcode     │          │    ├──────────────┤
│ is_default   │          │    │ id           │
└──────────────┘          │    │ product_id ──┤
                          │    │ quantity_kg  │
                          │    │ batch_number │
ORDERS & DELIVERY         │    │ expiry_date  │
┌──────────────┐          │    │ cost_price   │
│   orders     │          │    │ received_at  │
├──────────────┤          │    └──────────────┘
│ id           │          │
│ user_id  ────┼──────────┤    ACTIVITY & LOGS
│ order_number │          │    ┌──────────────┐
│ status       │          │    │ activity_logs│
│ subtotal     │          │    ├──────────────┤
│ delivery_fee │          │    │ id           │
│ total        │          │    │ user_id  ────┼──┐
│ address_id ──┤          │    │ action       │  │
│ notes        │          │    │ model_type   │  │
│ created_at   │          │    │ model_id     │  │
└──────┬───────┘          │    │ changes      │  │
       │                  │    │ ip_address   │  │
┌──────▼───────┐          │    │ created_at   │  │
│ order_items  │          │    └──────────────┘  │
├──────────────┤          │                      │
│ id           │          │    ┌──────────────┐  │
│ order_id ────┤          │    │   sessions   │  │
│ product_id ──┼──────────┘    ├──────────────┤  │
│ quantity_kg  │               │ id           │  │
│ price_per_kg │               │ user_id  ────┼──┘
│ total_price  │               │ token        │
└──────────────┘               │ ip_address   │
                               │ user_agent   │
┌──────────────┐               │ last_active  │
│  deliveries  │               └──────────────┘
├──────────────┤
│ id           │               SETTINGS & CONFIG
│ order_id ────┤               ┌──────────────┐
│ driver_id    │               │   settings   │
│ status       │               ├──────────────┤
│ route_data   │               │ id           │
│ pod_image    │               │ key          │
│ pod_signature│               │ value        │
│ delivered_at │               │ group        │
└──────────────┘               └──────────────┘
```

---

## 8. Color Palette & Branding

### 8.1 Primary Colors (Based on Logo + Butcher Theme)

| Color | Hex Code | Usage |
|-------|----------|-------|
| **Zambezi Red** | `#B91C1C` | Primary brand color, CTAs, highlights |
| **Dark Red** | `#7F1D1D` | Hover states, dark accents |
| **Light Red** | `#FEE2E2` | Backgrounds, alerts |
| **Charcoal** | `#1F2937` | Primary text, headers |
| **Warm Gray** | `#6B7280` | Secondary text |
| **Off White** | `#F9FAFB` | Page backgrounds |
| **Pure White** | `#FFFFFF` | Cards, modals |
| **Gold Accent** | `#D97706` | Premium highlights, badges |
| **Success Green** | `#059669` | Success states, availability |
| **Warning Amber** | `#D97706` | Warnings, low stock |
| **Error Red** | `#DC2626` | Errors, out of stock |

### 8.2 Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| **Headings** | Playfair Display | 700 | 24-48px |
| **Body** | Inter | 400, 500 | 14-16px |
| **Labels** | Inter | 600 | 12-14px |
| **Buttons** | Inter | 600 | 14-16px |

---

## 9. Business Rules

### 9.1 Pricing & Orders

| Rule | Value |
|------|-------|
| Pricing Model | Fixed price per kilogram |
| Minimum Order (Delivery) | AU$100 |
| Free Delivery Threshold | AU$100+ (specified postcodes) |
| Delivery Fee Calculation | $0.15/km for outside zones |
| Currency | Australian Dollar (AU$) |

### 9.2 Operating Hours

| Day | Hours |
|-----|-------|
| Monday - Sunday | 7:00 AM - 6:00 PM AEST |

### 9.3 Payment Methods

| Method | Status |
|--------|--------|
| Credit/Debit Cards (Visa, Mastercard) | ✅ Enabled |
| Stripe | ✅ Enabled |
| PayPal | ✅ Enabled |
| Afterpay | ✅ Enabled |
| Cash on Delivery | ✅ Enabled |

---

## 10. PDF Export Template

### 10.1 Standard PDF Layout

```
┌─────────────────────────────────────────────────────────────────┐
│  HEADER                                                         │
│  ┌─────────┐                                                    │
│  │  LOGO   │  ZAMBEZI MEATS                                     │
│  └─────────┘  6/1053 Old Princes Highway, Engadine NSW 2233     │
│              Phone: XXXX XXX XXX | www.zambezimeats.com         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  DOCUMENT TITLE                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                                                                 │
│  [Document Content Area]                                        │
│                                                                 │
│  - Tables                                                       │
│  - Lists                                                        │
│  - Data                                                         │
│                                                                 │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  FOOTER                                                         │
│  Generated by: [User Name] | Date: DD/MM/YYYY HH:MM AEST        │
│  ─────────────────────────────────────────────────────────────  │
│  CONFIDENTIAL: This document contains proprietary information   │
│  of Zambezi Meats. Unauthorized distribution is prohibited.     │
│  ─────────────────────────────────────────────────────────────  │
│  © 2025 Zambezi Meats. All rights reserved.      Page X of Y    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 11. Development Phases

### Phase 1: Foundation (Weeks 1-2)
- [ ] Project setup and repository initialization
- [ ] Database schema design and migrations
- [ ] Authentication and authorization system
- [ ] Basic API structure
- [ ] Frontend scaffolding and routing

### Phase 2: Core Features (Weeks 3-5)
- [ ] Product management module
- [ ] Inventory management module
- [ ] Public storefront pages
- [ ] Shopping cart functionality
- [ ] User registration and profiles

### Phase 3: Orders & Payments (Weeks 6-7)
- [ ] Checkout process
- [ ] Payment gateway integrations
- [ ] Order management system
- [ ] Email notifications
- [ ] Order tracking

### Phase 4: Operations (Weeks 8-9)
- [ ] Packer dashboard and workflow
- [ ] Driver dashboard and delivery management
- [ ] Route optimization
- [ ] Proof of delivery capture
- [ ] Real-time status updates

### Phase 5: Administration (Weeks 10-11)
- [ ] Admin dashboard
- [ ] User and role management
- [ ] Reports and analytics
- [ ] CRM features
- [ ] System settings

### Phase 6: Polish & Deploy (Week 12)
- [ ] Performance optimization
- [ ] Security audit
- [ ] Testing and bug fixes
- [ ] Documentation
- [ ] CyberPanel deployment
- [ ] Go-live

---

## 12. Success Metrics

| Metric | Target |
|--------|--------|
| Page Load Time | < 2 seconds |
| Mobile Responsiveness | 100% functionality |
| Uptime | 99.9% |
| Order Processing Time | < 24 hours |
| Customer Satisfaction | > 4.5/5 rating |
| Cart Abandonment | < 30% |

---

## 13. Document Information

| Field | Value |
|-------|-------|
| **Document Version** | 1.0 |
| **Created Date** | December 12, 2025 |
| **Author** | bguvava |
| **Status** | Draft |
| **Next Review** | Upon project kickoff |

---

*This document serves as the foundational blueprint for the Zambezi Meats online butchery store development. All specifications are subject to refinement during the development process.*
