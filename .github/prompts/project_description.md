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
To develop a **high-end, gourmet online butchery store** that provides a seamless, premium shopping experience for customers while offering robust business management tools for the store operations team. The system will be a **Single Page Application (SPA)** with a dashboard-centric architecture, designed for **instant product browsing** (shop-first approach) to maximize customer retention and conversion rates.

### 1.3 Project Goals

| # | Goal | Description |
|---|------|-------------|
| 1 | **Instant Shop Access** | Users land directly on the shop page to immediately browse products - no landing page barriers |
| 2 | **Premium User Experience** | Deliver a visually stunning, fast, and intuitive interface that reflects the gourmet quality of products |
| 3 | **Operational Efficiency** | Streamline order processing, inventory management, and delivery logistics |
| 4 | **Real-Time Inventory Control** | Prevent overselling through live stock tracking and synchronization |
| 5 | **Scalable Architecture** | Build a maintainable codebase that can grow with the business |
| 6 | **Data-Driven Decisions** | Provide comprehensive analytics and reporting for business insights |
| 7 | **Secure Transactions** | Implement robust security measures for payments and customer data |
| 8 | **Multi-Currency Support** | Allow customers to view prices and pay in AU$ or US$ |

---

## 2. System Architecture

### 2.1 Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              ZAMBEZI MEATS SPA                              │
│                         (Shop-First Architecture)                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌───────────────────────┐    ┌─────────────┐    ┌─────────────────────┐   │
│  │     SHOP/STOREFRONT   │    │  CUSTOMER   │    │       ADMIN         │   │
│  │   (Default Landing)   │    │  DASHBOARD  │    │     DASHBOARD       │   │
│  └───────────┬───────────┘    └──────┬──────┘    └──────────┬──────────┘   │
│              │                       │                      │              │
│              └───────────────────────┴──────────────────────┘              │
│                                      │                                      │
│                            ┌─────────▼─────────┐                           │
│                            │   API GATEWAY     │                           │
│                            │   (REST API)      │                           │
│                            └─────────┬─────────┘                           │
│                                      │                                      │
└──────────────────────────────────────┼──────────────────────────────────────┘
                                       │
┌──────────────────────────────────────┼──────────────────────────────────────┐
│                            BACKEND SERVICES                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │  Auth    │  │ Products │  │  Orders  │  │ Inventory│  │ Delivery │      │
│  │ Service  │  │ Service  │  │ Service  │  │ Service  │  │ Service  │      │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘      │
│       └─────────────┴─────────────┴─────────────┴─────────────┘            │
│                                      │                                      │
│                            ┌─────────▼─────────┐                           │
│                            │   MySQL Database  │                           │
│                            └───────────────────┘                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Design Principles

| Principle | Implementation |
|-----------|----------------|
| **Shop-First Approach** | No landing page - users see products immediately upon visiting |
| **SPA Architecture** | Dashboard-based interface with child modal windows |
| **Responsive Design** | Mobile-first approach, adapts to all screen sizes |
| **Real-Time Sync** | Long-polling/SSE for real-time updates (WebSocket alternative) |
| **Headless Modals** | Minimize page navigation, use tabs and modal dialogs |
| **Performance First** | Lazy loading, image optimization, caching strategies |
| **Currency Flexibility** | AU$ default with US$ option for international customers |

---

## 3. Data & System Workflow

### 3.1 Customer Order Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                           CUSTOMER ORDER WORKFLOW                            │
│                        (Shop-First - No Landing Page)                        │
└──────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐
  │  SHOP   │────▶│  ADD TO │────▶│CHECKOUT │────▶│ PAYMENT │────▶│  ORDER  │
  │(Landing)│     │  CART   │     │ DETAILS │     │ PROCESS │     │CONFIRMED│
  └─────────┘     └─────────┘     └─────────┘     └─────────┘     └─────────┘
       │                               │               │               │
       ▼                               ▼               ▼               ▼
  ┌─────────┐                    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │ Filter  │                    │ Address │    │ Stripe  │    │  Email  │
  │ Search  │                    │Validate │    │ PayPal  │    │  Alert  │
  │ Compare │                    │Delivery │    │Afterpay │    │Dashboard│
  │ AU$/US$ │                    │  Fees   │    │  Cash   │    │  Alert  │
  └─────────┘                    │Currency │    │AU$/US$  │    └─────────┘
                                 └─────────┘    └─────────┘
```

### 3.2 Order Processing Flow (Staff)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                          ORDER PROCESSING WORKFLOW                           │
│                    (Combined Packing & Delivery by Staff)                    │
└──────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐     ┌─────────┐
  │  NEW    │────▶│ACCEPTED │────▶│PREPARING│────▶│  OUT    │────▶│DELIVERED│
  │  ORDER  │     │         │     │& PACKING│     │DELIVERY │     │  (POD)  │
  └─────────┘     └─────────┘     └─────────┘     └─────────┘     └─────────┘
       │               │               │               │               │
       ▼               ▼               ▼               ▼               ▼
  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
  │Dashboard│    │ Stock   │    │ Staff   │    │ Route   │    │Complete │
  │  Alert  │    │ Reserve │    │Processes│    │ Optimize│    │ Order   │
  │  Email  │    │         │    │ Order   │    │ Track   │    │ Close   │
  └─────────┘    └─────────┘    └─────────┘    └─────────┘    └─────────┘
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

### 4.1 Role Definitions (Simplified - 4 Roles)

| Role | Description | Access Level |
|------|-------------|--------------|
| **Guest** | Unauthenticated visitor browsing the shop | Public shop only |
| **Customer** | Registered user who can place orders and track deliveries | Customer Dashboard |
| **Staff** | Combined role for packing, delivery, and daily operations | Staff Dashboard |
| **Admin** | Full system control including management and configuration | Admin Dashboard |

### 4.2 Role Hierarchy

```
                    ┌─────────────────┐
                    │      ADMIN      │
                    │   (Level 3)     │
                    │ Full Control    │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │      STAFF      │
                    │   (Level 2)     │
                    │ Packing+Delivery│
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │    CUSTOMER     │
                    │   (Level 1)     │
                    │ Shopping+Orders │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │     GUEST       │
                    │   (Level 0)     │
                    │ Browse Only     │
                    └─────────────────┘
```

### 4.3 Role Responsibilities Detail

#### Guest
- Browse products immediately (shop is the landing page)
- View product details, prices, and availability
- Add items to cart
- Complete checkout (guest checkout available)
- Contact the store
- Register for an account
- Select preferred currency (AU$/US$)

#### Customer
- All Guest capabilities
- Access personal dashboard
- Track order status and history
- Manage delivery addresses
- Reorder previous purchases
- Manage wishlist
- Submit support tickets
- View and manage notifications
- Set currency preference

#### Staff
- Access Staff dashboard
- View and process assigned orders
- Update order status (preparing → packed → out for delivery → delivered)
- View inventory levels
- Record waste/damage
- Capture proof of delivery (signature/photo)
- View optimized delivery routes
- Log personal activity
- Quick stock lookups

#### Admin
- Full system access
- User and staff management
- Product and category management
- Complete inventory control
- Order lifecycle management (accept, edit, cancel, refund)
- Delivery zone and fee configuration
- Financial reports and analytics
- System settings and integrations
- Audit logs and activity monitoring
- Promotions and discount management
- Backup and maintenance
- Currency exchange rate management

---

## 5. System Modules

### 5.1 Module Overview by Role

#### Guest/Public Modules (Shop-First - No Landing Page)
| Module | Description |
|--------|-------------|
| **Shop** | Product catalog as the main landing page - immediate product browsing |
| **Product Detail** | Individual product modal/page with images, description, pricing |
| **Cart** | Shopping cart management (slide-out panel) |
| **Checkout** | Streamlined checkout process |
| **Quick View** | Product quick-view modal without leaving shop |
| **Search** | Global product search with filters |
| **Categories** | Category navigation sidebar/menu |
| **Contact** | Contact form modal |
| **About** | About us modal/section |
| **Currency Selector** | AU$/US$ toggle in header |

#### Customer Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Dashboard home with recent orders and quick actions |
| **My Orders** | Order history, tracking, and reorder functionality |
| **Order Detail** | Individual order view with status timeline |
| **My Profile** | Personal information and preferences (including currency) |
| **Addresses** | Delivery address management |
| **Wishlist** | Saved products for future purchase |
| **Notifications** | Order updates and alerts |
| **Support** | Help center and ticket submission |

#### Staff Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | Daily tasks summary, pending orders, deliveries count |
| **Orders Queue** | List of orders to prepare and pack |
| **Order Processing** | Order details, packaging checklist, status updates |
| **Deliveries** | Assigned deliveries with route map |
| **Delivery Detail** | Individual delivery info and POD capture |
| **Stock Check** | Quick inventory lookup |
| **Waste Log** | Record damaged/expired items |
| **My Activity** | Personal activity and performance log |

#### Admin Dashboard Modules
| Module | Description |
|--------|-------------|
| **Overview** | KPIs, alerts, revenue charts, quick actions |
| **Orders** | Full order management (all orders, filters, actions) |
| **Products** | Product and category management |
| **Inventory** | Stock levels, adjustments, expiry tracking, waste |
| **Customers** | Customer database and CRM |
| **Staff** | Staff account management and activity monitoring |
| **Deliveries** | All delivery operations, zone management, fee config |
| **Finance** | Revenue, payments, tax, financial reports |
| **Reports** | Sales analytics, inventory reports, custom reports |
| **Promotions** | Discounts, coupons, promotional campaigns |
| **Settings** | Store hours, delivery zones, payment gateways, currency rates |
| **Audit Logs** | Complete activity and change logs |
| **Integrations** | Third-party services (payments, email, maps) |
| **System** | Backup, maintenance, health monitoring |

---

## 6. Permissions Matrix

### 6.1 Shop & Public Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Shop (Landing) | ✅ | ✅ | ✅ | ✅ |
| View Products | ✅ | ✅ | ✅ | ✅ |
| View Product Details | ✅ | ✅ | ✅ | ✅ |
| View Prices | ✅ | ✅ | ✅ | ✅ |
| Switch Currency (AU$/US$) | ✅ | ✅ | ✅ | ✅ |
| Add to Cart | ✅ | ✅ | ❌ | ❌ |
| Checkout | ✅ | ✅ | ❌ | ❌ |
| Use Wishlist | ❌ | ✅ | ❌ | ❌ |
| Submit Contact Form | ✅ | ✅ | ❌ | ❌ |
| Create Account | ✅ | ❌ | ❌ | ❌ |

### 6.2 Customer Account Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| Access Customer Dashboard | ❌ | ✅ | ❌ | ✅ |
| View Own Orders | ❌ | ✅ | ❌ | ✅ |
| Track Own Orders | ❌ | ✅ | ❌ | ✅ |
| Cancel Own Orders* | ❌ | ✅ | ❌ | ✅ |
| Request Refund | ❌ | ✅ | ❌ | ✅ |
| Manage Own Profile | ❌ | ✅ | ✅ | ✅ |
| Manage Own Addresses | ❌ | ✅ | ❌ | ✅ |
| View Order History | ❌ | ✅ | ❌ | ✅ |
| Reorder Past Orders | ❌ | ✅ | ❌ | ❌ |
| Manage Wishlist | ❌ | ✅ | ❌ | ❌ |
| Submit Support Ticket | ❌ | ✅ | ❌ | ✅ |
| Set Currency Preference | ❌ | ✅ | ✅ | ✅ |

*\* Only before order is processed*

### 6.3 Order Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View All Orders | ❌ | ❌ | ❌ | ✅ |
| View Assigned Orders | ❌ | ❌ | ✅ | ✅ |
| Accept New Orders | ❌ | ❌ | ❌ | ✅ |
| Reject Orders | ❌ | ❌ | ❌ | ✅ |
| Update Order Status | ❌ | ❌ | ✅* | ✅ |
| Assign Orders to Staff | ❌ | ❌ | ❌ | ✅ |
| Edit Order Details | ❌ | ❌ | ❌ | ✅ |
| Cancel Orders | ❌ | ❌ | ❌ | ✅ |
| Process Refunds | ❌ | ❌ | ❌ | ✅ |
| View Order Analytics | ❌ | ❌ | ❌ | ✅ |
| Export Orders (PDF) | ❌ | ❌ | ❌ | ✅ |
| Print Packing Slip | ❌ | ❌ | ✅ | ✅ |

*\* Limited to: Preparing → Packed → Out for Delivery → Delivered*

### 6.4 Product Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Products (Admin Panel) | ❌ | ❌ | ❌ | ✅ |
| Create Products | ❌ | ❌ | ❌ | ✅ |
| Edit Products | ❌ | ❌ | ❌ | ✅ |
| Delete Products | ❌ | ❌ | ❌ | ✅ |
| Manage Categories | ❌ | ❌ | ❌ | ✅ |
| Set Pricing (AU$/US$) | ❌ | ❌ | ❌ | ✅ |
| Manage Product Images | ❌ | ❌ | ❌ | ✅ |
| Set Product Availability | ❌ | ❌ | ❌ | ✅ |
| Bulk Import Products | ❌ | ❌ | ❌ | ✅ |
| Export Product Data (PDF) | ❌ | ❌ | ❌ | ✅ |
| Manage Featured Products | ❌ | ❌ | ❌ | ✅ |

### 6.5 Inventory Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Stock Levels | ❌ | ❌ | ✅ | ✅ |
| Receive Stock | ❌ | ❌ | ❌ | ✅ |
| Adjust Stock | ❌ | ❌ | ❌ | ✅ |
| Record Waste/Damage | ❌ | ❌ | ✅ | ✅ |
| Set Low Stock Alerts | ❌ | ❌ | ❌ | ✅ |
| Manage Expiry Dates | ❌ | ❌ | ❌ | ✅ |
| View Inventory Reports | ❌ | ❌ | ❌ | ✅ |
| Export Inventory Data (PDF) | ❌ | ❌ | ❌ | ✅ |

### 6.6 Delivery Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View All Deliveries | ❌ | ❌ | ❌ | ✅ |
| View Assigned Deliveries | ❌ | ❌ | ✅ | ✅ |
| Update Delivery Status | ❌ | ❌ | ✅ | ✅ |
| Capture POD (Photo/Signature) | ❌ | ❌ | ✅ | ✅ |
| View Route Map | ❌ | ❌ | ✅ | ✅ |
| Manage Delivery Zones | ❌ | ❌ | ❌ | ✅ |
| Set Delivery Fees | ❌ | ❌ | ❌ | ✅ |
| View Delivery Analytics | ❌ | ❌ | ❌ | ✅ |
| Assign Deliveries to Staff | ❌ | ❌ | ❌ | ✅ |

### 6.7 Customer Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Customer List | ❌ | ❌ | ❌ | ✅ |
| View Customer Details | ❌ | ❌ | ✅* | ✅ |
| Edit Customer Info | ❌ | ❌ | ❌ | ✅ |
| Delete Customer | ❌ | ❌ | ❌ | ✅ |
| View Purchase History | ❌ | ❌ | ❌ | ✅ |
| Manage Customer Notes | ❌ | ❌ | ❌ | ✅ |
| Handle Support Tickets | ❌ | ❌ | ❌ | ✅ |
| Export Customer Data (PDF) | ❌ | ❌ | ❌ | ✅ |

*\* Limited to delivery-relevant information (name, address, phone)*

### 6.8 Staff Management Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Staff List | ❌ | ❌ | ❌ | ✅ |
| Create Staff Account | ❌ | ❌ | ❌ | ✅ |
| Edit Staff Details | ❌ | ❌ | ❌ | ✅ |
| Deactivate Staff | ❌ | ❌ | ❌ | ✅ |
| Delete Staff Account | ❌ | ❌ | ❌ | ✅ |
| View Staff Activity | ❌ | ❌ | ❌ | ✅ |
| View Own Activity | ❌ | ❌ | ✅ | ✅ |

### 6.9 Financial & Reports Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View Sales Reports | ❌ | ❌ | ❌ | ✅ |
| View Financial Summary | ❌ | ❌ | ❌ | ✅ |
| View Payment Records | ❌ | ❌ | ❌ | ✅ |
| Process Manual Payments | ❌ | ❌ | ❌ | ✅ |
| View Tax Reports | ❌ | ❌ | ❌ | ✅ |
| Export Financial Data (PDF) | ❌ | ❌ | ❌ | ✅ |
| Configure Payment Settings | ❌ | ❌ | ❌ | ✅ |
| Manage Currency Exchange Rates | ❌ | ❌ | ❌ | ✅ |

### 6.10 System Administration Permissions

| Permission | Guest | Customer | Staff | Admin |
|------------|:-----:|:--------:|:-----:|:-----:|
| View System Settings | ❌ | ❌ | ❌ | ✅ |
| Edit Store Settings | ❌ | ❌ | ❌ | ✅ |
| Manage Integrations | ❌ | ❌ | ❌ | ✅ |
| View Audit Logs | ❌ | ❌ | ❌ | ✅ |
| Manage Backups | ❌ | ❌ | ❌ | ✅ |
| System Maintenance | ❌ | ❌ | ❌ | ✅ |
| Configure Email Templates | ❌ | ❌ | ❌ | ✅ |
| Manage Promotions | ❌ | ❌ | ❌ | ✅ |
| Configure Currency Settings | ❌ | ❌ | ❌ | ✅ |

---

## 7. Technical Stack

### 7.1 Backend Framework Comparison

> **⚠️ DECISION REQUIRED: Choose your preferred backend framework**

#### Option A: Laravel 11 (PHP)

| Aspect | Details |
|--------|---------|
| **Language** | PHP 8.2+ |
| **Type** | Full-stack MVC Framework |
| **Learning Curve** | Moderate |
| **Performance** | Good (with optimization) |
| **Hosting Compatibility** | ✅ Excellent - Native CyberPanel/cPanel support |

**Pros:**
- ✅ Native shared hosting support (CyberPanel, cPanel, DirectAdmin)
- ✅ Excellent ORM (Eloquent) for database operations
- ✅ Built-in authentication (Sanctum for SPA)
- ✅ Mature ecosystem with extensive packages
- ✅ Queue system for background jobs
- ✅ Blade templating (if needed for emails/PDFs)
- ✅ Laravel Cashier for subscription management
- ✅ Great documentation and community

**Cons:**
- ❌ Slower than Node.js for concurrent requests
- ❌ PHP shared hosting resource limits
- ❌ Less suitable for real-time features (needs workarounds)

**Best For:** Traditional shared hosting deployment, rapid development

---

#### Option B: Node.js + Express/Fastify (JavaScript)

| Aspect | Details |
|--------|---------|
| **Language** | JavaScript/TypeScript |
| **Type** | Minimal Framework (build your stack) |
| **Learning Curve** | Moderate to High |
| **Performance** | Excellent (event-driven, non-blocking) |
| **Hosting Compatibility** | ⚠️ Requires VPS or Node.js hosting |

**Pros:**
- ✅ Excellent performance for concurrent requests
- ✅ Same language as frontend (JavaScript)
- ✅ Better for real-time features (SSE/polling)
- ✅ Huge npm ecosystem
- ✅ TypeScript support for type safety
- ✅ Better JSON handling (native)
- ✅ Fastify is extremely fast

**Cons:**
- ❌ NOT supported on standard CyberPanel/cPanel shared hosting
- ❌ Requires VPS or specialized Node.js hosting
- ❌ More manual setup (auth, ORM, validation)
- ❌ Callback/Promise complexity
- ❌ Less structured than Laravel

**Best For:** VPS hosting, real-time apps, high-traffic scenarios

---

### 7.2 Comparison Matrix

| Criteria | Laravel (PHP) | Node.js (Express/Fastify) | Winner |
|----------|---------------|---------------------------|--------|
| **CyberPanel Hosting** | ✅ Native | ❌ Not supported | Laravel |
| **Shared Hosting** | ✅ Yes | ❌ No (needs VPS) | Laravel |
| **Development Speed** | ✅ Fast (batteries included) | ⚠️ Moderate | Laravel |
| **Performance** | ⚠️ Good | ✅ Excellent | Node.js |
| **Real-Time Updates** | ⚠️ SSE/Polling | ✅ Native event loop | Node.js |
| **API Development** | ✅ Excellent | ✅ Excellent | Tie |
| **Database ORM** | ✅ Eloquent (excellent) | ⚠️ Prisma/Sequelize | Laravel |
| **Authentication** | ✅ Built-in (Sanctum) | ⚠️ Manual (Passport.js) | Laravel |
| **PDF Generation** | ✅ DomPDF/TCPDF | ✅ Puppeteer/PDFKit | Tie |
| **Deployment Ease** | ✅ Upload & run | ⚠️ Process manager needed | Laravel |
| **Community/Support** | ✅ Excellent | ✅ Excellent | Tie |
| **Cost (Hosting)** | ✅ Cheaper (shared) | ⚠️ Higher (VPS) | Laravel |

### 7.3 Recommendation

**🏆 RECOMMENDED: Laravel 11 (PHP)**

Given the requirements:
1. ✅ CyberPanel hosting is specified - Laravel works natively
2. ✅ Shared hosting is cost-effective for a small butcher shop
3. ✅ Faster development with built-in features
4. ✅ E-commerce packages available (payment gateways, inventory)
5. ✅ No additional infrastructure needed

**Alternative Consideration:**
If you plan to scale significantly or need advanced real-time features, consider a VPS with Node.js in the future. Laravel will serve well for initial launch and can handle the expected traffic.

---

### 7.4 Recommended Technology Stack (Laravel-Based)

#### Frontend
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Framework** | Vue.js 3 + Vite | Lightweight, fast, SPA-optimized |
| **UI Library** | Tailwind CSS + Headless UI | Modern, customizable components |
| **State Management** | Pinia | Official Vue.js store |
| **HTTP Client** | Axios | Robust API communication |
| **Charts** | Chart.js + Vue-ChartJS | Lightweight analytics |
| **PDF Generation** | jsPDF + html2canvas | Client-side PDF export |
| **Currency** | currency.js | Accurate currency calculations |
| **Form Validation** | VeeValidate + Yup | Form handling |
| **Icons** | Heroicons / Lucide | Modern icon sets |
| **Animations** | GSAP / Vue Transitions | Smooth animations |
| **Date Handling** | Day.js | Lightweight dates |

#### Backend (Laravel)
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Runtime** | PHP 8.2+ | CyberPanel native |
| **Framework** | Laravel 11 | Robust MVC, API-ready |
| **API Auth** | Laravel Sanctum | SPA authentication |
| **Database** | MySQL 8.0 | Reliable, well-supported |
| **Caching** | File Cache / Redis | Query optimization |
| **Queue** | Laravel Queue (Database) | Background jobs |
| **Real-Time** | Server-Sent Events (SSE) | Real-time updates |
| **PDF Server-Side** | DomPDF | Server PDF generation |
| **Currency Exchange** | ExchangeRate-API | Live currency rates |

#### Alternative Backend (Node.js) - If VPS Chosen
| Component | Technology | Justification |
|-----------|------------|---------------|
| **Runtime** | Node.js 20 LTS | Latest stable |
| **Framework** | Fastify | High performance |
| **API Auth** | Passport.js + JWT | Token authentication |
| **ORM** | Prisma | Type-safe database |
| **Database** | MySQL 8.0 | Consistency |
| **Caching** | Redis | Fast caching |
| **Real-Time** | Native EventEmitter | SSE streaming |
| **PDF** | Puppeteer | High-quality PDFs |

#### Third-Party Integrations
| Service | Provider | Purpose |
|---------|----------|---------|
| **Payments** | Stripe | Primary gateway (AU$/US$) |
| **Payments** | PayPal | Alternative (AU$/US$) |
| **Payments** | Afterpay | Buy-now-pay-later |
| **Email** | SMTP / SendGrid | Transactional emails |
| **Maps** | Google Maps API | Delivery routes |
| **Address** | Google Places API | Australian address autocomplete |
| **Currency** | ExchangeRate-API | AU$/US$ conversion |

---

## 8. Hosting Environment Recommendation

### 8.1 Hosting Options Comparison

| Hosting Type | Provider Examples | Laravel | Node.js | Monthly Cost | Best For |
|--------------|-------------------|---------|---------|--------------|----------|
| **Shared (cPanel)** | Hostinger, SiteGround | ✅ Yes | ❌ No | $5-15 | Small shops, starting out |
| **CyberPanel VPS** | Contabo, Vultr | ✅ Yes | ✅ Yes | $10-30 | Growing business |
| **Managed Laravel** | Laravel Forge + DigitalOcean | ✅ Yes | ❌ No | $20-50 | Laravel-focused |
| **Cloud PaaS** | Railway, Render | ✅ Yes | ✅ Yes | $20-50 | Auto-scaling |
| **AWS/GCP** | AWS Lightsail, GCP | ✅ Yes | ✅ Yes | $20-100+ | Enterprise |

### 8.2 Recommended Hosting Setup

**🏆 RECOMMENDED: CyberPanel on VPS**

| Component | Recommendation | Cost (USD/month) |
|-----------|----------------|------------------|
| **VPS Provider** | Contabo VPS S | ~$7 |
| **Alternative** | Vultr High Frequency | ~$12 |
| **Control Panel** | CyberPanel (Free) | $0 |
| **SSL** | Let's Encrypt (Free) | $0 |
| **CDN** | Cloudflare (Free tier) | $0 |
| **Backup** | CyberPanel + Offsite | ~$3 |
| **Domain** | Existing (zambezimeats.com) | ~$15/year |

**Estimated Total: $10-20/month**

### 8.3 Recommended VPS Specifications

| Spec | Minimum | Recommended |
|------|---------|-------------|
| **CPU** | 2 vCPU | 4 vCPU |
| **RAM** | 4 GB | 8 GB |
| **Storage** | 50 GB SSD | 100 GB NVMe |
| **Bandwidth** | 1 TB | Unlimited |
| **Location** | Sydney, Australia | Sydney, Australia |

### 8.4 Why CyberPanel VPS?

1. ✅ **LiteSpeed Web Server** - 10x faster than Apache
2. ✅ **Free SSL** - Let's Encrypt integration
3. ✅ **One-Click Laravel** - Easy deployment
4. ✅ **LSCache** - Built-in caching
5. ✅ **Email Server** - Built-in email
6. ✅ **Free** - No license cost
7. ✅ **Low Latency** - Australian data center option

---

## 9. Color Palette & Branding

### 9.1 Primary Colors (Vibrant Butcher Theme)

| Color | Hex Code | Usage |
|-------|----------|-------|
| **Zambezi Red** | `#DC2626` | Primary brand color, CTAs, highlights |
| **Dark Red** | `#991B1B` | Hover states, dark accents |
| **Light Red** | `#FEE2E2` | Backgrounds, alerts |
| **Charcoal** | `#1F2937` | Primary text, headers |
| **Warm Gray** | `#6B7280` | Secondary text |
| **Off White** | `#F9FAFB` | Page backgrounds |
| **Pure White** | `#FFFFFF` | Cards, modals |
| **Gold Accent** | `#D97706` | Premium highlights, badges |
| **Success Green** | `#059669` | Success states, availability |
| **Warning Amber** | `#F59E0B` | Warnings, low stock |

### 9.2 Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| **Headings** | Playfair Display | 700 | 24-48px |
| **Body** | Inter | 400, 500 | 14-16px |
| **Labels** | Inter | 600 | 12-14px |
| **Buttons** | Inter | 600 | 14-16px |
| **Prices** | Inter | 700 | 16-24px |

---

## 10. Business Rules

### 10.1 Pricing & Orders

| Rule | Value |
|------|-------|
| Pricing Model | Fixed price per kilogram |
| Minimum Order (Delivery) | AU$100 / ~US$65 |
| Free Delivery Threshold | AU$100+ (specified postcodes) |
| Delivery Fee Calculation | $0.15/km for outside zones |
| **Currencies Supported** | **AU$ (default) and US$** |
| Exchange Rate Source | ExchangeRate-API (daily update) |

### 10.2 Currency Handling

| Aspect | Implementation |
|--------|----------------|
| Default Currency | AU$ (Australian Dollar) |
| Alternative Currency | US$ (United States Dollar) |
| Display | User-selectable in header |
| Prices Stored | AU$ (base currency) |
| Conversion | Real-time via API |
| Checkout | Payment in selected currency |
| Rounding | Nearest cent |

### 10.3 Operating Hours

| Day | Hours |
|-----|-------|
| Monday - Sunday | 7:00 AM - 6:00 PM AEST |

### 10.4 Payment Methods

| Method | AU$ | US$ |
|--------|:---:|:---:|
| Credit/Debit Cards (Visa, Mastercard) | ✅ | ✅ |
| Stripe | ✅ | ✅ |
| PayPal | ✅ | ✅ |
| Afterpay | ✅ | ❌ |
| Cash on Delivery | ✅ | ❌ |

---

## 11. PDF Export Template

### 11.1 Standard PDF Layout

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

## 12. Development Phases

### Phase 1: Foundation (Weeks 1-2)
- [ ] Project setup and repository initialization
- [ ] Database schema design and migrations
- [ ] Authentication system (Guest, Customer, Staff, Admin)
- [ ] Basic API structure
- [ ] Frontend scaffolding with shop-first routing

### Phase 2: Core Shop Features (Weeks 3-5)
- [ ] Product catalog (shop as landing page)
- [ ] Product detail modals
- [ ] Shopping cart (slide-out panel)
- [ ] Search and filtering
- [ ] Currency selector (AU$/US$)
- [ ] User registration/login

### Phase 3: Orders & Payments (Weeks 6-7)
- [ ] Checkout process (multi-currency)
- [ ] Payment gateway integrations (Stripe, PayPal, Afterpay)
- [ ] Order management system
- [ ] Email notifications
- [ ] Order tracking

### Phase 4: Staff & Operations (Weeks 8-9)
- [ ] Staff dashboard
- [ ] Order processing workflow
- [ ] Delivery management
- [ ] Route optimization
- [ ] Proof of delivery capture
- [ ] Inventory quick-look

### Phase 5: Administration (Weeks 10-11)
- [ ] Admin dashboard with KPIs
- [ ] User management
- [ ] Full inventory control
- [ ] Reports and analytics
- [ ] Promotions system
- [ ] System settings

### Phase 6: Polish & Deploy (Week 12)
- [ ] Performance optimization
- [ ] Security audit
- [ ] Testing and bug fixes
- [ ] Documentation
- [ ] CyberPanel VPS deployment
- [ ] Go-live

---

## 13. Directory Structure

```
zambezi-meats/
├── .github/
│   ├── workflows/          # GitHub Actions CI/CD
│   ├── prompts/            # AI prompts and documentation
│   └── user_requirements.txt
├── backend/                # Laravel Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Exports/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   └── tests/
├── frontend/               # Vue.js Application
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   │   ├── shop/       # Shop-first components
│   │   │   ├── cart/
│   │   │   ├── customer/
│   │   │   ├── staff/
│   │   │   └── admin/
│   │   ├── composables/
│   │   ├── layouts/
│   │   ├── pages/
│   │   ├── router/
│   │   ├── stores/
│   │   └── services/
│   └── public/
├── docs/
├── .gitignore
└── README.md
```

---

## 14. Success Metrics

| Metric | Target |
|--------|--------|
| Page Load Time (Shop) | < 1.5 seconds |
| Time to First Product View | Instant (no landing page) |
| Mobile Responsiveness | 100% functionality |
| Uptime | 99.9% |
| Order Processing Time | < 24 hours |
| Customer Satisfaction | > 4.5/5 rating |
| Cart Abandonment | < 25% |
| Currency Conversion Accuracy | 99.99% |

---

## 15. Document Information

| Field | Value |
|-------|-------|
| **Document Version** | 2.0 |
| **Created Date** | December 12, 2025 |
| **Last Updated** | December 12, 2025 |
| **Author** | bguvava |
| **Status** | Draft - Pending Tech Stack Decision |
| **Next Review** | Upon backend framework selection |

---

## 16. Decision Checklist

Before proceeding, please confirm:

- [ ] **Backend Framework**: Laravel (recommended) or Node.js?
- [ ] **Hosting**: CyberPanel VPS (recommended) or alternative?
- [ ] **Currency Default**: AU$ with US$ option confirmed?
- [ ] **Roles Confirmed**: Guest, Customer, Staff, Admin?
- [ ] **Shop-First Approach**: No landing page confirmed?

---

*This document serves as the foundational blueprint for the Zambezi Meats online butchery store development. All specifications are subject to refinement during the development process.*
