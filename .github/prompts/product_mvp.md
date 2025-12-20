# Zambezi Meats Online Butchery Store - MVP Requirements Index

> **Document Type:** MVP Requirements Index  
> **Total Requirements:** 396 across 17 modules  
> **Split into:** 9 Parts for manageable reading  
> **Developer:** bguvava (www.bguvava.com)

---

## Project Overview

| Field                | Value                                                     |
| -------------------- | --------------------------------------------------------- |
| **Project**          | Zambezi Meats Online Butchery Store                       |
| **Company**          | Zambezi Meats                                             |
| **Address**          | 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia |
| **Logo**             | `.github/official_logo.png`                               |
| **Status**           | MVP Phase                                                 |
| **Default Currency** | AU$ (AUD) with US$ support via ExchangeRate-API           |

---

## Technology Stack

### Backend

| Component      | Technology                              |
| -------------- | --------------------------------------- |
| Framework      | Laravel 12.x                            |
| PHP Version    | 8.2+                                    |
| Database       | MySQL 8.0 (database: `my_zambezimeats`) |
| Authentication | Laravel Sanctum (cookie-based)          |
| ORM            | Eloquent                                |
| API Version    | All endpoints under `/api/v1/`          |
| Real-Time      | Server-Sent Events (SSE)                |

### Frontend

| Component        | Technology                 |
| ---------------- | -------------------------- |
| Framework        | Vue.js 3 (Composition API) |
| Build Tool       | Vite                       |
| CSS              | Tailwind CSS               |
| UI Components    | shadcn/ui + Headless UI    |
| State Management | Pinia                      |
| HTTP Client      | Axios                      |

### Infrastructure

| Component  | Technology                                 |
| ---------- | ------------------------------------------ |
| Hosting    | CyberPanel VPS                             |
| Web Server | OpenLiteSpeed                              |
| SSL        | Let's Encrypt                              |
| Payments   | Stripe, PayPal, Afterpay, Cash on Delivery |

---

## Document Parts

The MVP requirements have been split into 9 parts for easier reading and navigation:

| Part       | File                                                        | Modules                        | Requirements |
| ---------- | ----------------------------------------------------------- | ------------------------------ | ------------ |
| **Part 1** | [Foundation](mvp/part1-foundation.md)                       | DEV-ENV + PROJ-INIT + DATABASE | 49           |
| **Part 2** | [Auth & Landing](mvp/part2-auth-landing.md)                 | AUTH + LANDING                 | 40           |
| **Part 3** | [Shop & Cart](mvp/part3-shop-cart.md)                       | SHOP + CART                    | 51           |
| **Part 4** | [Checkout](mvp/part4-checkout.md)                           | CHECKOUT                       | 30           |
| **Part 5** | [Customer & Staff](mvp/part5-customer-staff.md)             | CUSTOMER + STAFF               | 47           |
| **Part 6** | [Admin Dashboard](mvp/part6-admin.md)                       | ADMIN                          | 28           |
| **Part 7** | [Inventory & Delivery](mvp/part7-inventory-delivery.md)     | INVENTORY + DELIVERY           | 37           |
| **Part 8** | [Reports & Settings](mvp/part8-reports-settings.md)         | REPORTS + SETTINGS             | 52           |
| **Part 9** | [Deployment & Checklist](mvp/part9-deployment-checklist.md) | DEPLOYMENT + CHECKLIST         | 62           |
|            |                                                             | **TOTAL**                      | **396**      |

---

## Requirements Summary by Module

| Module                  | ID Prefix | Count | Part |
| ----------------------- | --------- | ----- | ---- |
| Development Environment | DEV-ENV   | 10    | 1    |
| Project Initialization  | PROJ-INIT | 15    | 1    |
| Database Schema         | DB        | 24    | 1    |
| Authentication          | AUTH      | 20    | 2    |
| Landing Page            | LAND      | 20    | 2    |
| Shop (Product Catalog)  | SHOP      | 28    | 3    |
| Shopping Cart           | CART      | 23    | 3    |
| Checkout                | CHK       | 30    | 4    |
| Customer Dashboard      | CUST      | 23    | 5    |
| Staff Dashboard         | STAFF     | 24    | 5    |
| Admin Dashboard         | ADMIN     | 28    | 6    |
| Inventory Management    | INV       | 18    | 7    |
| Delivery Management     | DEL       | 19    | 7    |
| Reports & Analytics     | RPT       | 22    | 8    |
| System Settings         | SET       | 30    | 8    |
| Deployment              | DEP       | 30    | 9    |
| Final Checklist         | CHK       | 32    | 9    |

---

## User Roles

| Role         | Description             | Access Level                      |
| ------------ | ----------------------- | --------------------------------- |
| **Guest**    | Unauthenticated visitor | Public pages only                 |
| **Customer** | Registered shopper      | Shop, orders, profile             |
| **Staff**    | Zambezi Meats employee  | Orders, deliveries, waste logging |
| **Admin**    | Full system control     | All features + settings           |

---

## Key Features

### Customer Features

- Browse products by category with filtering
- Persistent shopping cart
- Multiple payment methods (Stripe, PayPal, Afterpay, Cash)
- Order tracking with real-time status updates
- Multi-address support with Google Places autocomplete
- Order history and reordering
- Wishlist functionality

### Staff Features

- Order queue management
- Delivery assignment and tracking
- Proof of delivery (signature + photo)
- Waste/damaged goods logging
- Real-time order notifications (SSE)

### Admin Features

- Complete dashboard with KPIs
- Product and category management
- User management (customers + staff)
- Inventory management with alerts
- Delivery zone configuration
- Reports & analytics with PDF export
- System settings configuration

---

## Quick Links

### Start Reading

1. **[Part 1: Foundation](mvp/part1-foundation.md)** - Start here for environment setup and database schema
2. **[Part 2: Auth & Landing](mvp/part2-auth-landing.md)** - Authentication flows and landing page
3. **[Part 3: Shop & Cart](mvp/part3-shop-cart.md)** - Product catalog and shopping cart

### Implementation Priority

- **P0 (Critical):** Parts 1, 2, 9
- **P1 (High):** Parts 3, 4, 5, 6, 7
- **P2 (Medium):** Part 8

---

## Document Information

| Field            | Value                         |
| ---------------- | ----------------------------- |
| **Created**      | December 2024                 |
| **Last Updated** | December 2024                 |
| **Author**       | bguvava                       |
| **Format**       | Markdown (split into 9 parts) |
| **Location**     | `.github/prompts/mvp/`        |

---

> **Note:** Each part file contains detailed requirements, API endpoints, wireframes, and test criteria. Navigate using the links above or browse the `mvp/` directory.

---

## Original Full Document (Archived Below)

The original monolithic document content is preserved below for reference.

---

## 1. Introduction

### 1.1 Purpose

This Product Requirement Document (PRD) defines the Minimum Viable Product (MVP) for the Zambezi Meats online butchery store. It provides a step-by-step, module-by-module breakdown from development environment setup to production deployment.

### 1.2 Scope

The MVP includes:

- Modern animated landing page with login
- Customer-facing shop with product catalog
- Complete order lifecycle (cart → checkout → delivery)
- Staff dashboard for order processing
- Admin dashboard for business management
- Multi-currency support (AU$/US$)

### 1.3 Design Principles

| Principle               | Implementation                             |
| ----------------------- | ------------------------------------------ |
| **Session Security**    | Auto-logout after 5 minutes of inactivity  |
| **API Versioning**      | All APIs under `/api/v1/` namespace        |
| **Search Optimization** | Limited filters to reduce database queries |
| **Bulk Operations**     | Restricted to activity logs only           |
| **Document Actions**    | View (new tab) and Download options        |
| **Testing Standard**    | 100% test pass rate required per module    |
| **Documentation**       | Stored in `/docs/{module_name}/`           |
| **Tests**               | Stored in `/tests/{module_name}/`          |

### 1.4 Technology Stack

| Layer         | Technology                                               |
| ------------- | -------------------------------------------------------- |
| **Frontend**  | Vue.js 3 + Vite + Tailwind CSS + shadcn/ui + Headless UI |
| **Backend**   | Laravel 12.x + PHP 8.2+ + Laravel Sanctum                |
| **Database**  | MySQL 8.0 (database: `my_zambezimeats`)                  |
| **State**     | Pinia                                                    |
| **HTTP**      | Axios                                                    |
| **Real-Time** | Server-Sent Events (SSE)                                 |

### 1.5 Developer Credits

```
┌─────────────────────────────────────────┐
│   Developed with ❤️ by bguvava          │
│   www.bguvava.com                       │
└─────────────────────────────────────────┘
```

---

## 2. Development Environment Setup

### 2.1 Module Overview

| Field             | Value                 |
| ----------------- | --------------------- |
| **Module Name**   | DEV-ENV               |
| **Priority**      | P0 - Critical         |
| **Dependencies**  | None                  |
| **Documentation** | `/docs/environment/`  |
| **Tests**         | `/tests/environment/` |

### 2.2 Module Objectives

1. Set up consistent development environment across all developers
2. Install and configure all required tools and extensions
3. Establish coding standards and linting rules
4. Configure VS Code for optimal Laravel + Vue.js development

### 2.3 Success Criteria

| Criteria                      | Target                  |
| ----------------------------- | ----------------------- |
| All tools installed           | 100%                    |
| VS Code extensions configured | 100%                    |
| Linting passes                | 0 errors                |
| Environment variables set     | All required            |
| Local server running          | Both frontend & backend |

### 2.4 Requirements

| Requirement ID | Module Name | Description                                | User Story                                                                     | Expected System Behaviour/Outcome                                                                                               | Role      |
| -------------- | ----------- | ------------------------------------------ | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------- | --------- |
| DEV-ENV-001    | Environment | Install PHP 8.2+ with required extensions  | As a developer, I need PHP 8.2+ installed with all Laravel-required extensions | PHP runs with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, gd                                 | Developer |
| DEV-ENV-002    | Environment | Install Composer 2.x                       | As a developer, I need Composer to manage PHP dependencies                     | Composer is globally accessible via CLI                                                                                         | Developer |
| DEV-ENV-003    | Environment | Install Node.js 20 LTS + npm               | As a developer, I need Node.js for frontend build tools                        | Node.js and npm are globally accessible via CLI                                                                                 | Developer |
| DEV-ENV-004    | Environment | Install MySQL 8.0                          | As a developer, I need MySQL for database operations                           | MySQL server running on port 3306, accessible via CLI and GUI                                                                   | Developer |
| DEV-ENV-005    | Environment | Configure VS Code with required extensions | As a developer, I need VS Code configured for Laravel + Vue development        | Extensions installed: Volar, PHP Intelephense, Laravel Extra Intellisense, Tailwind CSS IntelliSense, ESLint, Prettier, GitLens | Developer |
| DEV-ENV-006    | Environment | Set up Git with proper configuration       | As a developer, I need version control configured                              | Git configured with user name, email, and .gitignore                                                                            | Developer |
| DEV-ENV-007    | Environment | Configure ESLint and Prettier              | As a developer, I need consistent code formatting                              | ESLint + Prettier configurations in place, format on save enabled                                                               | Developer |
| DEV-ENV-008    | Environment | Install Laravel Installer                  | As a developer, I need to quickly scaffold Laravel projects                    | `laravel new` command available globally                                                                                        | Developer |
| DEV-ENV-009    | Environment | Set up local hosts entry                   | As a developer, I need a local domain for testing                              | `zambezimeats.local` pointing to 127.0.0.1                                                                                      | Developer |
| DEV-ENV-010    | Environment | Create environment documentation           | As a developer, I need setup instructions for onboarding                       | Complete README with setup steps in `/docs/environment/`                                                                        | Developer |

### 2.5 VS Code Extensions Required

```json
{
  "recommendations": [
    "Vue.volar",
    "bmewburn.vscode-intelephense-client",
    "onecentlin.laravel-extension-pack",
    "bradlc.vscode-tailwindcss",
    "dbaeumer.vscode-eslint",
    "esbenp.prettier-vscode",
    "eamodio.gitlens",
    "formulahendry.auto-rename-tag",
    "christian-kohler.path-intellisense",
    "mikestead.dotenv",
    "EditorConfig.EditorConfig"
  ]
}
```

---

## 3. Project Initialization

### 3.1 Module Overview

| Field             | Value                  |
| ----------------- | ---------------------- |
| **Module Name**   | PROJ-INIT              |
| **Priority**      | P0 - Critical          |
| **Dependencies**  | DEV-ENV                |
| **Documentation** | `/docs/project-init/`  |
| **Tests**         | `/tests/project-init/` |

### 3.2 Module Objectives

1. Initialize Laravel 12.x backend project
2. Initialize Vue.js 3 + Vite frontend project
3. Configure project structure according to specifications
4. Set up API versioning structure
5. Configure CORS and sanctum for SPA authentication

### 3.3 Success Criteria

| Criteria                | Target     |
| ----------------------- | ---------- |
| Laravel project created | ✓          |
| Vue.js project created  | ✓          |
| API versioning in place | `/api/v1/` |
| Sanctum configured      | ✓          |
| CORS configured         | ✓          |
| Both servers running    | ✓          |

### 3.4 Requirements

| Requirement ID  | Module Name  | Description                                       | User Story                                                     | Expected System Behaviour/Outcome                                                     | Role      |
| --------------- | ------------ | ------------------------------------------------- | -------------------------------------------------------------- | ------------------------------------------------------------------------------------- | --------- |
| PROJ-INIT-001   | Project Init | Create Laravel 12.x backend project               | As a developer, I need a Laravel project with proper structure | Laravel project created in `/backend` with standard structure                         | Developer |
| PROJ-INIT-002   | Project Init | Create Vue.js 3 + Vite frontend project           | As a developer, I need a Vue.js SPA project                    | Vue.js project created in `/frontend` with Vite, Tailwind CSS, shadcn/ui              | Developer |
| PROJ-INIT-003   | Project Init | Configure API versioning folder structure         | As a developer, I need versioned API routes                    | Routes organized as `/api/v1/` with versioned controllers                             | Developer |
| PROJ-INIT-004   | Project Init | Install and configure Laravel Sanctum             | As a developer, I need SPA authentication                      | Sanctum installed, configured for cookie-based SPA auth                               | Developer |
| PROJ-INIT-005   | Project Init | Configure CORS for frontend-backend communication | As a developer, I need cross-origin requests to work           | CORS configured to allow frontend origin                                              | Developer |
| PROJ-INIT-006   | Project Init | Set up environment files                          | As a developer, I need separate configs for dev/staging/prod   | `.env.example` with all required variables documented                                 | Developer |
| PROJ-INIT-007   | Project Init | Install frontend dependencies                     | As a developer, I need all frontend packages                   | Pinia, Axios, VeeValidate, Zod, Day.js, currency.js, Vue Sonner, Lucide Vue installed | Developer |
| PROJ-INIT-008   | Project Init | Configure Tailwind CSS with custom theme          | As a developer, I need branded styling                         | Tailwind configured with Zambezi Meats color palette                                  | Developer |
| PROJ-INIT-009   | Project Init | Set up shadcn/ui components                       | As a developer, I need UI component library                    | shadcn/ui initialized with base components                                            | Developer |
| PROJ-INIT-010   | Project Init | Configure Pinia store structure                   | As a developer, I need state management                        | Pinia stores organized by domain (auth, cart, products, etc.)                         | Developer |
| PROJ-INIT-012.x | Project Init | Set up Axios instance with interceptors           | As a developer, I need configured HTTP client                  | Axios instance with base URL, auth interceptors, error handling                       | Developer |
| PROJ-INIT-012   | Project Init | Create base layout components                     | As a developer, I need layout templates                        | Guest layout, Customer layout, Staff layout, Admin layout                             | Developer |
| PROJ-INIT-013   | Project Init | Configure Vue Router with guards                  | As a developer, I need routing with protection                 | Routes defined with auth guards for role-based access                                 | Developer |
| PROJ-INIT-014   | Project Init | Set up session timeout (5 minutes)                | As a developer, I need auto-logout for security                | Session expires after 5 minutes of inactivity, user redirected to login               | Developer |
| PROJ-INIT-015   | Project Init | Create project documentation                      | As a developer, I need project setup docs                      | README.md with complete setup and run instructions                                    | Developer |

### 3.5 Directory Structure

```
zambezi-meats/
├── .github/
│   ├── prompts/
│   │   ├── project_description.md
│   │   └── product_mvp.md
│   └── workflows/
├── backend/                          # Laravel 12.x
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/
│   │   │   │       └── V1/          # API Version 1
│   │   │   │           ├── AuthController.php
│   │   │   │           ├── ProductController.php
│   │   │   │           └── ...
│   │   │   ├── Middleware/
│   │   │   │   ├── SessionTimeout.php
│   │   │   │   └── ...
│   │   │   └── Requests/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Exports/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── routes/
│   │   └── api/
│   │       └── v1.php               # Versioned API routes
│   ├── storage/
│   └── tests/
├── frontend/                         # Vue.js 3 + Vite
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   │   ├── ui/                  # shadcn/ui components
│   │   │   ├── common/
│   │   │   ├── landing/
│   │   │   ├── shop/
│   │   │   ├── cart/
│   │   │   ├── checkout/
│   │   │   ├── customer/
│   │   │   ├── staff/
│   │   │   └── admin/
│   │   ├── composables/
│   │   ├── layouts/
│   │   │   ├── GuestLayout.vue
│   │   │   ├── CustomerLayout.vue
│   │   │   ├── StaffLayout.vue
│   │   │   └── AdminLayout.vue
│   │   ├── pages/
│   │   ├── router/
│   │   ├── stores/
│   │   │   ├── auth.js
│   │   │   ├── cart.js
│   │   │   ├── products.js
│   │   │   ├── orders.js
│   │   │   └── currency.js
│   │   ├── services/
│   │   │   └── api.js
│   │   ├── utils/
│   │   ├── App.vue
│   │   └── main.js
│   └── public/
├── docs/                             # Documentation by module
│   ├── environment/
│   ├── project-init/
│   ├── database/
│   ├── auth/
│   ├── landing/
│   ├── shop/
│   ├── cart/
│   ├── checkout/
│   ├── customer/
│   ├── staff/
│   ├── admin/
│   ├── inventory/
│   ├── delivery/
│   ├── reports/
│   └── settings/
├── tests/                            # Tests by module
│   ├── environment/
│   ├── project-init/
│   ├── database/
│   ├── auth/
│   ├── landing/
│   ├── shop/
│   ├── cart/
│   ├── checkout/
│   ├── customer/
│   ├── staff/
│   ├── admin/
│   ├── inventory/
│   ├── delivery/
│   ├── reports/
│   └── settings/
├── .gitignore
└── README.md
```

---

## 4. Database Design & Setup

### 4.1 Module Overview

| Field             | Value              |
| ----------------- | ------------------ |
| **Module Name**   | DATABASE           |
| **Priority**      | P0 - Critical      |
| **Dependencies**  | PROJ-INIT          |
| **Documentation** | `/docs/database/`  |
| **Tests**         | `/tests/database/` |
| **Database Name** | `my_zambezimeats`  |

### 4.2 Module Objectives

1. Design normalized database schema
2. Create all migrations in logical order
3. Implement proper relationships and constraints
4. Create seeders for testing data
5. Document all tables and relationships

### 4.3 Success Criteria

| Criteria                        | Target                |
| ------------------------------- | --------------------- |
| All migrations run successfully | 100%                  |
| Foreign key constraints valid   | 100%                  |
| Seeders execute without errors  | 100%                  |
| Indexes optimized               | All search fields     |
| Documentation complete          | ERD + Data Dictionary |

### 4.4 Requirements

| Requirement ID | Module Name | Description                         | User Story                                         | Expected System Behaviour/Outcome                                                                                                                                    | Role      |
| -------------- | ----------- | ----------------------------------- | -------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------- |
| DB-001         | Database    | Create database `my_zambezimeats`   | As a developer, I need a dedicated database        | MySQL database created with UTF8MB4 charset                                                                                                                          | Developer |
| DB-002         | Database    | Create `users` table                | As a developer, I need user storage                | Users table with: id, name, email, password, role, phone, avatar, is_active, currency_preference, timestamps                                                         | Developer |
| DB-003         | Database    | Create `categories` table           | As a developer, I need product categories          | Categories table with: id, name, slug, description, image, parent_id, sort_order, is_active, timestamps                                                              | Developer |
| DB-004         | Database    | Create `products` table             | As a developer, I need product storage             | Products table with: id, category_id, name, slug, description, price_per_kg, stock_quantity, unit, image, gallery, is_featured, is_active, timestamps                | Developer |
| DB-005         | Database    | Create `product_images` table       | As a developer, I need multiple product images     | Product images table with: id, product_id, image_path, sort_order, timestamps                                                                                        | Developer |
| DB-006         | Database    | Create `addresses` table            | As a developer, I need customer addresses          | Addresses table with: id, user_id, label, street, suburb, city, state, postcode, country, is_default, timestamps                                                     | Developer |
| DB-007         | Database    | Create `delivery_zones` table       | As a developer, I need delivery zone config        | Zones table with: id, name, postcodes (JSON), is_free_delivery, min_order_amount, delivery_fee, timestamps                                                           | Developer |
| DB-008         | Database    | Create `orders` table               | As a developer, I need order storage               | Orders table with: id, user_id, order_number, status, subtotal, delivery_fee, total, currency, exchange_rate, delivery_address, notes, assigned_staff_id, timestamps | Developer |
| DB-009         | Database    | Create `order_items` table          | As a developer, I need order line items            | Order items table with: id, order_id, product_id, product_name, price_per_kg, quantity_kg, line_total, timestamps                                                    | Developer |
| DB-010         | Database    | Create `order_status_history` table | As a developer, I need order status tracking       | Status history with: id, order_id, status, notes, changed_by, timestamps                                                                                             | Developer |
| DB-011         | Database    | Create `payments` table             | As a developer, I need payment records             | Payments table with: id, order_id, payment_method, transaction_id, amount, currency, status, timestamps                                                              | Developer |
| DB-012         | Database    | Create `inventory_logs` table       | As a developer, I need stock tracking              | Inventory logs with: id, product_id, type, quantity, reason, user_id, timestamps                                                                                     | Developer |
| DB-013         | Database    | Create `delivery_proofs` table      | As a developer, I need POD storage                 | Delivery proofs with: id, order_id, signature_path, photo_path, notes, captured_by, timestamps                                                                       | Developer |
| DB-014         | Database    | Create `wishlists` table            | As a developer, I need wishlist storage            | Wishlists with: id, user_id, product_id, timestamps                                                                                                                  | Developer |
| DB-015         | Database    | Create `notifications` table        | As a developer, I need notification storage        | Notifications with: id, user_id, type, title, message, data, read_at, timestamps                                                                                     | Developer |
| DB-016         | Database    | Create `activity_logs` table        | As a developer, I need audit logging               | Activity logs with: id, user_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent, timestamps                                            | Developer |
| DB-017         | Database    | Create `settings` table             | As a developer, I need system settings             | Settings with: id, key, value, type, group, timestamps                                                                                                               | Developer |
| DB-018         | Database    | Create `currency_rates` table       | As a developer, I need exchange rate storage       | Currency rates with: id, base_currency, target_currency, rate, fetched_at, timestamps                                                                                | Developer |
| DB-019         | Database    | Create `promotions` table           | As a developer, I need promotional campaigns       | Promotions with: id, name, code, type, value, min_order, start_date, end_date, is_active, timestamps                                                                 | Developer |
| DB-020         | Database    | Create `support_tickets` table      | As a developer, I need customer support storage    | Support tickets with: id, user_id, order_id, subject, message, status, priority, timestamps                                                                          | Developer |
| DB-021         | Database    | Create `ticket_replies` table       | As a developer, I need ticket conversation storage | Ticket replies with: id, ticket_id, user_id, message, timestamps                                                                                                     | Developer |
| DB-022         | Database    | Create database seeders             | As a developer, I need test data                   | Seeders for: admin user, sample categories, sample products, delivery zones, settings                                                                                | Developer |
| DB-023         | Database    | Create database documentation       | As a developer, I need schema documentation        | ERD diagram and data dictionary in `/docs/database/`                                                                                                                 | Developer |
| DB-024         | Database    | Optimize indexes                    | As a developer, I need fast queries                | Indexes on: email, slug, order_number, status, created_at, foreign keys                                                                                              | Developer |

### 4.5 Entity Relationship Overview

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                           DATABASE SCHEMA OVERVIEW                                   │
│                              my_zambezimeats                                         │
└─────────────────────────────────────────────────────────────────────────────────────┘

  ┌──────────┐        ┌──────────┐        ┌──────────┐        ┌──────────┐
  │  users   │───────<│ addresses│        │categories│───────<│ products │
  └────┬─────┘        └──────────┘        └──────────┘        └────┬─────┘
       │                                                           │
       │  ┌──────────┐        ┌──────────────┐                     │
       └─<│  orders  │───────<│ order_items  │>────────────────────┘
          └────┬─────┘        └──────────────┘
               │
               │  ┌───────────────────┐     ┌────────────────┐
               ├─<│order_status_history│    │ delivery_proofs│
               │  └───────────────────┘     └────────────────┘
               │                                    │
               └────────────────────────────────────┘

  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
  │ activity_logs│    │ notifications│    │   settings   │
  └──────────────┘    └──────────────┘    └──────────────┘
```

---

## 5. Authentication & Authorization Module

### 5.1 Module Overview

| Field             | Value          |
| ----------------- | -------------- |
| **Module Name**   | AUTH           |
| **Priority**      | P0 - Critical  |
| **Dependencies**  | DATABASE       |
| **Documentation** | `/docs/auth/`  |
| **Tests**         | `/tests/auth/` |

### 5.2 Module Objectives

1. Implement secure user registration and login
2. Configure Laravel Sanctum for SPA authentication
3. Implement role-based access control (Guest, Customer, Staff, Admin)
4. Create 5-minute session timeout with auto-logout
5. Implement CSRF protection and secure cookie handling

### 5.3 Success Criteria

| Criteria                | Target            |
| ----------------------- | ----------------- |
| Registration works      | ✓                 |
| Login/Logout works      | ✓                 |
| Session timeout (5 min) | ✓                 |
| Role-based guards       | 4 roles protected |
| CSRF protection         | Active            |
| Password reset works    | ✓                 |
| Test coverage           | 100%              |

### 5.4 Requirements

| Requirement ID | Module Name | Description                           | User Story                                                                | Expected System Behaviour/Outcome                                                       | Role      |
| -------------- | ----------- | ------------------------------------- | ------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- | --------- |
| AUTH-001       | Auth        | Create registration endpoint          | As a guest, I want to create an account so I can track my orders          | User can register with name, email, password; receives confirmation; redirected to shop | Guest     |
| AUTH-002       | Auth        | Create login endpoint                 | As a user, I want to log in so I can access my dashboard                  | User can log in with email/password; session created; redirected based on role          | All       |
| AUTH-003       | Auth        | Create logout endpoint                | As a user, I want to log out securely                                     | Session destroyed; cookies cleared; redirected to landing page                          | All       |
| AUTH-004       | Auth        | Implement session timeout (5 minutes) | As a business owner, I want inactive sessions to auto-expire for security | After 5 minutes of inactivity, user is logged out and sees "Session expired" message    | All       |
| AUTH-005       | Auth        | Create password reset flow            | As a user, I want to reset my password if I forget it                     | User requests reset; receives email with link; can set new password                     | Customer  |
| AUTH-006       | Auth        | Implement role-based middleware       | As a developer, I need route protection by role                           | Middleware checks user role; unauthorized access returns 403                            | Developer |
| AUTH-007       | Auth        | Create auth guards for Vue Router     | As a developer, I need frontend route protection                          | Navigation guards check auth status and role; redirect if unauthorized                  | Developer |
| AUTH-008       | Auth        | Implement CSRF protection             | As a developer, I need CSRF protection for forms                          | CSRF token included in all requests; 419 error handled gracefully                       | Developer |
| AUTH-009       | Auth        | Create current user endpoint          | As a frontend, I need to fetch logged-in user data                        | `/api/v1/user` returns current user with role and preferences                           | All       |
| AUTH-010       | Auth        | Create auth store (Pinia)             | As a developer, I need frontend auth state management                     | Auth store manages user, token, isAuthenticated, role                                   | Developer |
| AUTH-011       | Auth        | Implement "Remember Me" functionality | As a user, I want to stay logged in on trusted devices                    | Extended session duration when "Remember Me" checked                                    | Customer  |
| AUTH-012       | Auth        | Create session activity tracker       | As a developer, I need to track user activity for timeout                 | Activity tracked on API calls and user interactions                                     | Developer |
| AUTH-013       | Auth        | Handle 419 CSRF errors gracefully     | As a user, I want clear feedback when session expires                     | 419 errors show "Session expired" modal with re-login option                            | All       |
| AUTH-014       | Auth        | Handle 401 unauthorized errors        | As a user, I want proper feedback for auth issues                         | 401 errors redirect to login with return URL preserved                                  | All       |
| AUTH-015       | Auth        | Create admin-only seeder              | As a developer, I need initial admin account                              | Admin user seeded: admin@zambezimeats.com                                               | Developer |
| AUTH-016       | Auth        | Create login page UI                  | As a user, I want a clean login form                                      | Modern login form with email, password, remember me, forgot password link               | All       |
| AUTH-017       | Auth        | Create registration page UI           | As a guest, I want a simple registration form                             | Registration form with name, email, phone, password, confirm password                   | Guest     |
| AUTH-018       | Auth        | Create forgot password page UI        | As a user, I want to request password reset                               | Email input form with clear instructions                                                | All       |
| AUTH-019       | Auth        | Create reset password page UI         | As a user, I want to set a new password                                   | New password form with confirmation                                                     | All       |
| AUTH-020       | Auth        | Write comprehensive auth tests        | As a developer, I need 100% test coverage                                 | Unit and feature tests for all auth endpoints and flows                                 | Developer |

### 5.5 Session Timeout Implementation

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                           SESSION TIMEOUT FLOW (5 Minutes)                          │
└─────────────────────────────────────────────────────────────────────────────────────┘

  ┌─────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
  │  User   │────▶│  Activity   │────▶│   Timer     │────▶│   Warning   │
  │  Login  │     │  Detected   │     │   Reset     │     │   (4:30)    │
  └─────────┘     └─────────────┘     └─────────────┘     └──────┬──────┘
                                                                 │
                           ┌─────────────────────────────────────┘
                           │
                           ▼
                  ┌─────────────────┐        ┌─────────────────┐
                  │    No Activity  │───────▶│   Auto Logout   │
                  │    (5:00)       │        │  + Lock Screen  │
                  └─────────────────┘        └─────────────────┘
```

### 5.6 API Endpoints

| Method | Endpoint                       | Description            | Auth |
| ------ | ------------------------------ | ---------------------- | ---- |
| POST   | `/api/v1/auth/register`        | Register new customer  | No   |
| POST   | `/api/v1/auth/login`           | User login             | No   |
| POST   | `/api/v1/auth/logout`          | User logout            | Yes  |
| POST   | `/api/v1/auth/forgot-password` | Request password reset | No   |
| POST   | `/api/v1/auth/reset-password`  | Reset password         | No   |
| GET    | `/api/v1/auth/user`            | Get current user       | Yes  |
| POST   | `/api/v1/auth/refresh`         | Refresh session        | Yes  |

---

## 6. Landing Page Module

### 6.1 Module Overview

| Field             | Value             |
| ----------------- | ----------------- |
| **Module Name**   | LANDING           |
| **Priority**      | P1 - High         |
| **Dependencies**  | AUTH              |
| **Documentation** | `/docs/landing/`  |
| **Tests**         | `/tests/landing/` |

### 6.2 Module Objectives

1. Create modern, animated landing page as entry point
2. Showcase brand identity and premium quality
3. Provide clear navigation to shop and login
4. Display featured products and promotions
5. Build trust with social proof and quality indicators

### 6.3 Success Criteria

| Criteria          | Target      |
| ----------------- | ----------- |
| Page load time    | < 2 seconds |
| Mobile responsive | 100%        |
| Animations smooth | 60fps       |
| CTA click-through | Measurable  |
| Lighthouse score  | > 90        |
| Test coverage     | 100%        |

### 6.4 Requirements

| Requirement ID | Module Name | Description                                  | User Story                                                                          | Expected System Behaviour/Outcome                                                                    | Role      |
| -------------- | ----------- | -------------------------------------------- | ----------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- | --------- |
| LAND-001       | Landing     | Create hero section with animated background | As a visitor, I want to see an impressive hero section that conveys premium quality | Full-width hero with animated meat imagery, compelling headline, and CTA button                      | Guest     |
| LAND-002       | Landing     | Create navigation header                     | As a visitor, I want easy navigation                                                | Sticky header with logo, nav links (Shop, About, Contact), Login/Register buttons, currency selector | Guest     |
| LAND-003       | Landing     | Implement smooth scroll animations           | As a visitor, I want engaging scroll experience                                     | Elements animate in on scroll using GSAP/Intersection Observer                                       | Guest     |
| LAND-004       | Landing     | Create featured products section             | As a visitor, I want to see popular products                                        | Grid of 4-6 featured products with images, names, prices, "View" button                              | Guest     |
| LAND-005       | Landing     | Create "Why Choose Us" section               | As a visitor, I want to know the benefits                                           | 3-4 benefit cards with icons: Premium Quality, Fresh Daily, Fast Delivery, Best Prices               | Guest     |
| LAND-006       | Landing     | Create about section                         | As a visitor, I want to know about the company                                      | Brief company story with image, link to full about page                                              | Guest     |
| LAND-007       | Landing     | Create testimonials/reviews section          | As a visitor, I want social proof                                                   | Carousel of customer reviews with ratings                                                            | Guest     |
| LAND-008       | Landing     | Create delivery zones section                | As a visitor, I want to know if you deliver to my area                              | Map or list of delivery areas with "Check Your Area" feature                                         | Guest     |
| LAND-009       | Landing     | Create contact section                       | As a visitor, I want to contact the store                                           | Contact form, address, phone, email, operating hours                                                 | Guest     |
| LAND-010       | Landing     | Create footer                                | As a visitor, I want site-wide links                                                | Footer with links, social media, newsletter signup, developer credits                                | Guest     |
| LAND-011       | Landing     | Implement currency selector                  | As a visitor, I want to see prices in my currency                                   | Dropdown to switch between AU$ and US$                                                               | Guest     |
| LAND-012       | Landing     | Create login modal/section                   | As a visitor, I want quick access to login                                          | Login form accessible from header, can also be modal                                                 | Guest     |
| LAND-013       | Landing     | Implement mobile hamburger menu              | As a mobile user, I want accessible navigation                                      | Responsive hamburger menu with slide-out navigation                                                  | Guest     |
| LAND-014       | Landing     | Add micro-interactions                       | As a visitor, I want delightful interactions                                        | Button hovers, card elevations, smooth transitions                                                   | Guest     |
| LAND-015       | Landing     | Implement lazy loading for images            | As a visitor, I want fast page loads                                                | Images lazy-loaded with blur placeholder                                                             | Guest     |
| LAND-016       | Landing     | Create "Shop Now" CTA                        | As a visitor, I want to quickly access the shop                                     | Prominent CTA button that navigates to shop page                                                     | Guest     |
| LAND-017       | Landing     | Add SEO meta tags                            | As a business, I want search engine visibility                                      | Title, description, Open Graph, Twitter cards configured                                             | Admin     |
| LAND-018       | Landing     | Implement newsletter signup                  | As a visitor, I want to subscribe to updates                                        | Email input with validation, success feedback                                                        | Guest     |
| LAND-019       | Landing     | Add developer credits                        | As the developer, I want attribution                                                | Footer text: "Developed with ❤️ by bguvava"                                                          | Developer |
| LAND-020       | Landing     | Write landing page tests                     | As a developer, I need 100% test coverage                                           | Component tests, E2E tests for navigation and interactions                                           | Developer |

### 6.5 Landing Page Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER (Sticky)                                                                    │
│  ┌──────┐                                           ┌────┐ ┌────────┐ ┌──────────┐  │
│  │ LOGO │  Shop  About  Contact             AU$/US$ │Cart│ │ Login  │ │ Register │  │
│  └──────┘                                           └────┘ └────────┘ └──────────┘  │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│                              HERO SECTION                                           │
│                     ┌─────────────────────────────────┐                             │
│                     │      Premium Quality Meats      │                             │
│                     │   Delivered Fresh to Your Door  │                             │
│                     │                                 │                             │
│                     │     ┌──────────────────┐        │                             │
│                     │     │    SHOP NOW      │        │                             │
│                     │     └──────────────────┘        │                             │
│                     └─────────────────────────────────┘                             │
│                        (Animated background)                                        │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           FEATURED PRODUCTS                                         │
│   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐                            │
│   │ Product │   │ Product │   │ Product │   │ Product │                            │
│   │  Image  │   │  Image  │   │  Image  │   │  Image  │                            │
│   │ $XX/kg  │   │ $XX/kg  │   │ $XX/kg  │   │ $XX/kg  │                            │
│   └─────────┘   └─────────┘   └─────────┘   └─────────┘                            │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           WHY CHOOSE US                                             │
│   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐   ┌───────────────┐    │
│   │   🥩 Premium  │   │   🌿 Fresh    │   │   🚚 Fast     │   │   💰 Best     │    │
│   │    Quality    │   │    Daily      │   │   Delivery    │   │    Prices     │    │
│   └───────────────┘   └───────────────┘   └───────────────┘   └───────────────┘    │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                           TESTIMONIALS                                              │
│              ◀  "Best quality meat I've ever ordered!"  ▶                          │
│                         ⭐⭐⭐⭐⭐ - John D.                                          │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                              CONTACT                                                │
│   ┌─────────────────────────────────┐    📍 6/1053 Old Princes Highway            │
│   │     Contact Form                │       Engadine, NSW 2233                     │
│   │     Name: ________________      │    📞 XXXX XXX XXX                           │
│   │     Email: _______________      │    ✉️  info@zambezimeats.com                  │
│   │     Message: _____________      │    🕐 Mon-Sun: 7am - 6pm                     │
│   │     [SEND]                      │                                              │
│   └─────────────────────────────────┘                                              │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│  FOOTER                                                                             │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐                    │
│  │ Quick Links│  │  Products  │  │   Legal    │  │  Connect   │                    │
│  │ Shop       │  │ Beef       │  │ Privacy    │  │ Facebook   │                    │
│  │ About      │  │ Chicken    │  │ Terms      │  │ Instagram  │                    │
│  │ Contact    │  │ Lamb       │  │ Returns    │  │ Newsletter │                    │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘                    │
│                                                                                     │
│  ───────────────────────────────────────────────────────────────────────────────    │
│                    Developed with ❤️ by bguvava                                     │
│                © 2025 Zambezi Meats. All rights reserved.                          │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 1 Summary

### Modules Covered

| #   | Module                         | Requirements                   | Priority |
| --- | ------------------------------ | ------------------------------ | -------- |
| 1   | Development Environment        | DEV-ENV-001 to DEV-ENV-010     | P0       |
| 2   | Project Initialization         | PROJ-INIT-001 to PROJ-INIT-015 | P0       |
| 3   | Database Design & Setup        | DB-001 to DB-024               | P0       |
| 4   | Authentication & Authorization | AUTH-001 to AUTH-020           | P0       |
| 5   | Landing Page                   | LAND-001 to LAND-020           | P1       |

### Total Requirements in Part 1: 89

---

## 7. Shop & Product Catalog Module

### 7.1 Module Overview

| Field             | Value                   |
| ----------------- | ----------------------- |
| **Module Name**   | SHOP                    |
| **Priority**      | P0 - Critical           |
| **Dependencies**  | AUTH, DATABASE, LANDING |
| **Documentation** | `/docs/shop/`           |
| **Tests**         | `/tests/shop/`          |

### 7.2 Module Objectives

1. Create the primary shop interface as the main destination from landing page
2. Display products with categories, filtering, and search
3. Implement multi-currency display (AU$/US$)
4. Show real-time stock availability
5. Provide quick-view and full product detail modals
6. Optimize for performance with lazy loading and pagination

### 7.3 Success Criteria

| Criteria             | Target               |
| -------------------- | -------------------- |
| Page load time       | < 1.5 seconds        |
| Products per page    | 12-24 (configurable) |
| Search response time | < 500ms              |
| Filter response time | < 300ms              |
| Mobile responsive    | 100%                 |
| Test coverage        | 100%                 |

### 7.4 Search Filter Optimization

> **Note:** Limited filters to reduce database queries and enhance data retrieval speeds.

| Filter       | Type                | Indexed |
| ------------ | ------------------- | ------- |
| Category     | Single select       | ✅      |
| Price Range  | Min/Max slider      | ✅      |
| Availability | In Stock only       | ✅      |
| Sort By      | Price, Name, Newest | ✅      |

### 7.5 Requirements

| Requirement ID | Module Name | Description                           | User Story                                                     | Expected System Behaviour/Outcome                                       | Role           |
| -------------- | ----------- | ------------------------------------- | -------------------------------------------------------------- | ----------------------------------------------------------------------- | -------------- |
| SHOP-001       | Shop        | Create shop page layout               | As a customer, I want to browse all products easily            | Grid/list view of products with sidebar categories, header with search  | Guest/Customer |
| SHOP-002       | Shop        | Implement category sidebar/navigation | As a customer, I want to filter by meat type                   | Clickable category list with product counts, collapsible on mobile      | Guest/Customer |
| SHOP-003       | Shop        | Create product card component         | As a customer, I want to see product info at a glance          | Card showing: image, name, price/kg, stock status, quick add button     | Guest/Customer |
| SHOP-004       | Shop        | Implement product search              | As a customer, I want to find specific products quickly        | Search bar with instant results dropdown, searches name and description | Guest/Customer |
| SHOP-005       | Shop        | Implement price range filter          | As a customer, I want to filter by budget                      | Dual-handle slider for min/max price, updates results in real-time      | Guest/Customer |
| SHOP-006       | Shop        | Implement sort functionality          | As a customer, I want to sort products                         | Dropdown: Price Low-High, Price High-Low, Name A-Z, Newest First        | Guest/Customer |
| SHOP-007       | Shop        | Implement "In Stock Only" filter      | As a customer, I want to see only available products           | Toggle to hide out-of-stock items                                       | Guest/Customer |
| SHOP-008       | Shop        | Create product quick-view modal       | As a customer, I want to see more details without leaving shop | Modal with larger image, full description, add to cart with quantity    | Guest/Customer |
| SHOP-009       | Shop        | Create full product detail page       | As a customer, I want comprehensive product information        | Full page with gallery, description, nutrition info, related products   | Guest/Customer |
| SHOP-010       | Shop        | Implement product image gallery       | As a customer, I want to see multiple product images           | Thumbnail gallery with zoom on hover, lightbox on click                 | Guest/Customer |
| SHOP-011       | Shop        | Display currency toggle               | As a customer, I want to view prices in my currency            | Header dropdown to switch AU$/US$, all prices update instantly          | Guest/Customer |
| SHOP-012       | Shop        | Show real-time stock levels           | As a customer, I want to know product availability             | Stock indicator: "In Stock", "Low Stock (X left)", "Out of Stock"       | Guest/Customer |
| SHOP-013       | Shop        | Implement pagination                  | As a customer, I want to browse many products efficiently      | Page numbers or infinite scroll with "Load More" button                 | Guest/Customer |
| SHOP-014       | Shop        | Create "Add to Cart" functionality    | As a customer, I want to add products to my cart               | Weight input (kg) + Add button, updates cart count in header            | Guest/Customer |
| SHOP-015       | Shop        | Implement wishlist toggle             | As a customer, I want to save products for later               | Heart icon to add/remove from wishlist (requires login)                 | Customer       |
| SHOP-016       | Shop        | Show featured products section        | As a customer, I want to see popular/recommended items         | Highlighted section at top for featured products                        | Guest/Customer |
| SHOP-017       | Shop        | Display promotional banners           | As a customer, I want to see current deals                     | Banner area for promotions, linked to filtered results                  | Guest/Customer |
| SHOP-018       | Shop        | Implement breadcrumb navigation       | As a customer, I want to know where I am                       | Breadcrumbs: Home > Shop > Category > Product                           | Guest/Customer |
| SHOP-019       | Shop        | Create empty state for no results     | As a customer, I want feedback when search finds nothing       | Friendly message with suggestions or "Clear Filters" option             | Guest/Customer |
| SHOP-020       | Shop        | Implement skeleton loading            | As a customer, I want visual feedback while loading            | Skeleton placeholders for products during API calls                     | Guest/Customer |
| SHOP-021       | Shop        | Create products API endpoint          | As a developer, I need a products listing API                  | `GET /api/v1/products` with filtering, sorting, pagination              | Developer      |
| SHOP-022       | Shop        | Create single product API endpoint    | As a developer, I need product detail API                      | `GET /api/v1/products/{slug}` returns full product data                 | Developer      |
| SHOP-023       | Shop        | Create categories API endpoint        | As a developer, I need categories listing API                  | `GET /api/v1/categories` returns all active categories                  | Developer      |
| SHOP-024       | Shop        | Create featured products API endpoint | As a developer, I need featured products API                   | `GET /api/v1/products/featured` returns featured items                  | Developer      |
| SHOP-025       | Shop        | Create products Pinia store           | As a developer, I need state management for products           | Store with products, categories, filters, loading states                | Developer      |
| SHOP-026       | Shop        | Implement URL query sync              | As a customer, I want to share filtered results                | Filters sync to URL params, shareable links work                        | Guest/Customer |
| SHOP-027       | Shop        | Optimize images for web               | As a developer, I need fast image loading                      | Images served in WebP format, multiple sizes, lazy loaded               | Developer      |
| SHOP-028       | Shop        | Write shop module tests               | As a developer, I need 100% test coverage                      | Unit tests for components, integration tests for API, E2E tests         | Developer      |

### 7.6 Shop API Endpoints

| Method | Endpoint                    | Description            | Auth | Filters                                              |
| ------ | --------------------------- | ---------------------- | ---- | ---------------------------------------------------- |
| GET    | `/api/v1/products`          | List products          | No   | category, min_price, max_price, in_stock, sort, page |
| GET    | `/api/v1/products/featured` | Featured products      | No   | limit                                                |
| GET    | `/api/v1/products/{slug}`   | Product detail         | No   | -                                                    |
| GET    | `/api/v1/categories`        | List categories        | No   | -                                                    |
| GET    | `/api/v1/categories/{slug}` | Category with products | No   | -                                                    |

### 7.7 Shop Page Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER                                                                             │
│  Logo   Shop  About  Contact              🔍 Search...     AU$/US$  🛒(3)  Login   │
├─────────────────────────────────────────────────────────────────────────────────────┤
│  Home > Shop > Beef                                                                 │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  CATEGORIES    │  ┌─────────────────────────────────────────────────────────────┐   │
│                │  │ 🔍 Search products...                    Sort: [Price ▼]   │   │
│  ☑ All (45)    │  └─────────────────────────────────────────────────────────────┘   │
│  ☐ Beef (12)   │                                                                    │
│  ☐ Chicken (8) │  ┌─────────────────────────────────────────────────────────────┐   │
│  ☐ Lamb (10)   │  │ FEATURED: Premium Wagyu Beef - 20% OFF This Week!           │   │
│  ☐ Pork (7)    │  └─────────────────────────────────────────────────────────────┘   │
│  ☐ Seafood (5) │                                                                    │
│  ☐ Sausages(3) │   ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐  │
│                │   │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │
│  ────────────  │   │            │  │            │  │            │  │            │  │
│  PRICE RANGE   │   │ Ribeye     │  │ T-Bone     │  │ Scotch     │  │ Rump       │  │
│  $0 ━━━━━ $100 │   │ $45.99/kg  │  │ $38.99/kg  │  │ $52.99/kg  │  │ $28.99/kg  │  │
│                │   │ ● In Stock │  │ ● Low (3)  │  │ ● In Stock │  │ ○ Out      │  │
│  ────────────  │   │ [Add ♡]    │  │ [Add ♡]    │  │ [Add ♡]    │  │ [♡]        │  │
│  ☑ In Stock    │   └────────────┘  └────────────┘  └────────────┘  └────────────┘  │
│                │                                                                    │
│                │   ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐  │
│                │   │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │
│                │   │ ...        │  │ ...        │  │ ...        │  │ ...        │  │
│                │   └────────────┘  └────────────┘  └────────────┘  └────────────┘  │
│                │                                                                    │
│                │              ◀  1  2  3  4  5  ...  12  ▶                         │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

---

## 8. Cart Module

### 8.1 Module Overview

| Field             | Value          |
| ----------------- | -------------- |
| **Module Name**   | CART           |
| **Priority**      | P0 - Critical  |
| **Dependencies**  | SHOP           |
| **Documentation** | `/docs/cart/`  |
| **Tests**         | `/tests/cart/` |

### 8.2 Module Objectives

1. Create persistent shopping cart (localStorage + database for logged-in users)
2. Display cart as slide-out panel for seamless shopping
3. Calculate totals with real-time currency conversion
4. Validate stock availability before checkout
5. Support guest checkout with cart preservation

### 8.3 Success Criteria

| Criteria             | Target                                  |
| -------------------- | --------------------------------------- |
| Cart persistence     | 7 days for guests, indefinite for users |
| Update response time | < 200ms                                 |
| Stock validation     | Real-time on checkout                   |
| Currency conversion  | Accurate to 2 decimals                  |
| Test coverage        | 100%                                    |

### 8.4 Requirements

| Requirement ID | Module Name | Description                               | User Story                                                     | Expected System Behaviour/Outcome                                                | Role           |
| -------------- | ----------- | ----------------------------------------- | -------------------------------------------------------------- | -------------------------------------------------------------------------------- | -------------- |
| CART-001       | Cart        | Create cart slide-out panel               | As a customer, I want to view my cart without leaving the page | Slide-out panel from right with cart items, totals, checkout button              | Guest/Customer |
| CART-002       | Cart        | Display cart items list                   | As a customer, I want to see what's in my cart                 | List showing: product image, name, price/kg, quantity, line total, remove button | Guest/Customer |
| CART-003       | Cart        | Implement quantity adjustment             | As a customer, I want to change quantities                     | +/- buttons or input field to adjust kg, minimum 0.5kg increments                | Guest/Customer |
| CART-004       | Cart        | Implement item removal                    | As a customer, I want to remove items                          | X button removes item with confirmation, cart updates                            | Guest/Customer |
| CART-005       | Cart        | Calculate and display subtotal            | As a customer, I want to see my total                          | Running subtotal calculated from all line items                                  | Guest/Customer |
| CART-006       | Cart        | Display delivery fee estimate             | As a customer, I want to know delivery costs                   | Show "Free delivery on orders $100+" or estimated fee                            | Guest/Customer |
| CART-007       | Cart        | Display order total                       | As a customer, I want to see final total                       | Subtotal + Delivery Fee = Total, in selected currency                            | Guest/Customer |
| CART-008       | Cart        | Show minimum order warning                | As a customer, I want to know if I meet minimum                | Warning if cart < $100: "Add $X more for delivery"                               | Guest/Customer |
| CART-009       | Cart        | Display currency conversion               | As a customer, I want to see prices in my currency             | All prices converted if US$ selected, show rate used                             | Guest/Customer |
| CART-010       | Cart        | Implement cart persistence (localStorage) | As a guest, I want my cart saved                               | Cart saved to localStorage, persists across sessions                             | Guest          |
| CART-011       | Cart        | Sync cart to database for logged-in users | As a customer, I want my cart synced to my account             | Cart synced to database on login, merge with localStorage                        | Customer       |
| CART-012       | Cart        | Validate stock on add to cart             | As a customer, I want to know if item is available             | Check stock before adding, show error if insufficient                            | Guest/Customer |
| CART-013       | Cart        | Validate stock on checkout                | As a customer, I want to know if items still available         | Re-validate all items before checkout, remove unavailable                        | Guest/Customer |
| CART-014       | Cart        | Update cart icon badge                    | As a customer, I want to see cart item count                   | Header cart icon shows number of items                                           | Guest/Customer |
| CART-015       | Cart        | Create "Continue Shopping" button         | As a customer, I want to add more items                        | Button closes cart panel, returns to shop                                        | Guest/Customer |
| CART-016       | Cart        | Create "Proceed to Checkout" button       | As a customer, I want to complete my order                     | Button validates cart and navigates to checkout                                  | Guest/Customer |
| CART-017       | Cart        | Handle empty cart state                   | As a customer, I want feedback when cart is empty              | Message: "Your cart is empty" with "Start Shopping" link                         | Guest/Customer |
| CART-018       | Cart        | Implement cart animation                  | As a customer, I want visual feedback                          | Items animate when added/removed, totals animate on change                       | Guest/Customer |
| CART-019       | Cart        | Create cart API endpoints                 | As a developer, I need cart CRUD APIs                          | `POST/GET/PUT/DELETE /api/v1/cart` endpoints                                     | Developer      |
| CART-020       | Cart        | Create cart Pinia store                   | As a developer, I need cart state management                   | Store with items, totals, add/remove/update actions                              | Developer      |
| CART-021       | Cart        | Handle price changes                      | As a customer, I want current prices                           | If product price changed since added, show notification                          | Guest/Customer |
| CART-022       | Cart        | Implement "Save for Later"                | As a customer, I want to move items to wishlist                | Option to move item from cart to wishlist                                        | Customer       |
| CART-023       | Cart        | Write cart module tests                   | As a developer, I need 100% test coverage                      | Unit tests for calculations, integration tests for sync                          | Developer      |

### 8.5 Cart API Endpoints

| Method | Endpoint                  | Description                     | Auth |
| ------ | ------------------------- | ------------------------------- | ---- |
| GET    | `/api/v1/cart`            | Get user's cart                 | Yes  |
| POST   | `/api/v1/cart/items`      | Add item to cart                | Yes  |
| PUT    | `/api/v1/cart/items/{id}` | Update cart item quantity       | Yes  |
| DELETE | `/api/v1/cart/items/{id}` | Remove item from cart           | Yes  |
| DELETE | `/api/v1/cart`            | Clear entire cart               | Yes  |
| POST   | `/api/v1/cart/validate`   | Validate cart for checkout      | Yes  |
| POST   | `/api/v1/cart/sync`       | Sync localStorage cart on login | Yes  |

### 8.6 Cart Panel Wireframe

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                          [X] Close  │
│  🛒 YOUR CART (3 items)                                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Ribeye Steak                                    [🗑️]   │ │
│  │        $45.99/kg                                               │ │
│  │        Qty: [-] 1.5 kg [+]                    = $68.99         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Chicken Breast                                  [🗑️]   │ │
│  │        $18.99/kg                                               │ │
│  │        Qty: [-] 2.0 kg [+]                    = $37.98         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Lamb Chops                                      [🗑️]   │ │
│  │        $32.99/kg  ⚠️ Price updated                             │ │
│  │        Qty: [-] 1.0 kg [+]                    = $32.99         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Subtotal                                            AU$ 139.96     │
│  Delivery                                            FREE ✓         │
│  ─────────────────────────────────────────────────────────────────  │
│  TOTAL                                               AU$ 139.96     │
│                                                                     │
│  💱 View in US$: ~$91.17 (Rate: 0.6514)                            │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              PROCEED TO CHECKOUT                            │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ← Continue Shopping                                                │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 9. Checkout Module

### 9.1 Module Overview

| Field             | Value              |
| ----------------- | ------------------ |
| **Module Name**   | CHECKOUT           |
| **Priority**      | P0 - Critical      |
| **Dependencies**  | CART, AUTH         |
| **Documentation** | `/docs/checkout/`  |
| **Tests**         | `/tests/checkout/` |

### 9.2 Module Objectives

1. Create streamlined multi-step checkout process
2. Implement secure payment processing (Stripe, PayPal, Afterpay)
3. Validate delivery address and calculate fees
4. Support guest checkout with account creation option
5. Handle payment errors gracefully (prevent 500 errors)

### 9.3 Success Criteria

| Criteria                 | Target              |
| ------------------------ | ------------------- |
| Checkout completion rate | > 75%               |
| Payment success rate     | > 98%               |
| Error handling           | All errors graceful |
| Mobile responsive        | 100%                |
| Test coverage            | 100%                |

### 9.4 Requirements

| Requirement ID | Module Name | Description                                 | User Story                                            | Expected System Behaviour/Outcome                                                 | Role           |
| -------------- | ----------- | ------------------------------------------- | ----------------------------------------------------- | --------------------------------------------------------------------------------- | -------------- |
| CHK-001        | Checkout    | Create checkout page layout                 | As a customer, I want a clear checkout process        | Multi-step form: Delivery → Payment → Review → Confirm                            | Guest/Customer |
| CHK-002        | Checkout    | Implement step indicator                    | As a customer, I want to know my progress             | Progress bar showing: 1. Delivery 2. Payment 3. Review 4. Done                    | Guest/Customer |
| CHK-003        | Checkout    | Create delivery address form                | As a customer, I want to enter my delivery address    | Form: street, suburb, city, state, postcode with validation                       | Guest/Customer |
| CHK-004        | Checkout    | Implement address autocomplete              | As a customer, I want easy address entry              | Google Places autocomplete for Australian addresses                               | Guest/Customer |
| CHK-005        | Checkout    | Display saved addresses for logged-in users | As a customer, I want to use saved addresses          | Dropdown/cards of saved addresses with "Add New" option                           | Customer       |
| CHK-006        | Checkout    | Validate delivery zone                      | As a customer, I want to know if you deliver to me    | Check postcode against delivery zones, show fee or "Sorry, we don't deliver here" | Guest/Customer |
| CHK-007        | Checkout    | Calculate and display delivery fee          | As a customer, I want to see delivery cost            | Free if $100+ and in zone, otherwise calculate by distance                        | Guest/Customer |
| CHK-008        | Checkout    | Create payment method selection             | As a customer, I want to choose payment method        | Options: Credit Card, PayPal, Afterpay, Cash on Delivery                          | Guest/Customer |
| CHK-009        | Checkout    | Integrate Stripe payment                    | As a customer, I want to pay by card                  | Stripe Elements for secure card input, supports AU$/US$                           | Guest/Customer |
| CHK-010        | Checkout    | Integrate PayPal payment                    | As a customer, I want to pay via PayPal               | PayPal button, redirect to PayPal, return to confirm                              | Guest/Customer |
| CHK-011        | Checkout    | Integrate Afterpay payment                  | As a customer, I want buy-now-pay-later option        | Afterpay widget (AU$ only), show installment amounts                              | Customer       |
| CHK-012        | Checkout    | Implement Cash on Delivery                  | As a customer, I want to pay on delivery              | COD option (AU$ only), no upfront payment required                                | Customer       |
| CHK-013        | Checkout    | Create order review step                    | As a customer, I want to review before paying         | Summary: items, delivery address, payment method, totals                          | Guest/Customer |
| CHK-014        | Checkout    | Implement order notes field                 | As a customer, I want to add special instructions     | Optional textarea for delivery/order notes                                        | Guest/Customer |
| CHK-015        | Checkout    | Create promo code input                     | As a customer, I want to apply discounts              | Input field with "Apply" button, validates code, shows discount                   | Guest/Customer |
| CHK-016        | Checkout    | Handle payment success                      | As a customer, I want confirmation of payment         | Success page with order number, email confirmation sent                           | Guest/Customer |
| CHK-017        | Checkout    | Handle payment failure                      | As a customer, I want clear feedback if payment fails | Error message with retry option, no order created                                 | Guest/Customer |
| CHK-018        | Checkout    | Handle 419 CSRF errors                      | As a customer, I want session issues handled          | If 419 occurs, refresh CSRF token and prompt retry                                | Guest/Customer |
| CHK-019        | Checkout    | Handle 500 server errors                    | As a customer, I want graceful error handling         | Friendly error message, log error, suggest contact support                        | Guest/Customer |
| CHK-020        | Checkout    | Create guest checkout flow                  | As a guest, I want to checkout without account        | Allow checkout without login, offer account creation post-checkout                | Guest          |
| CHK-021        | Checkout    | Offer account creation post-checkout        | As a guest, I want to create account after ordering   | "Create account to track your order" with pre-filled email                        | Guest          |
| CHK-022        | Checkout    | Reserve stock during checkout               | As a customer, I want my items held while I pay       | Stock temporarily reserved for 15 minutes during checkout                         | Guest/Customer |
| CHK-023        | Checkout    | Send order confirmation email               | As a customer, I want email confirmation              | Email with order details, items, totals, delivery info                            | Guest/Customer |
| CHK-024        | Checkout    | Send order alert to admin                   | As a business owner, I want new order notifications   | Email + dashboard alert when new order placed                                     | Admin          |
| CHK-025        | Checkout    | Create order in database                    | As a developer, I need order record created           | Order created with items, status "pending", payment record                        | Developer      |
| CHK-026        | Checkout    | Generate unique order number                | As a developer, I need order identification           | Format: ZM-YYYYMMDD-XXXX (e.g., ZM-20251212-0001)                                 | Developer      |
| CHK-027        | Checkout    | Create checkout API endpoints               | As a developer, I need checkout APIs                  | Endpoints for address validation, fee calculation, order creation                 | Developer      |
| CHK-028        | Checkout    | Create payment webhook handlers             | As a developer, I need payment confirmation           | Webhooks for Stripe, PayPal to confirm payment status                             | Developer      |
| CHK-029        | Checkout    | Implement checkout Pinia store              | As a developer, I need checkout state                 | Store for checkout steps, address, payment, order state                           | Developer      |
| CHK-030        | Checkout    | Write checkout module tests                 | As a developer, I need 100% test coverage             | Unit, integration, E2E tests including payment mocks                              | Developer      |

### 9.5 Checkout API Endpoints

| Method | Endpoint                              | Description                  | Auth     |
| ------ | ------------------------------------- | ---------------------------- | -------- |
| POST   | `/api/v1/checkout/validate-address`   | Validate delivery address    | No       |
| POST   | `/api/v1/checkout/calculate-delivery` | Calculate delivery fee       | No       |
| POST   | `/api/v1/checkout/apply-promo`        | Apply promo code             | No       |
| POST   | `/api/v1/checkout/create-order`       | Create order                 | Optional |
| POST   | `/api/v1/checkout/stripe/intent`      | Create Stripe payment intent | Optional |
| POST   | `/api/v1/checkout/paypal/create`      | Create PayPal order          | Optional |
| POST   | `/api/v1/checkout/paypal/capture`     | Capture PayPal payment       | Optional |
| POST   | `/api/v1/webhooks/stripe`             | Stripe webhook handler       | No       |
| POST   | `/api/v1/webhooks/paypal`             | PayPal webhook handler       | No       |

### 9.6 Checkout Flow Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  CHECKOUT                                                                           │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│     ●━━━━━━━━━━━━━━━●━━━━━━━━━━━━━━━○━━━━━━━━━━━━━━━○                              │
│     1. Delivery      2. Payment       3. Review       4. Done                       │
│                                                                                     │
├───────────────────────────────────────┬─────────────────────────────────────────────┤
│                                       │                                             │
│  DELIVERY ADDRESS                     │  ORDER SUMMARY                              │
│                                       │                                             │
│  ┌─ Saved Addresses ───────────────┐  │  ┌─────────────────────────────────────┐   │
│  │ ○ Home - 123 Main St, Sydney    │  │  │ Ribeye Steak x 1.5kg      $68.99   │   │
│  │ ○ Work - 456 Office Rd, CBD     │  │  │ Chicken Breast x 2.0kg    $37.98   │   │
│  │ ● + Add New Address             │  │  │ Lamb Chops x 1.0kg        $32.99   │   │
│  └─────────────────────────────────┘  │  └─────────────────────────────────────┘   │
│                                       │                                             │
│  Street Address *                     │  Subtotal                      $139.96     │
│  ┌─────────────────────────────────┐  │  Delivery                      FREE        │
│  │ Start typing for suggestions... │  │  ─────────────────────────────────────     │
│  └─────────────────────────────────┘  │  TOTAL                    AU$ 139.96       │
│                                       │                                             │
│  Suburb *              State *        │  ┌─────────────────────────────────────┐   │
│  ┌──────────────┐  ┌──────────────┐   │  │ Promo Code   [________] [APPLY]    │   │
│  │              │  │ NSW ▼        │   │  └─────────────────────────────────────┘   │
│  └──────────────┘  └──────────────┘   │                                             │
│                                       │                                             │
│  Postcode *            Phone *        │                                             │
│  ┌──────────────┐  ┌──────────────┐   │                                             │
│  │ 2233         │  │ 04XX XXX XXX │   │                                             │
│  └──────────────┘  └──────────────┘   │                                             │
│                                       │                                             │
│  ✅ Delivery available! FREE delivery │                                             │
│                                       │                                             │
│  Order Notes (optional)               │                                             │
│  ┌─────────────────────────────────┐  │                                             │
│  │ Leave at door, ring bell...     │  │                                             │
│  └─────────────────────────────────┘  │                                             │
│                                       │                                             │
│                   [CONTINUE TO PAYMENT →]                                           │
│                                       │                                             │
└───────────────────────────────────────┴─────────────────────────────────────────────┘
```

---

## 10. Customer Dashboard Module

### 10.1 Module Overview

| Field             | Value              |
| ----------------- | ------------------ |
| **Module Name**   | CUSTOMER           |
| **Priority**      | P1 - High          |
| **Dependencies**  | AUTH, CHECKOUT     |
| **Documentation** | `/docs/customer/`  |
| **Tests**         | `/tests/customer/` |

### 10.2 Module Objectives

1. Create personalized customer dashboard
2. Display order history with status tracking
3. Manage delivery addresses and preferences
4. Implement wishlist functionality
5. Provide reorder capability for past purchases
6. Handle support ticket submission

### 10.3 Success Criteria

| Criteria                 | Target          |
| ------------------------ | --------------- |
| Dashboard load time      | < 1 second      |
| Order history pagination | 10 per page     |
| Address management       | CRUD operations |
| Wishlist sync            | Real-time       |
| Test coverage            | 100%            |

### 10.4 Requirements

| Requirement ID | Module Name | Description                        | User Story                                        | Expected System Behaviour/Outcome                                               | Role      |
| -------------- | ----------- | ---------------------------------- | ------------------------------------------------- | ------------------------------------------------------------------------------- | --------- |
| CUST-001       | Customer    | Create customer dashboard layout   | As a customer, I want a personal dashboard        | Sidebar navigation with Overview, Orders, Profile, Addresses, Wishlist, Support | Customer  |
| CUST-002       | Customer    | Create dashboard overview page     | As a customer, I want a summary view              | Quick stats: recent orders, pending deliveries, wishlist count, quick actions   | Customer  |
| CUST-003       | Customer    | Create order history page          | As a customer, I want to see all my orders        | List of orders with: order number, date, status, total, view details link       | Customer  |
| CUST-004       | Customer    | Create order detail view           | As a customer, I want to see order details        | Full order info: items, totals, delivery address, status timeline, tracking     | Customer  |
| CUST-005       | Customer    | Implement order status timeline    | As a customer, I want to track order progress     | Visual timeline: Pending → Accepted → Preparing → Out for Delivery → Delivered  | Customer  |
| CUST-006       | Customer    | Create "Reorder" functionality     | As a customer, I want to quickly reorder          | "Reorder" button adds all items from past order to cart                         | Customer  |
| CUST-007       | Customer    | Implement order filtering          | As a customer, I want to filter my orders         | Filter by: status, date range                                                   | Customer  |
| CUST-008       | Customer    | Create profile management page     | As a customer, I want to update my info           | Edit: name, email, phone, password, currency preference                         | Customer  |
| CUST-009       | Customer    | Implement password change          | As a customer, I want to change my password       | Current password verification, new password with confirmation                   | Customer  |
| CUST-010       | Customer    | Create address management page     | As a customer, I want to manage addresses         | List of saved addresses with add, edit, delete, set default                     | Customer  |
| CUST-011       | Customer    | Create add/edit address modal      | As a customer, I want to add new addresses        | Form modal for address details with autocomplete                                | Customer  |
| CUST-012       | Customer    | Create wishlist page               | As a customer, I want to view saved products      | Grid of wishlist items with "Add to Cart" and "Remove" options                  | Customer  |
| CUST-013       | Customer    | Implement wishlist to cart         | As a customer, I want to buy wishlist items       | "Add to Cart" button moves item to cart with quantity selector                  | Customer  |
| CUST-014       | Customer    | Create notifications page          | As a customer, I want to see my notifications     | List of notifications: order updates, promotions, sorted by date                | Customer  |
| CUST-015       | Customer    | Implement notification preferences | As a customer, I want to control notifications    | Toggle: email notifications, order updates, promotional emails                  | Customer  |
| CUST-016       | Customer    | Create support/help page           | As a customer, I want to get help                 | FAQ section, contact info, ticket submission form                               | Customer  |
| CUST-017       | Customer    | Create support ticket submission   | As a customer, I want to submit a support request | Form: subject, order (optional), message, attachment                            | Customer  |
| CUST-018       | Customer    | View support ticket history        | As a customer, I want to see my tickets           | List of submitted tickets with status and replies                               | Customer  |
| CUST-019       | Customer    | Implement currency preference      | As a customer, I want to set my default currency  | Dropdown to set AU$ or US$ as default, saved to profile                         | Customer  |
| CUST-020       | Customer    | Create order invoice download      | As a customer, I want to download invoices        | "Download Invoice" button generates PDF, opens in new tab                       | Customer  |
| CUST-021       | Customer    | Create customer API endpoints      | As a developer, I need customer APIs              | Endpoints for profile, addresses, orders, wishlist, tickets                     | Developer |
| CUST-022       | Customer    | Create customer Pinia stores       | As a developer, I need state management           | Stores for profile, orders, addresses, wishlist, notifications                  | Developer |
| CUST-023       | Customer    | Write customer module tests        | As a developer, I need 100% test coverage         | Unit, integration, E2E tests for all customer features                          | Developer |

### 10.5 Customer API Endpoints

| Method | Endpoint                                   | Description          | Auth |
| ------ | ------------------------------------------ | -------------------- | ---- |
| GET    | `/api/v1/customer/profile`                 | Get customer profile | Yes  |
| PUT    | `/api/v1/customer/profile`                 | Update profile       | Yes  |
| PUT    | `/api/v1/customer/password`                | Change password      | Yes  |
| GET    | `/api/v1/customer/orders`                  | List customer orders | Yes  |
| GET    | `/api/v1/customer/orders/{id}`             | Get order details    | Yes  |
| POST   | `/api/v1/customer/orders/{id}/reorder`     | Reorder past order   | Yes  |
| GET    | `/api/v1/customer/orders/{id}/invoice`     | Download invoice PDF | Yes  |
| GET    | `/api/v1/customer/addresses`               | List addresses       | Yes  |
| POST   | `/api/v1/customer/addresses`               | Add address          | Yes  |
| PUT    | `/api/v1/customer/addresses/{id}`          | Update address       | Yes  |
| DELETE | `/api/v1/customer/addresses/{id}`          | Delete address       | Yes  |
| GET    | `/api/v1/customer/wishlist`                | Get wishlist         | Yes  |
| POST   | `/api/v1/customer/wishlist`                | Add to wishlist      | Yes  |
| DELETE | `/api/v1/customer/wishlist/{productId}`    | Remove from wishlist | Yes  |
| GET    | `/api/v1/customer/notifications`           | Get notifications    | Yes  |
| PUT    | `/api/v1/customer/notifications/{id}/read` | Mark as read         | Yes  |
| GET    | `/api/v1/customer/tickets`                 | List support tickets | Yes  |
| POST   | `/api/v1/customer/tickets`                 | Create ticket        | Yes  |
| GET    | `/api/v1/customer/tickets/{id}`            | Get ticket details   | Yes  |

### 10.6 Customer Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER                                              AU$/US$  🔔(2)  👤 John ▼     │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  MY ACCOUNT    │  DASHBOARD OVERVIEW                                                │
│                │                                                                    │
│  ┌──────────┐  │  Welcome back, John!                                              │
│  │ Overview │  │                                                                    │
│  ├──────────┤  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ My Orders│  │  │  📦 Orders  │ │ 🚚 Pending  │ │ ❤️ Wishlist │ │ 🎫 Tickets  │  │
│  ├──────────┤  │  │     12      │ │      2      │ │      5      │ │      1      │  │
│  │ Profile  │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  ├──────────┤  │                                                                    │
│  │ Addresses│  │  RECENT ORDERS                                      [View All →]  │
│  ├──────────┤  │  ┌────────────────────────────────────────────────────────────┐   │
│  │ Wishlist │  │  │ #ZM-20251210-0023  │  Dec 10, 2025  │  $156.99  │ Delivered │   │
│  ├──────────┤  │  │ #ZM-20251208-0019  │  Dec 8, 2025   │  $89.50   │ Preparing │   │
│  │ Support  │  │  │ #ZM-20251205-0015  │  Dec 5, 2025   │  $234.00  │ Delivered │   │
│  ├──────────┤  │  └────────────────────────────────────────────────────────────┘   │
│  │ Settings │  │                                                                    │
│  └──────────┘  │  QUICK ACTIONS                                                     │
│                │  ┌────────────────┐ ┌────────────────┐ ┌────────────────┐          │
│  ───────────── │  │  🛒 Shop Now   │ │ 📍 Addresses   │ │ 🔄 Reorder     │          │
│  [Logout]      │  └────────────────┘ └────────────────┘ └────────────────┘          │
│                │                                                                    │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

---

## 10.7 Order Detail with Timeline

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  ORDER DETAILS                                                     [← Back to Orders]│
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  Order #ZM-20251210-0023                               Status: 🟢 OUT FOR DELIVERY  │
│  Placed: December 10, 2025 at 2:34 PM                                               │
│                                                                                     │
│  ORDER TIMELINE                                                                     │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━    │
│                                                                                     │
│      ✅ Placed ─────► ✅ Accepted ─────► ✅ Preparing ─────► 🔵 Delivering ─────► ○  │
│      Dec 10           Dec 10             Dec 10              Dec 10           Done  │
│      2:34 PM          2:45 PM            3:00 PM             4:30 PM                │
│                                                                                     │
│  ITEMS                                                                              │
│  ┌────────────────────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Ribeye Steak              1.5 kg x $45.99/kg              = $68.99     │ │
│  │ [IMG]  Chicken Breast            2.0 kg x $18.99/kg              = $37.98     │ │
│  │ [IMG]  Lamb Chops                1.0 kg x $32.99/kg              = $32.99     │ │
│  └────────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                     │
│  DELIVERY ADDRESS                      │  PAYMENT                                   │
│  John Smith                            │  Visa ****4242                             │
│  123 Main Street                       │  Paid: $139.96 AUD                         │
│  Engadine, NSW 2233                    │                                            │
│  Phone: 0412 345 678                   │                                            │
│                                        │                                            │
│  SUMMARY                               │  ACTIONS                                   │
│  Subtotal:           $139.96           │  ┌──────────────────────────────────────┐  │
│  Delivery:           FREE              │  │         🔄 REORDER                   │  │
│  ─────────────────────────────         │  └──────────────────────────────────────┘  │
│  TOTAL:              $139.96 AUD       │  ┌──────────────────────────────────────┐  │
│                                        │  │         📄 DOWNLOAD INVOICE          │  │
│                                        │  └──────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 2 Summary

### Modules Covered

| #   | Module                 | Requirements         | Priority |
| --- | ---------------------- | -------------------- | -------- |
| 7   | Shop & Product Catalog | SHOP-001 to SHOP-028 | P0       |
| 8   | Cart                   | CART-001 to CART-023 | P0       |
| 9   | Checkout               | CHK-001 to CHK-030   | P0       |
| 10  | Customer Dashboard     | CUST-001 to CUST-023 | P1       |

### Total Requirements in Part 2: 104

### Cumulative Total (Parts 1+2): 193

---

## 11. Staff Dashboard Module

### 11.1 Module Overview

| Field             | Value                    |
| ----------------- | ------------------------ |
| **Module Name**   | STAFF                    |
| **Priority**      | P1 - High                |
| **Dependencies**  | AUTH, DATABASE, CHECKOUT |
| **Documentation** | `/docs/staff/`           |
| **Tests**         | `/tests/staff/`          |

### 11.2 Module Objectives

1. Create unified staff interface for order processing and delivery
2. Display assigned orders queue with real-time updates
3. Enable order status updates throughout fulfillment process
4. Implement proof of delivery capture (signature/photo)
5. Provide delivery route optimization and navigation

### 11.3 Success Criteria

| Criteria                | Target               |
| ----------------------- | -------------------- |
| Dashboard load time     | < 1 second           |
| Real-time order updates | SSE within 2 seconds |
| POD capture success     | 100%                 |
| Status update response  | < 500ms              |
| Test coverage           | 100%                 |

### 11.4 Requirements

| Requirement ID | Module Name | Description                             | User Story                                                | Expected System Behaviour/Outcome                                                   | Role      |
| -------------- | ----------- | --------------------------------------- | --------------------------------------------------------- | ----------------------------------------------------------------------------------- | --------- |
| STAFF-001      | Staff       | Create staff dashboard layout           | As a staff member, I want a focused work interface        | Sidebar: Overview, Orders Queue, My Deliveries, Stock Check, Waste Log, My Activity | Staff     |
| STAFF-002      | Staff       | Create dashboard overview               | As a staff member, I want to see today's workload         | Stats: orders to prepare, deliveries assigned, completed today, pending             | Staff     |
| STAFF-003      | Staff       | Create orders queue page                | As a staff member, I want to see orders I need to process | List of assigned orders sorted by priority/time, with status indicators             | Staff     |
| STAFF-004      | Staff       | Display order details for processing    | As a staff member, I want to see what to pack             | Order items list with: product, quantity, packing checklist                         | Staff     |
| STAFF-005      | Staff       | Implement order status updates          | As a staff member, I want to update order progress        | Buttons: "Start Preparing" → "Mark Packed" → "Out for Delivery" → "Delivered"       | Staff     |
| STAFF-006      | Staff       | Create packing checklist                | As a staff member, I want to confirm all items packed     | Checkbox list of order items, all must be checked to mark packed                    | Staff     |
| STAFF-007      | Staff       | Print packing slip                      | As a staff member, I want to print order details          | "Print" button generates packing slip PDF, opens in new tab                         | Staff     |
| STAFF-008      | Staff       | Create my deliveries page               | As a staff member, I want to see my delivery route        | List of deliveries with addresses, map view, optimized route                        | Staff     |
| STAFF-009      | Staff       | Display delivery route map              | As a staff member, I want navigation help                 | Google Maps integration showing delivery stops, turn-by-turn option                 | Staff     |
| STAFF-010      | Staff       | Implement proof of delivery capture     | As a staff member, I want to record delivery completion   | Modal: signature pad + photo upload + notes                                         | Staff     |
| STAFF-011      | Staff       | Capture customer signature              | As a staff member, I want customer confirmation           | Canvas signature pad, saves as image                                                | Staff     |
| STAFF-012      | Staff       | Capture delivery photo                  | As a staff member, I want proof of delivery location      | Camera access or file upload for delivery photo                                     | Staff     |
| STAFF-013      | Staff       | Add delivery notes                      | As a staff member, I want to record delivery details      | Text field for notes (e.g., "Left at door", "Handed to recipient")                  | Staff     |
| STAFF-014      | Staff       | Create stock check page                 | As a staff member, I want to verify inventory             | Search products, view current stock levels, low stock alerts                        | Staff     |
| STAFF-015      | Staff       | Create waste log page                   | As a staff member, I want to record damaged/expired items | Form: product selection, quantity, reason (damaged/expired/other), notes            | Staff     |
| STAFF-016      | Staff       | Submit waste log entry                  | As a staff member, I want to save waste records           | Entry saved, stock automatically adjusted, notification to admin                    | Staff     |
| STAFF-017      | Staff       | Create my activity page                 | As a staff member, I want to see my work history          | Log of: orders processed, deliveries completed, waste logged                        | Staff     |
| STAFF-018      | Staff       | Implement real-time order notifications | As a staff member, I want alerts for new assignments      | SSE notifications when new order assigned, audio alert option                       | Staff     |
| STAFF-019      | Staff       | View customer delivery info             | As a staff member, I want customer contact for delivery   | Display: customer name, phone, address, delivery notes                              | Staff     |
| STAFF-020      | Staff       | Handle delivery issues                  | As a staff member, I want to report problems              | "Report Issue" button: customer not home, wrong address, refused delivery           | Staff     |
| STAFF-021      | Staff       | Create staff API endpoints              | As a developer, I need staff-specific APIs                | Endpoints for queue, status updates, POD, waste logging                             | Developer |
| STAFF-022      | Staff       | Implement SSE for real-time updates     | As a developer, I need push notifications                 | Server-Sent Events for order assignments and status changes                         | Developer |
| STAFF-023      | Staff       | Create staff Pinia stores               | As a developer, I need state management                   | Stores for orders queue, deliveries, activity                                       | Developer |
| STAFF-024      | Staff       | Write staff module tests                | As a developer, I need 100% test coverage                 | Unit, integration, E2E tests for all staff features                                 | Developer |

### 11.5 Staff API Endpoints

| Method | Endpoint                              | Description               | Auth        |
| ------ | ------------------------------------- | ------------------------- | ----------- |
| GET    | `/api/v1/staff/dashboard`             | Get dashboard stats       | Yes (Staff) |
| GET    | `/api/v1/staff/orders`                | Get assigned orders       | Yes (Staff) |
| GET    | `/api/v1/staff/orders/{id}`           | Get order details         | Yes (Staff) |
| PUT    | `/api/v1/staff/orders/{id}/status`    | Update order status       | Yes (Staff) |
| GET    | `/api/v1/staff/deliveries`            | Get assigned deliveries   | Yes (Staff) |
| POST   | `/api/v1/staff/deliveries/{id}/pod`   | Submit proof of delivery  | Yes (Staff) |
| POST   | `/api/v1/staff/deliveries/{id}/issue` | Report delivery issue     | Yes (Staff) |
| GET    | `/api/v1/staff/stock`                 | Search stock levels       | Yes (Staff) |
| POST   | `/api/v1/staff/waste`                 | Log waste entry           | Yes (Staff) |
| GET    | `/api/v1/staff/activity`              | Get personal activity log | Yes (Staff) |
| GET    | `/api/v1/staff/notifications/stream`  | SSE notification stream   | Yes (Staff) |

### 11.6 Staff Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  STAFF DASHBOARD                                         🔔(3)  👤 Sarah (Staff) ▼ │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  STAFF MENU    │  TODAY'S OVERVIEW                                                  │
│                │                                                                    │
│  ┌──────────┐  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ Overview │  │  │ 📋 To Pack  │ │ 🚚 Deliver  │ │ ✅ Done     │ │ ⚠️ Issues   │  │
│  ├──────────┤  │  │      5      │ │      3      │ │     12      │ │      0      │  │
│  │ Orders   │  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  │ Queue    │  │                                                                    │
│  ├──────────┤  │  ORDERS QUEUE                                          [Refresh]  │
│  │ My       │  │  ┌────────────────────────────────────────────────────────────┐   │
│  │Deliveries│  │  │ 🔴 #ZM-0024  │ John S.    │ 4 items │ URGENT   │ [Process] │   │
│  ├──────────┤  │  │ 🟡 #ZM-0025  │ Mary K.    │ 2 items │ Normal   │ [Process] │   │
│  │ Stock    │  │  │ 🟢 #ZM-0026  │ Peter L.   │ 6 items │ Normal   │ [Process] │   │
│  │ Check    │  │  │ 🟢 #ZM-0027  │ Lisa M.    │ 3 items │ Normal   │ [Process] │   │
│  ├──────────┤  │  │ 🟢 #ZM-0028  │ Tom R.     │ 1 item  │ Normal   │ [Process] │   │
│  │ Waste    │  │  └────────────────────────────────────────────────────────────┘   │
│  │ Log      │  │                                                                    │
│  ├──────────┤  │  MY DELIVERIES TODAY                                   [View Map] │
│  │ My       │  │  ┌────────────────────────────────────────────────────────────┐   │
│  │ Activity │  │  │ 1. #ZM-0020 │ Engadine    │ 12:30 PM │ En Route  │ [POD]   │   │
│  └──────────┘  │  │ 2. #ZM-0021 │ Menai       │ 1:00 PM  │ Pending   │         │   │
│                │  │ 3. #ZM-0022 │ Sutherland  │ 1:30 PM  │ Pending   │         │   │
│  ───────────── │  └────────────────────────────────────────────────────────────┘   │
│  [Logout]      │                                                                    │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

### 11.7 Order Processing Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  PROCESS ORDER #ZM-20251212-0024                                  [← Back to Queue] │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  Customer: John Smith                    Status: 🟡 PREPARING                       │
│  Phone: 0412 345 678                     Placed: Today at 10:15 AM                  │
│  Delivery: 123 Main St, Engadine 2233    Priority: 🔴 URGENT                        │
│                                                                                     │
│  PACKING CHECKLIST                                                                  │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                                                     │
│  ☑  Ribeye Steak                    1.5 kg                     ✓ Packed            │
│  ☑  Chicken Breast                  2.0 kg                     ✓ Packed            │
│  ☐  Lamb Chops                      1.0 kg                     Pending             │
│  ☐  Beef Mince                      0.5 kg                     Pending             │
│                                                                                     │
│  ─────────────────────────────────────────────────────────────────────────────────  │
│                                                                                     │
│  ORDER NOTES                                                                        │
│  "Please pack lamb chops separately. Ring doorbell on delivery."                    │
│                                                                                     │
│  ─────────────────────────────────────────────────────────────────────────────────  │
│                                                                                     │
│  ACTIONS                                                                            │
│  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐            │
│  │   🖨️ PRINT SLIP   │  │  ✅ MARK PACKED    │  │  ⚠️ REPORT ISSUE  │            │
│  └────────────────────┘  └────────────────────┘  └────────────────────┘            │
│                                                                                     │
│  Note: All items must be checked before marking as packed                           │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

### 11.8 Proof of Delivery Modal

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  PROOF OF DELIVERY - Order #ZM-0024                                          [X]   │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  Customer: John Smith                                                               │
│  Address: 123 Main St, Engadine 2233                                                │
│                                                                                     │
│  CUSTOMER SIGNATURE                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                             │   │
│  │                        [Signature Canvas Area]                              │   │
│  │                                                                             │   │
│  │                            ~~~~signature~~~~                                │   │
│  │                                                                             │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                            [Clear Signature]        │
│                                                                                     │
│  DELIVERY PHOTO                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                                                                             │   │
│  │                    📷 [Take Photo] or [Upload Photo]                        │   │
│  │                                                                             │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  DELIVERY NOTES                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │ Handed to customer directly                                                 │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │                        ✅ COMPLETE DELIVERY                                 │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 12. Admin Dashboard Module

### 12.1 Module Overview

| Field             | Value                |
| ----------------- | -------------------- |
| **Module Name**   | ADMIN                |
| **Priority**      | P0 - Critical        |
| **Dependencies**  | All previous modules |
| **Documentation** | `/docs/admin/`       |
| **Tests**         | `/tests/admin/`      |

### 12.2 Module Objectives

1. Create comprehensive admin dashboard for business management
2. Implement full CRUD for products, categories, users
3. Provide order lifecycle management with all actions
4. Display business analytics and KPIs
5. Manage system settings and configurations

### 12.3 Success Criteria

| Criteria            | Target               |
| ------------------- | -------------------- |
| Dashboard load time | < 2 seconds          |
| CRUD operations     | All successful       |
| Report generation   | < 5 seconds          |
| Real-time updates   | SSE within 2 seconds |
| Test coverage       | 100%                 |

### 12.4 Bulk Operations Policy

> **Important:** Bulk operations are restricted to Activity Logs only.

| Module        | Bulk Delete | Bulk Save | Bulk Export |
| ------------- | :---------: | :-------: | :---------: |
| Products      |     ❌      |    ❌     |     ❌      |
| Orders        |     ❌      |    ❌     |     ❌      |
| Customers     |     ❌      |    ❌     |     ❌      |
| Activity Logs |     ✅      |    ✅     |     ❌      |

### 12.5 Requirements

| Requirement ID | Module Name | Description                             | User Story                                                  | Expected System Behaviour/Outcome                                                                                          | Role      |
| -------------- | ----------- | --------------------------------------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | --------- |
| ADMIN-001      | Admin       | Create admin dashboard layout           | As an admin, I want a comprehensive management interface    | Sidebar: Overview, Orders, Products, Inventory, Customers, Staff, Deliveries, Finance, Reports, Promotions, Settings, Logs | Admin     |
| ADMIN-002      | Admin       | Create dashboard overview with KPIs     | As an admin, I want to see business performance at a glance | Cards: Today's revenue, orders, new customers, pending orders + charts                                                     | Admin     |
| ADMIN-003      | Admin       | Display real-time order alerts          | As an admin, I want immediate notification of new orders    | SSE alerts with sound, badge count, quick action buttons                                                                   | Admin     |
| ADMIN-004      | Admin       | Create revenue chart (7/30 days)        | As an admin, I want to visualize revenue trends             | Line/bar chart showing daily revenue, compare periods                                                                      | Admin     |
| ADMIN-005      | Admin       | Create orders management page           | As an admin, I want to manage all orders                    | Table: order#, customer, status, total, date, actions                                                                      | Admin     |
| ADMIN-006      | Admin       | Implement order filtering               | As an admin, I want to find specific orders                 | Filters: status, date range, customer (limited filters per policy)                                                         | Admin     |
| ADMIN-007      | Admin       | Create order detail view                | As an admin, I want full order information                  | Complete order data with edit, status change, refund actions                                                               | Admin     |
| ADMIN-008      | Admin       | Implement order actions                 | As an admin, I want to manage order lifecycle               | Actions: Accept, Reject, Assign Staff, Edit, Cancel, Refund                                                                | Admin     |
| ADMIN-009      | Admin       | Assign orders to staff                  | As an admin, I want to delegate order processing            | Dropdown to select staff member, notification sent                                                                         | Admin     |
| ADMIN-010      | Admin       | Process refunds                         | As an admin, I want to refund orders                        | Refund modal: full/partial amount, reason, confirm                                                                         | Admin     |
| ADMIN-011      | Admin       | Create products management page         | As an admin, I want to manage product catalog               | Table: image, name, category, price, stock, status, actions                                                                | Admin     |
| ADMIN-012      | Admin       | Create add/edit product form            | As an admin, I want to create and update products           | Modal form: name, category, price, description, images, stock, status                                                      | Admin     |
| ADMIN-013      | Admin       | Implement product image upload          | As an admin, I want to add product photos                   | Multi-image upload with drag-drop, reorder, delete                                                                         | Admin     |
| ADMIN-014      | Admin       | Create categories management            | As an admin, I want to organize products                    | CRUD for categories: name, slug, image, parent, sort order                                                                 | Admin     |
| ADMIN-015      | Admin       | Delete products (single)                | As an admin, I want to remove products                      | Delete with confirmation, soft delete to preserve order history                                                            | Admin     |
| ADMIN-016      | Admin       | Create customers management page        | As an admin, I want to manage customer accounts             | Table: name, email, phone, orders count, total spent, actions                                                              | Admin     |
| ADMIN-017      | Admin       | View customer details                   | As an admin, I want to see customer information             | Profile, order history, addresses, support tickets                                                                         | Admin     |
| ADMIN-018      | Admin       | Create staff management page            | As an admin, I want to manage staff accounts                | Table: name, email, role, status, activity, actions                                                                        | Admin     |
| ADMIN-019      | Admin       | Create/edit staff accounts              | As an admin, I want to add staff members                    | Form: name, email, phone, password, role                                                                                   | Admin     |
| ADMIN-020      | Admin       | Activate/deactivate staff               | As an admin, I want to control staff access                 | Toggle active status, deactivated staff cannot login                                                                       | Admin     |
| ADMIN-021      | Admin       | View staff activity                     | As an admin, I want to monitor staff performance            | Activity log: orders processed, deliveries, login times                                                                    | Admin     |
| ADMIN-022      | Admin       | Create promotions management            | As an admin, I want to create discounts                     | CRUD: promo code, type (% or $), value, min order, dates, status                                                           | Admin     |
| ADMIN-023      | Admin       | Create audit/activity logs page         | As an admin, I want to see system activity                  | Log table: user, action, details, IP, timestamp                                                                            | Admin     |
| ADMIN-024      | Admin       | Implement bulk delete for activity logs | As an admin, I want to clean old logs                       | Select multiple logs, bulk delete with date range option                                                                   | Admin     |
| ADMIN-025      | Admin       | Create admin API endpoints              | As a developer, I need admin CRUD APIs                      | Full CRUD endpoints for products, categories, users, orders, promotions                                                    | Developer |
| ADMIN-026      | Admin       | Implement admin middleware              | As a developer, I need admin-only route protection          | Middleware verifies admin role, returns 403 for non-admins                                                                 | Developer |
| ADMIN-027      | Admin       | Create admin Pinia stores               | As a developer, I need state management                     | Stores for orders, products, customers, staff, promotions                                                                  | Developer |
| ADMIN-028      | Admin       | Write admin module tests                | As a developer, I need 100% test coverage                   | Unit, integration, E2E tests for all admin features                                                                        | Developer |

### 12.6 Admin API Endpoints

| Method | Endpoint                           | Description          | Auth        |
| ------ | ---------------------------------- | -------------------- | ----------- |
| GET    | `/api/v1/admin/dashboard`          | Get dashboard stats  | Yes (Admin) |
| GET    | `/api/v1/admin/orders`             | List all orders      | Yes (Admin) |
| GET    | `/api/v1/admin/orders/{id}`        | Get order details    | Yes (Admin) |
| PUT    | `/api/v1/admin/orders/{id}`        | Update order         | Yes (Admin) |
| PUT    | `/api/v1/admin/orders/{id}/status` | Change order status  | Yes (Admin) |
| PUT    | `/api/v1/admin/orders/{id}/assign` | Assign to staff      | Yes (Admin) |
| POST   | `/api/v1/admin/orders/{id}/refund` | Process refund       | Yes (Admin) |
| GET    | `/api/v1/admin/products`           | List products        | Yes (Admin) |
| POST   | `/api/v1/admin/products`           | Create product       | Yes (Admin) |
| PUT    | `/api/v1/admin/products/{id}`      | Update product       | Yes (Admin) |
| DELETE | `/api/v1/admin/products/{id}`      | Delete product       | Yes (Admin) |
| GET    | `/api/v1/admin/categories`         | List categories      | Yes (Admin) |
| POST   | `/api/v1/admin/categories`         | Create category      | Yes (Admin) |
| PUT    | `/api/v1/admin/categories/{id}`    | Update category      | Yes (Admin) |
| DELETE | `/api/v1/admin/categories/{id}`    | Delete category      | Yes (Admin) |
| GET    | `/api/v1/admin/customers`          | List customers       | Yes (Admin) |
| GET    | `/api/v1/admin/customers/{id}`     | Get customer details | Yes (Admin) |
| PUT    | `/api/v1/admin/customers/{id}`     | Update customer      | Yes (Admin) |
| GET    | `/api/v1/admin/staff`              | List staff           | Yes (Admin) |
| POST   | `/api/v1/admin/staff`              | Create staff account | Yes (Admin) |
| PUT    | `/api/v1/admin/staff/{id}`         | Update staff         | Yes (Admin) |
| DELETE | `/api/v1/admin/staff/{id}`         | Delete staff         | Yes (Admin) |
| GET    | `/api/v1/admin/promotions`         | List promotions      | Yes (Admin) |
| POST   | `/api/v1/admin/promotions`         | Create promotion     | Yes (Admin) |
| PUT    | `/api/v1/admin/promotions/{id}`    | Update promotion     | Yes (Admin) |
| DELETE | `/api/v1/admin/promotions/{id}`    | Delete promotion     | Yes (Admin) |
| GET    | `/api/v1/admin/activity-logs`      | List activity logs   | Yes (Admin) |
| DELETE | `/api/v1/admin/activity-logs/bulk` | Bulk delete logs     | Yes (Admin) |

### 12.7 Admin Dashboard Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  ADMIN DASHBOARD                                     🔔(5)  👤 Admin ▼   [Logout]   │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  ADMIN MENU    │  DASHBOARD OVERVIEW                              Today: Dec 12     │
│                │                                                                    │
│  ┌──────────┐  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │ Overview │  │  │ 💰 Revenue  │ │ 📦 Orders   │ │ 👥 New      │ │ ⏳ Pending  │  │
│  ├──────────┤  │  │  $2,450.00  │ │     18      │ │ Customers 3 │ │      5      │  │
│  │ Orders   │  │  │   ▲ 12%    │ │    ▲ 8%    │ │    ▲ 2     │ │    ▼ 2     │  │
│  ├──────────┤  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│  │ Products │  │                                                                    │
│  ├──────────┤  │  REVENUE CHART (Last 7 Days)                                       │
│  │ Inventory│  │  ┌────────────────────────────────────────────────────────────┐   │
│  ├──────────┤  │  │     $3k ┤                                    ╭─╮            │   │
│  │ Customers│  │  │         │                              ╭─╮  │ │            │   │
│  ├──────────┤  │  │     $2k ┤              ╭─╮   ╭─╮      │ │  │ │  ╭─╮       │   │
│  │ Staff    │  │  │         │        ╭─╮  │ │  │ │  ╭─╮  │ │  │ │  │ │       │   │
│  ├──────────┤  │  │     $1k ┤  ╭─╮  │ │  │ │  │ │  │ │  │ │  │ │  │ │       │   │
│  │Deliveries│  │  │         │  │ │  │ │  │ │  │ │  │ │  │ │  │ │  │ │       │   │
│  ├──────────┤  │  │       0 ┼──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴──┴─┴────────│   │
│  │ Finance  │  │  │          Mon   Tue   Wed   Thu   Fri   Sat   Sun         │   │
│  ├──────────┤  │  └────────────────────────────────────────────────────────────┘   │
│  │ Reports  │  │                                                                    │
│  ├──────────┤  │  RECENT ORDERS                                     [View All →]   │
│  │Promotions│  │  ┌────────────────────────────────────────────────────────────┐   │
│  ├──────────┤  │  │ #ZM-0024 │ John S.  │ $156.99 │ 🟡 Pending  │ [View][✓][✗]│   │
│  │ Settings │  │  │ #ZM-0023 │ Mary K.  │ $89.50  │ 🟢 Preparing│ [View]      │   │
│  ├──────────┤  │  │ #ZM-0022 │ Peter L. │ $234.00 │ 🔵 Delivery │ [View]      │   │
│  │ Logs     │  │  └────────────────────────────────────────────────────────────┘   │
│  └──────────┘  │                                                                    │
│                │  LOW STOCK ALERTS                    TOP PRODUCTS TODAY            │
│  ───────────── │  ┌───────────────────────┐          ┌───────────────────────┐     │
│  Developed     │  │ ⚠️ Ribeye (5 left)    │          │ 1. Chicken Breast (8) │     │
│  with ❤️ by    │  │ ⚠️ Lamb Chops (3)     │          │ 2. Ribeye Steak (6)   │     │
│  bguvava       │  │ ⚠️ Pork Ribs (2)      │          │ 3. Beef Mince (5)     │     │
│                │  └───────────────────────┘          └───────────────────────┘     │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

### 12.8 Product Management Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  PRODUCTS MANAGEMENT                                                [+ Add Product] │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  🔍 Search products...                    Category: [All ▼]    Status: [All ▼]     │
│                                                                                     │
│  ┌──────────────────────────────────────────────────────────────────────────────┐  │
│  │ ☐ │ Image │ Product Name     │ Category │ Price/kg │ Stock │ Status │Actions│  │
│  ├──────────────────────────────────────────────────────────────────────────────┤  │
│  │ ☐ │ [IMG] │ Ribeye Steak     │ Beef     │ $45.99   │  5 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Chicken Breast   │ Chicken  │ $18.99   │  45   │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Lamb Chops       │ Lamb     │ $32.99   │  3 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Pork Ribs        │ Pork     │ $24.99   │  2 ⚠️ │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ Beef Mince       │ Beef     │ $16.99   │  30   │ Active │ ✏️ 🗑️ │  │
│  │ ☐ │ [IMG] │ T-Bone Steak     │ Beef     │ $38.99   │  0    │Inactive│ ✏️ 🗑️ │  │
│  └──────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                     │
│  Showing 1-6 of 45 products                              ◀  1  2  3  ...  8  ▶     │
│                                                                                     │
│  Note: Bulk operations not available for products. Delete items individually.       │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 3A Summary

### Modules Covered

| #   | Module          | Requirements           | Priority |
| --- | --------------- | ---------------------- | -------- |
| 11  | Staff Dashboard | STAFF-001 to STAFF-024 | P1       |
| 12  | Admin Dashboard | ADMIN-001 to ADMIN-028 | P0       |

### Total Requirements in Part 3A: 52

### Cumulative Total (Parts 1+2+3A): 245

---

## 13. Inventory Management Module

### 13.1 Module Overview

| Field             | Value               |
| ----------------- | ------------------- |
| **Module Name**   | INVENTORY           |
| **Priority**      | P1 - High           |
| **Dependencies**  | ADMIN, DATABASE     |
| **Documentation** | `/docs/inventory/`  |
| **Tests**         | `/tests/inventory/` |

### 13.2 Module Objectives

1. Provide real-time stock level tracking and management
2. Implement stock receive, adjust, and deduct operations
3. Create low stock alerts and notifications
4. Track inventory history with full audit trail
5. Manage waste/damaged goods logging

### 13.3 Success Criteria

| Criteria         | Target                  |
| ---------------- | ----------------------- |
| Stock accuracy   | 99.9%                   |
| Real-time sync   | < 1 second              |
| Low stock alerts | Triggered automatically |
| Audit trail      | 100% of changes logged  |
| Test coverage    | 100%                    |

### 13.4 Requirements

| Requirement ID | Module Name | Description                     | User Story                                              | Expected System Behaviour/Outcome                                                     | Role      |
| -------------- | ----------- | ------------------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------- | --------- |
| INV-001        | Inventory   | Create inventory dashboard      | As an admin, I want an overview of stock levels         | Dashboard with: total products, low stock count, out of stock count, recent movements | Admin     |
| INV-002        | Inventory   | Display stock levels table      | As an admin, I want to see all product stock            | Table: product, category, current stock, min stock, status, last updated              | Admin     |
| INV-003        | Inventory   | Implement stock filtering       | As an admin, I want to find specific stock items        | Filters: category, stock status (low/out/normal), search by name                      | Admin     |
| INV-004        | Inventory   | Create stock receive form       | As an admin, I want to add incoming stock               | Form: product, quantity, supplier (optional), notes, date                             | Admin     |
| INV-005        | Inventory   | Create stock adjustment form    | As an admin, I want to correct stock discrepancies      | Form: product, current qty, new qty, reason, notes                                    | Admin     |
| INV-006        | Inventory   | Auto-deduct stock on order      | As an admin, I want stock to decrease on orders         | Stock automatically reduced when order status changes to "Preparing"                  | Admin     |
| INV-007        | Inventory   | Restore stock on order cancel   | As an admin, I want stock restored for cancelled orders | Stock automatically returned when order is cancelled/refunded                         | Admin     |
| INV-008        | Inventory   | Set minimum stock thresholds    | As an admin, I want to define low stock levels          | Per-product min_stock field, triggers alert when reached                              | Admin     |
| INV-009        | Inventory   | Create low stock alerts         | As an admin, I want notifications for low stock         | Dashboard alert + email when stock falls below minimum                                | Admin     |
| INV-010        | Inventory   | Create out of stock alerts      | As an admin, I want to know when items sell out         | Dashboard alert + email when stock reaches zero                                       | Admin     |
| INV-011        | Inventory   | Display inventory history       | As an admin, I want to see stock movements              | Log table: date, product, type, quantity, user, reason                                | Admin     |
| INV-012        | Inventory   | View product inventory detail   | As an admin, I want to see single product history       | Product-specific view with all stock movements                                        | Admin     |
| INV-013        | Inventory   | Create waste management section | As an admin, I want to track damaged/expired goods      | List of waste entries with: product, qty, reason, date, logged by                     | Admin     |
| INV-014        | Inventory   | Review and approve waste logs   | As an admin, I want to verify waste entries             | Approve/reject waste entries from staff                                               | Admin     |
| INV-015        | Inventory   | Generate inventory report       | As an admin, I want stock reports                       | Report: current levels, movements, waste summary                                      | Admin     |
| INV-016        | Inventory   | Export inventory data           | As an admin, I want to download inventory               | Export to PDF with view in new tab / download option                                  | Admin     |
| INV-017        | Inventory   | Create inventory API endpoints  | As a developer, I need inventory APIs                   | CRUD endpoints for stock operations                                                   | Developer |
| INV-018        | Inventory   | Write inventory module tests    | As a developer, I need 100% test coverage               | Unit tests for calculations, integration tests for auto-deduct                        | Developer |

### 13.5 Inventory API Endpoints

| Method | Endpoint                              | Description           | Auth        |
| ------ | ------------------------------------- | --------------------- | ----------- |
| GET    | `/api/v1/admin/inventory`             | List all inventory    | Yes (Admin) |
| GET    | `/api/v1/admin/inventory/{productId}` | Get product inventory | Yes (Admin) |
| POST   | `/api/v1/admin/inventory/receive`     | Receive stock         | Yes (Admin) |
| POST   | `/api/v1/admin/inventory/adjust`      | Adjust stock          | Yes (Admin) |
| GET    | `/api/v1/admin/inventory/history`     | Get inventory history | Yes (Admin) |
| GET    | `/api/v1/admin/inventory/low-stock`   | Get low stock items   | Yes (Admin) |
| GET    | `/api/v1/admin/inventory/alerts`      | Get inventory alerts  | Yes (Admin) |
| GET    | `/api/v1/admin/waste`                 | List waste entries    | Yes (Admin) |
| PUT    | `/api/v1/admin/waste/{id}/approve`    | Approve waste entry   | Yes (Admin) |
| GET    | `/api/v1/admin/inventory/export`      | Export inventory PDF  | Yes (Admin) |

### 13.6 Inventory Dashboard Wireframe

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

## 14. Delivery Management Module

### 14.1 Module Overview

| Field             | Value                  |
| ----------------- | ---------------------- |
| **Module Name**   | DELIVERY               |
| **Priority**      | P1 - High              |
| **Dependencies**  | ADMIN, STAFF, CHECKOUT |
| **Documentation** | `/docs/delivery/`      |
| **Tests**         | `/tests/delivery/`     |

### 14.2 Module Objectives

1. Manage delivery zones and fee calculations
2. Track all deliveries with status updates
3. View and manage proof of delivery records
4. Handle delivery issues and exceptions
5. Provide delivery analytics and reporting

### 14.3 Success Criteria

| Criteria                 | Target            |
| ------------------------ | ----------------- |
| Delivery success rate    | > 98%             |
| POD capture rate         | 100%              |
| Fee calculation accuracy | 100%              |
| Zone coverage            | All service areas |
| Test coverage            | 100%              |

### 14.4 Requirements

| Requirement ID | Module Name | Description                      | User Story                                       | Expected System Behaviour/Outcome                                     | Role      |
| -------------- | ----------- | -------------------------------- | ------------------------------------------------ | --------------------------------------------------------------------- | --------- |
| DEL-001        | Delivery    | Create delivery dashboard        | As an admin, I want an overview of deliveries    | Stats: today's deliveries, pending, in progress, completed, issues    | Admin     |
| DEL-002        | Delivery    | Display all deliveries list      | As an admin, I want to see all deliveries        | Table: order#, customer, address, status, assigned staff, time        | Admin     |
| DEL-003        | Delivery    | Filter deliveries                | As an admin, I want to find specific deliveries  | Filters: status, date range, staff member                             | Admin     |
| DEL-004        | Delivery    | View delivery detail             | As an admin, I want full delivery information    | Order details, customer info, POD, timeline, notes                    | Admin     |
| DEL-005        | Delivery    | Assign delivery to staff         | As an admin, I want to delegate deliveries       | Dropdown to select staff, notification sent to staff                  | Admin     |
| DEL-006        | Delivery    | Reassign delivery                | As an admin, I want to change assigned staff     | Change staff assignment with reason                                   | Admin     |
| DEL-007        | Delivery    | View proof of delivery           | As an admin, I want to see delivery confirmation | Display signature, photo, notes, timestamp                            | Admin     |
| DEL-008        | Delivery    | Download POD document            | As an admin, I want to save POD                  | Download POD as PDF with all details                                  | Admin     |
| DEL-009        | Delivery    | Handle delivery issues           | As an admin, I want to resolve problems          | View reported issues, add resolution, update status                   | Admin     |
| DEL-010        | Delivery    | Create delivery zones management | As an admin, I want to manage delivery areas     | CRUD for zones: name, postcodes, free delivery threshold, fee         | Admin     |
| DEL-011        | Delivery    | Add delivery zone                | As an admin, I want to create new zones          | Form: zone name, postcodes (comma-separated), min order, delivery fee | Admin     |
| DEL-012        | Delivery    | Edit delivery zone               | As an admin, I want to update zones              | Modify zone settings, postcodes, fees                                 | Admin     |
| DEL-013        | Delivery    | Delete delivery zone             | As an admin, I want to remove zones              | Delete with confirmation, check for active orders                     | Admin     |
| DEL-014        | Delivery    | Configure delivery fees          | As an admin, I want to set delivery pricing      | Base fee, per-km rate, free delivery threshold                        | Admin     |
| DEL-015        | Delivery    | View delivery map                | As an admin, I want to see delivery locations    | Google Maps with all delivery pins for the day                        | Admin     |
| DEL-016        | Delivery    | Generate delivery report         | As an admin, I want delivery analytics           | Report: deliveries by zone, staff performance, issues                 | Admin     |
| DEL-017        | Delivery    | Export delivery data             | As an admin, I want to download delivery info    | Export to PDF with view/download options                              | Admin     |
| DEL-018        | Delivery    | Create delivery API endpoints    | As a developer, I need delivery APIs             | Endpoints for zones, assignments, POD, reports                        | Developer |
| DEL-019        | Delivery    | Write delivery module tests      | As a developer, I need 100% test coverage        | Unit, integration tests for fee calculation, zones                    | Developer |

### 14.5 Delivery API Endpoints

| Method | Endpoint                               | Description           | Auth        |
| ------ | -------------------------------------- | --------------------- | ----------- |
| GET    | `/api/v1/admin/deliveries`             | List all deliveries   | Yes (Admin) |
| GET    | `/api/v1/admin/deliveries/{id}`        | Get delivery details  | Yes (Admin) |
| PUT    | `/api/v1/admin/deliveries/{id}/assign` | Assign to staff       | Yes (Admin) |
| GET    | `/api/v1/admin/deliveries/{id}/pod`    | Get proof of delivery | Yes (Admin) |
| PUT    | `/api/v1/admin/deliveries/{id}/issue`  | Update issue status   | Yes (Admin) |
| GET    | `/api/v1/admin/delivery-zones`         | List delivery zones   | Yes (Admin) |
| POST   | `/api/v1/admin/delivery-zones`         | Create zone           | Yes (Admin) |
| PUT    | `/api/v1/admin/delivery-zones/{id}`    | Update zone           | Yes (Admin) |
| DELETE | `/api/v1/admin/delivery-zones/{id}`    | Delete zone           | Yes (Admin) |
| GET    | `/api/v1/admin/delivery-settings`      | Get delivery settings | Yes (Admin) |
| PUT    | `/api/v1/admin/delivery-settings`      | Update settings       | Yes (Admin) |
| GET    | `/api/v1/admin/deliveries/export`      | Export deliveries PDF | Yes (Admin) |

### 14.6 Delivery Zones Wireframe

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
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 15. Reports & Analytics Module

### 15.1 Module Overview

| Field             | Value             |
| ----------------- | ----------------- |
| **Module Name**   | REPORTS           |
| **Priority**      | P2 - Medium       |
| **Dependencies**  | All data modules  |
| **Documentation** | `/docs/reports/`  |
| **Tests**         | `/tests/reports/` |

### 15.2 Module Objectives

1. Provide comprehensive sales and revenue analytics
2. Generate product performance reports
3. Track customer analytics and behavior
4. Create staff performance reports
5. Export all reports as PDF with view/download options

### 15.3 Success Criteria

| Criteria               | Target          |
| ---------------------- | --------------- |
| Report generation time | < 5 seconds     |
| Data accuracy          | 100%            |
| Export functionality   | View + Download |
| Date range flexibility | Custom ranges   |
| Test coverage          | 100%            |

### 15.4 Document Actions Policy

> **All reports must support:**
>
> - **View**: Opens PDF in new browser tab
> - **Download**: Downloads PDF file directly

### 15.5 Requirements

| Requirement ID | Module Name | Description                        | User Story                                 | Expected System Behaviour/Outcome                            | Role      |
| -------------- | ----------- | ---------------------------------- | ------------------------------------------ | ------------------------------------------------------------ | --------- |
| RPT-001        | Reports     | Create reports dashboard           | As an admin, I want a reports overview     | Dashboard with report categories and quick access            | Admin     |
| RPT-002        | Reports     | Create sales summary report        | As an admin, I want to see sales overview  | Report: total revenue, orders, avg order value by period     | Admin     |
| RPT-003        | Reports     | Create revenue by period report    | As an admin, I want revenue breakdown      | Daily/weekly/monthly revenue with charts                     | Admin     |
| RPT-004        | Reports     | Create orders by status report     | As an admin, I want order status breakdown | Pie chart + table: pending, processing, delivered, cancelled | Admin     |
| RPT-005        | Reports     | Create product sales report        | As an admin, I want product performance    | Table: product, qty sold, revenue, % of total                | Admin     |
| RPT-006        | Reports     | Create category sales report       | As an admin, I want category performance   | Chart + table: category, qty sold, revenue                   | Admin     |
| RPT-007        | Reports     | Create top products report         | As an admin, I want bestsellers list       | Top 10 products by revenue and quantity                      | Admin     |
| RPT-008        | Reports     | Create low performing products     | As an admin, I want underperforming items  | Products with low/no sales in period                         | Admin     |
| RPT-009        | Reports     | Create customer report             | As an admin, I want customer analytics     | New vs returning, top customers, avg spend                   | Admin     |
| RPT-010        | Reports     | Create customer acquisition report | As an admin, I want growth metrics         | New customers by period, source tracking                     | Admin     |
| RPT-011        | Reports     | Create staff performance report    | As an admin, I want staff productivity     | Orders processed, deliveries, ratings by staff               | Admin     |
| RPT-012        | Reports     | Create delivery performance report | As an admin, I want delivery metrics       | On-time rate, issues, by zone                                | Admin     |
| RPT-013        | Reports     | Create inventory report            | As an admin, I want stock analytics        | Stock levels, turnover, waste summary                        | Admin     |
| RPT-014        | Reports     | Create financial summary report    | As an admin, I want financial overview     | Revenue, fees, refunds, net income                           | Admin     |
| RPT-015        | Reports     | Create payment methods report      | As an admin, I want payment breakdown      | Orders by payment method, success rates                      | Admin     |
| RPT-016        | Reports     | Implement date range selector      | As an admin, I want custom date ranges     | Presets (today, week, month, year) + custom picker           | Admin     |
| RPT-017        | Reports     | Implement report charts            | As an admin, I want visual analytics       | Line, bar, pie charts using Chart.js                         | Admin     |
| RPT-018        | Reports     | Export report to PDF (View)        | As an admin, I want to view report         | "View" button opens PDF in new tab                           | Admin     |
| RPT-019        | Reports     | Export report to PDF (Download)    | As an admin, I want to download report     | "Download" button downloads PDF file                         | Admin     |
| RPT-020        | Reports     | Schedule automated reports         | As an admin, I want regular reports        | Configure weekly/monthly email reports                       | Admin     |
| RPT-021        | Reports     | Create reports API endpoints       | As a developer, I need report APIs         | Endpoints for each report type                               | Developer |
| RPT-022        | Reports     | Write reports module tests         | As a developer, I need 100% test coverage  | Unit tests for calculations, integration tests               | Developer |

### 15.6 Reports API Endpoints

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

### 15.7 Reports Dashboard Wireframe

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

## Part 3B Summary

### Modules Covered

| #   | Module               | Requirements       | Priority |
| --- | -------------------- | ------------------ | -------- |
| 13  | Inventory Management | INV-001 to INV-018 | P1       |
| 14  | Delivery Management  | DEL-001 to DEL-019 | P1       |
| 15  | Reports & Analytics  | RPT-001 to RPT-022 | P2       |

### Total Requirements in Part 3B: 59

### Cumulative Total (Parts 1+2+3A+3B): 304

---

## 16. System Settings Module

### 16.1 Module Overview

| Field             | Value              |
| ----------------- | ------------------ |
| **Module Name**   | SETTINGS           |
| **Priority**      | P1 - High          |
| **Dependencies**  | ADMIN              |
| **Documentation** | `/docs/settings/`  |
| **Tests**         | `/tests/settings/` |

### 16.2 Module Objectives

1. Provide centralized system configuration management
2. Configure store information, operating hours, and branding
3. Manage payment gateway settings and credentials
4. Configure email templates and notification preferences
5. Manage currency exchange rate settings
6. Control application-wide feature toggles

### 16.3 Success Criteria

| Criteria              | Target               |
| --------------------- | -------------------- |
| All settings editable | Admin only           |
| Changes take effect   | Immediate            |
| Validation            | All fields validated |
| Audit logging         | All changes logged   |
| Test coverage         | 100%                 |

### 16.4 Settings Categories

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

### 16.5 Requirements

| Requirement ID | Module Name | Description                       | User Story                                        | Expected System Behaviour/Outcome                          | Role      |
| -------------- | ----------- | --------------------------------- | ------------------------------------------------- | ---------------------------------------------------------- | --------- |
| SET-001        | Settings    | Create settings dashboard         | As an admin, I want centralized configuration     | Settings page with categorized sections                    | Admin     |
| SET-002        | Settings    | Configure store name and tagline  | As an admin, I want to set business identity      | Store name appears in header, emails, invoices             | Admin     |
| SET-003        | Settings    | Configure store logo              | As an admin, I want to upload business logo       | Logo upload with preview, used across application          | Admin     |
| SET-004        | Settings    | Configure store address           | As an admin, I want to set business address       | Address displayed on landing page, invoices, emails        | Admin     |
| SET-005        | Settings    | Configure contact information     | As an admin, I want to set contact details        | Phone, email, social media links configured                | Admin     |
| SET-006        | Settings    | Configure operating hours         | As an admin, I want to set business hours         | Daily hours displayed on landing page, delivery scheduling | Admin     |
| SET-007        | Settings    | Configure holiday schedules       | As an admin, I want to mark closed days           | Holiday dates prevent delivery scheduling                  | Admin     |
| SET-008        | Settings    | Configure Stripe credentials      | As an admin, I want to set up card payments       | Stripe API keys (test/live), webhook secret                | Admin     |
| SET-009        | Settings    | Configure PayPal credentials      | As an admin, I want to enable PayPal              | PayPal client ID/secret (sandbox/live)                     | Admin     |
| SET-010        | Settings    | Configure Afterpay credentials    | As an admin, I want to enable Afterpay            | Afterpay merchant ID/secret                                | Admin     |
| SET-011        | Settings    | Enable/disable payment methods    | As an admin, I want to control payment options    | Toggle each payment method on/off                          | Admin     |
| SET-012        | Settings    | Configure SMTP settings           | As an admin, I want email delivery working        | SMTP host, port, username, password, encryption            | Admin     |
| SET-013        | Settings    | Configure sender email            | As an admin, I want branded email sender          | From name and email address for all emails                 | Admin     |
| SET-014        | Settings    | Manage email templates            | As an admin, I want to customize emails           | Edit templates: order confirmation, password reset, etc.   | Admin     |
| SET-015        | Settings    | Configure default currency        | As an admin, I want to set base currency          | Default currency (AU$) for all transactions                | Admin     |
| SET-016        | Settings    | Configure exchange rate API       | As an admin, I want automatic currency conversion | ExchangeRate-API key, update frequency                     | Admin     |
| SET-017        | Settings    | Set exchange rate manually        | As an admin, I want to override exchange rate     | Manual AU$/US$ rate override option                        | Admin     |
| SET-018        | Settings    | Configure minimum order amount    | As an admin, I want to set order minimum          | Minimum order value for delivery (e.g., $50)               | Admin     |
| SET-019        | Settings    | Configure free delivery threshold | As an admin, I want to set free delivery minimum  | Free delivery for orders over (e.g., $100)                 | Admin     |
| SET-020        | Settings    | Configure session timeout         | As an admin, I want to set security timeout       | Session timeout duration (default 5 minutes)               | Admin     |
| SET-021        | Settings    | Configure password policies       | As an admin, I want secure passwords              | Min length, complexity requirements                        | Admin     |
| SET-022        | Settings    | Configure notification recipients | As an admin, I want order notifications           | Email addresses for new order alerts                       | Admin     |
| SET-023        | Settings    | Enable/disable features           | As an admin, I want to toggle features            | Feature flags: wishlist, reviews, guest checkout           | Admin     |
| SET-024        | Settings    | Configure SEO meta defaults       | As an admin, I want SEO configuration             | Default title, description, keywords                       | Admin     |
| SET-025        | Settings    | Configure social media links      | As an admin, I want social presence               | Facebook, Instagram, Twitter URLs                          | Admin     |
| SET-026        | Settings    | Import/export settings            | As an admin, I want settings backup               | Export settings to JSON, import from file                  | Admin     |
| SET-027        | Settings    | View settings change history      | As an admin, I want to audit changes              | Log of all settings changes with user, timestamp           | Admin     |
| SET-028        | Settings    | Create settings API endpoints     | As a developer, I need settings CRUD APIs         | Endpoints for reading/updating settings                    | Developer |
| SET-029        | Settings    | Cache settings for performance    | As a developer, I need fast settings access       | Settings cached in Redis/file, invalidate on change        | Developer |
| SET-030        | Settings    | Write settings module tests       | As a developer, I need 100% test coverage         | Unit tests for validation, integration tests for APIs      | Developer |

### 16.6 Settings API Endpoints

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

### 16.7 Settings Dashboard Wireframe

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
│  │   Delivery   │  │                 │  LOGO   │                                │ │
│  │   Security   │  │                 │  [IMG]  │                                │ │
│  │   Features   │  │                 └─────────┘                                │ │
│  │   SEO        │  │                                                             │ │
│  │              │  │  Address:       [6/1053 Old Princes Highway            ]   │ │
│  └──────────────┘  │  Suburb:        [Engadine                              ]   │ │
│                    │  State:         [NSW                 ] Postcode: [2233]   │ │
│                    │                                                             │ │
│                    │  Phone:         [XXXX XXX XXX                          ]   │ │
│                    │  Email:         [info@zambezimeats.com                 ]   │ │
│                    │  ABN:           [XX XXX XXX XXX                        ]   │ │
│                    │                                                             │ │
│                    │  ────────────────────────────────────────────────────────── │ │
│                    │                                                             │ │
│                    │  SOCIAL MEDIA                                               │ │
│                    │  Facebook:      [https://facebook.com/zambezimeats     ]   │ │
│                    │  Instagram:     [https://instagram.com/zambezimeats    ]   │ │
│                    │  Twitter:       [                                      ]   │ │
│                    │                                                             │ │
│                    └──────────────────────────────────────────────────────────────┘ │
│                                                                                     │
│  ┌──────────────────────────────────────────────────────────────────────────────┐   │
│  │  PAYMENT GATEWAYS                                                [Save]      │   │
│  │  ────────────────────────────────────────────────────────────────────────── │   │
│  │                                                                             │   │
│  │  STRIPE                                               [✓ Enabled]          │   │
│  │  ┌───────────────────────────────────────────────────────────────────────┐ │   │
│  │  │ Mode:         ○ Test  ● Live                                         │ │   │
│  │  │ Public Key:   [pk_live_****************************]                  │ │   │
│  │  │ Secret Key:   [sk_live_**************************** ]  [Show]        │ │   │
│  │  │ Webhook Key:  [whsec_*****************************  ]  [Show]        │ │   │
│  │  └───────────────────────────────────────────────────────────────────────┘ │   │
│  │                                                                             │   │
│  │  PAYPAL                                               [✓ Enabled]          │   │
│  │  ┌───────────────────────────────────────────────────────────────────────┐ │   │
│  │  │ Mode:         ○ Sandbox  ● Live                                      │ │   │
│  │  │ Client ID:    [****************************                       ]  │ │   │
│  │  │ Secret:       [****************************                  ] [Show]│ │   │
│  │  └───────────────────────────────────────────────────────────────────────┘ │   │
│  │                                                                             │   │
│  │  AFTERPAY                                             [✓ Enabled]          │   │
│  │  CASH ON DELIVERY                                     [✓ Enabled]          │   │
│  │                                                                             │   │
│  └──────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 17. Deployment & Go-Live

### 17.1 Module Overview

| Field             | Value                |
| ----------------- | -------------------- |
| **Module Name**   | DEPLOYMENT           |
| **Priority**      | P0 - Critical        |
| **Dependencies**  | All Modules          |
| **Documentation** | `/docs/deployment/`  |
| **Tests**         | `/tests/deployment/` |

### 17.2 Module Objectives

1. Prepare production environment on CyberPanel VPS
2. Configure secure server with SSL certificates
3. Deploy Laravel backend with optimizations
4. Deploy Vue.js frontend with production build
5. Configure database migrations and seeders
6. Set up monitoring, logging, and backup systems
7. Establish rollback strategy for failed deployments

### 17.3 Success Criteria

| Criteria                 | Target               |
| ------------------------ | -------------------- |
| Server response time     | < 200ms              |
| Page load time           | < 2 seconds          |
| SSL certificate          | Valid A+ rating      |
| Zero downtime deployment | ✓                    |
| Database backup          | Daily automated      |
| Monitoring               | 24/7 uptime tracking |
| Test coverage            | All E2E tests pass   |

### 17.4 Server Requirements

| Component    | Specification              |
| ------------ | -------------------------- |
| VPS Provider | CyberPanel compatible      |
| OS           | Ubuntu 22.04 LTS           |
| RAM          | Minimum 4GB                |
| CPU          | 2+ vCPUs                   |
| Storage      | 50GB+ SSD                  |
| PHP          | 8.2+                       |
| MySQL        | 8.0                        |
| Node.js      | 20 LTS                     |
| Web Server   | OpenLiteSpeed (CyberPanel) |

### 17.5 Requirements

| Requirement ID | Module Name | Description                           | User Story                                   | Expected System Behaviour/Outcome                              | Role      |
| -------------- | ----------- | ------------------------------------- | -------------------------------------------- | -------------------------------------------------------------- | --------- |
| DEP-001        | Deployment  | Provision VPS server                  | As a developer, I need a production server   | VPS with Ubuntu 22.04 LTS provisioned                          | Developer |
| DEP-002        | Deployment  | Install CyberPanel                    | As a developer, I need server management     | CyberPanel installed with OpenLiteSpeed                        | Developer |
| DEP-003        | Deployment  | Configure domain DNS                  | As a developer, I need domain resolution     | A records pointing to VPS IP address                           | Developer |
| DEP-004        | Deployment  | Create SSL certificate                | As a developer, I need HTTPS                 | Let's Encrypt SSL certificate installed, auto-renewal          | Developer |
| DEP-005        | Deployment  | Configure PHP 8.2                     | As a developer, I need PHP configured        | PHP 8.2 with required extensions, optimized settings           | Developer |
| DEP-006        | Deployment  | Create MySQL database                 | As a developer, I need production database   | `my_zambezimeats` database created with secure user            | Developer |
| DEP-007        | Deployment  | Set up Git repository access          | As a developer, I need code deployment       | SSH keys configured for Git pull                               | Developer |
| DEP-008        | Deployment  | Create deployment directory structure | As a developer, I need organized deployment  | Directories: /var/www/zambezimeats/{backend,frontend}          | Developer |
| DEP-009        | Deployment  | Configure environment variables       | As a developer, I need production config     | .env file with production credentials (DB, API keys, etc.)     | Developer |
| DEP-010        | Deployment  | Deploy Laravel backend                | As a developer, I need backend deployed      | Clone repo, composer install, migrations, optimize             | Developer |
| DEP-011        | Deployment  | Configure Laravel optimizations       | As a developer, I need optimized performance | Config cache, route cache, view cache, autoloader optimization | Developer |
| DEP-012        | Deployment  | Set up Laravel queue worker           | As a developer, I need background jobs       | Queue worker running via Supervisor                            | Developer |
| DEP-013        | Deployment  | Set up Laravel scheduler              | As a developer, I need scheduled tasks       | Cron job for Laravel scheduler                                 | Developer |
| DEP-014        | Deployment  | Build Vue.js for production           | As a developer, I need frontend built        | npm run build with production optimizations                    | Developer |
| DEP-015        | Deployment  | Deploy Vue.js frontend                | As a developer, I need frontend deployed     | Built files deployed to public directory                       | Developer |
| DEP-016        | Deployment  | Configure OpenLiteSpeed vhost         | As a developer, I need web server config     | Virtual host with rewrite rules for SPA and API                | Developer |
| DEP-017        | Deployment  | Run database migrations               | As a developer, I need database schema       | All migrations run successfully                                | Developer |
| DEP-018        | Deployment  | Run database seeders                  | As a developer, I need initial data          | Admin user, settings, delivery zones seeded                    | Developer |
| DEP-019        | Deployment  | Configure payment webhooks            | As a developer, I need payment callbacks     | Stripe/PayPal webhooks configured with production URLs         | Developer |
| DEP-020        | Deployment  | Set up log rotation                   | As a developer, I need log management        | Laravel logs rotated daily, 30-day retention                   | Developer |
| DEP-021        | Deployment  | Configure error monitoring            | As a developer, I need error tracking        | Sentry or similar for error reporting                          | Developer |
| DEP-022        | Deployment  | Set up uptime monitoring              | As a developer, I need availability tracking | UptimeRobot or similar for 24/7 monitoring                     | Developer |
| DEP-023        | Deployment  | Configure database backups            | As a developer, I need data protection       | Daily automated backups, 30-day retention, off-site storage    | Developer |
| DEP-024        | Deployment  | Configure file storage backups        | As a developer, I need file protection       | Daily backup of uploads, product images                        | Developer |
| DEP-025        | Deployment  | Create deployment script              | As a developer, I need automated deployment  | Shell script for pull, build, deploy, cache clear              | Developer |
| DEP-026        | Deployment  | Create rollback procedure             | As a developer, I need failure recovery      | Documented rollback steps, previous version backup             | Developer |
| DEP-027        | Deployment  | Configure firewall rules              | As a developer, I need security              | UFW configured: allow 80, 443, 22; deny others                 | Developer |
| DEP-028        | Deployment  | Set up fail2ban                       | As a developer, I need intrusion prevention  | Fail2ban protecting SSH and web services                       | Developer |
| DEP-029        | Deployment  | Run production smoke tests            | As a developer, I need deployment validation | Automated tests verifying all critical paths                   | Developer |
| DEP-030        | Deployment  | Create deployment documentation       | As a developer, I need deployment guide      | Complete runbook in `/docs/deployment/`                        | Developer |

### 17.6 Deployment Workflow

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                           DEPLOYMENT PIPELINE                                        │
└─────────────────────────────────────────────────────────────────────────────────────┘

  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
  │  Developer  │────▶│   Git Push  │────▶│    Tests    │────▶│   Build     │
  │   Commit    │     │   to Main   │     │    Pass?    │     │  Frontend   │
  └─────────────┘     └─────────────┘     └──────┬──────┘     └──────┬──────┘
                                                 │                   │
                                           No ◄──┤                   │
                                                 │                   │
                                                 ▼                   ▼
                                          ┌─────────────┐     ┌─────────────┐
                                          │    Notify   │     │   Deploy    │
                                          │   Failure   │     │   Backend   │
                                          └─────────────┘     └──────┬──────┘
                                                                     │
                       ┌─────────────────────────────────────────────┘
                       │
                       ▼
  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
  │   Deploy    │────▶│    Run      │────▶│   Smoke     │────▶│    LIVE     │
  │  Frontend   │     │ Migrations  │     │   Tests     │     │   ✓         │
  └─────────────┘     └─────────────┘     └──────┬──────┘     └─────────────┘
                                                 │
                                           Fail? │
                                                 ▼
                                          ┌─────────────┐
                                          │  Rollback   │
                                          │  Previous   │
                                          └─────────────┘
```

### 17.7 Server Directory Structure

```
/var/www/zambezimeats/
├── backend/                          # Laravel 11
│   ├── current -> releases/v1.2.3    # Symlink to current release
│   ├── releases/                     # Release versions
│   │   ├── v1.2.3/
│   │   ├── v1.2.2/
│   │   └── v1.2.1/
│   ├── shared/                       # Shared across releases
│   │   ├── storage/
│   │   │   ├── app/
│   │   │   │   └── public/          # Uploaded files
│   │   │   ├── logs/
│   │   │   └── framework/
│   │   └── .env
│   └── repo/                         # Git repository
├── frontend/                         # Vue.js 3
│   ├── current -> releases/v1.2.3
│   ├── releases/
│   └── repo/
├── backups/                          # Database backups
│   ├── daily/
│   └── weekly/
├── logs/                             # Aggregated logs
└── scripts/                          # Deployment scripts
    ├── deploy.sh
    ├── rollback.sh
    └── backup.sh
```

### 17.8 Environment Variables (Production)

```env
# Application
APP_NAME="Zambezi Meats"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://zambezimeats.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_zambezimeats
DB_USERNAME=zambezi_prod
DB_PASSWORD=***SECURE_PASSWORD***

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=5

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@zambezimeats.com
MAIL_PASSWORD=***MAIL_PASSWORD***
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=orders@zambezimeats.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_MODE=live

# Currency
EXCHANGE_RATE_API_KEY=...

# Monitoring
SENTRY_LARAVEL_DSN=https://...@sentry.io/...
```

---

## 18. Final Checklist

### 18.1 Module Overview

| Field             | Value                     |
| ----------------- | ------------------------- |
| **Module Name**   | CHECKLIST                 |
| **Priority**      | P0 - Critical             |
| **Dependencies**  | All Modules               |
| **Documentation** | `/docs/checklist/`        |
| **Tests**         | All `/tests/` directories |

### 18.2 Module Objectives

1. Verify all modules implemented and tested
2. Ensure security audit completed
3. Validate performance benchmarks met
4. Confirm all documentation complete
5. Obtain go-live approval

### 18.3 Requirements

| Requirement ID | Module Name | Description                       | User Story                                               | Expected System Behaviour/Outcome                                | Role      |
| -------------- | ----------- | --------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------- | --------- |
| CHK-001        | Checklist   | Verify DEV-ENV module complete    | As a project manager, I want module completion verified  | All 10 DEV-ENV requirements verified complete                    | Admin     |
| CHK-002        | Checklist   | Verify PROJ-INIT module complete  | As a project manager, I want module completion verified  | All 15 PROJ-INIT requirements verified complete                  | Admin     |
| CHK-003        | Checklist   | Verify DATABASE module complete   | As a project manager, I want module completion verified  | All 24 DATABASE requirements verified complete                   | Admin     |
| CHK-004        | Checklist   | Verify AUTH module complete       | As a project manager, I want module completion verified  | All 20 AUTH requirements verified complete, 100% tests pass      | Admin     |
| CHK-005        | Checklist   | Verify LANDING module complete    | As a project manager, I want module completion verified  | All 20 LANDING requirements verified complete, 100% tests pass   | Admin     |
| CHK-006        | Checklist   | Verify SHOP module complete       | As a project manager, I want module completion verified  | All 28 SHOP requirements verified complete, 100% tests pass      | Admin     |
| CHK-007        | Checklist   | Verify CART module complete       | As a project manager, I want module completion verified  | All 23 CART requirements verified complete, 100% tests pass      | Admin     |
| CHK-008        | Checklist   | Verify CHECKOUT module complete   | As a project manager, I want module completion verified  | All 30 CHECKOUT requirements verified complete, 100% tests pass  | Admin     |
| CHK-009        | Checklist   | Verify CUSTOMER module complete   | As a project manager, I want module completion verified  | All 27 CUSTOMER requirements verified complete, 100% tests pass  | Admin     |
| CHK-010        | Checklist   | Verify STAFF module complete      | As a project manager, I want module completion verified  | All 22 STAFF requirements verified complete, 100% tests pass     | Admin     |
| CHK-011        | Checklist   | Verify ADMIN module complete      | As a project manager, I want module completion verified  | All 30 ADMIN requirements verified complete, 100% tests pass     | Admin     |
| CHK-012        | Checklist   | Verify INVENTORY module complete  | As a project manager, I want module completion verified  | All 18 INVENTORY requirements verified complete, 100% tests pass | Admin     |
| CHK-013        | Checklist   | Verify DELIVERY module complete   | As a project manager, I want module completion verified  | All 19 DELIVERY requirements verified complete, 100% tests pass  | Admin     |
| CHK-014        | Checklist   | Verify REPORTS module complete    | As a project manager, I want module completion verified  | All 22 REPORTS requirements verified complete, 100% tests pass   | Admin     |
| CHK-015        | Checklist   | Verify SETTINGS module complete   | As a project manager, I want module completion verified  | All 30 SETTINGS requirements verified complete, 100% tests pass  | Admin     |
| CHK-016        | Checklist   | Verify DEPLOYMENT module complete | As a project manager, I want module completion verified  | All 30 DEPLOYMENT requirements verified complete                 | Admin     |
| CHK-017        | Checklist   | Run full test suite               | As a developer, I need all tests passing                 | All unit, integration, E2E tests pass at 100%                    | Developer |
| CHK-018        | Checklist   | Perform security audit            | As a security officer, I want vulnerabilities identified | OWASP top 10 checked, no critical/high vulnerabilities           | Admin     |
| CHK-019        | Checklist   | Verify SSL configuration          | As a security officer, I want secure connections         | SSL Labs score A+, HTTPS enforced                                | Admin     |
| CHK-020        | Checklist   | Test payment flows (Stripe)       | As a tester, I need payment verification                 | Stripe payments work in live mode                                | Tester    |
| CHK-021        | Checklist   | Test payment flows (PayPal)       | As a tester, I need payment verification                 | PayPal payments work in live mode                                | Tester    |
| CHK-022        | Checklist   | Test payment flows (Afterpay)     | As a tester, I need payment verification                 | Afterpay payments work in live mode                              | Tester    |
| CHK-023        | Checklist   | Verify email delivery             | As a tester, I need email verification                   | All transactional emails delivered                               | Tester    |
| CHK-024        | Checklist   | Performance benchmark             | As a performance engineer, I want speed verified         | Page load < 2s, API response < 200ms                             | Developer |
| CHK-025        | Checklist   | Mobile responsiveness check       | As a tester, I need mobile verification                  | All pages render correctly on mobile devices                     | Tester    |
| CHK-026        | Checklist   | Cross-browser testing             | As a tester, I need browser compatibility                | Chrome, Firefox, Safari, Edge all working                        | Tester    |
| CHK-027        | Checklist   | Accessibility audit               | As an accessibility specialist, I want WCAG compliance   | WCAG 2.1 Level AA compliance verified                            | Admin     |
| CHK-028        | Checklist   | SEO verification                  | As a marketing manager, I want SEO ready                 | Meta tags, sitemap, robots.txt configured                        | Admin     |
| CHK-029        | Checklist   | Backup verification               | As a sysadmin, I want backups working                    | Database and file backups tested and restorable                  | Admin     |
| CHK-030        | Checklist   | Documentation complete            | As a project manager, I want complete documentation      | All `/docs/{module}/` directories populated                      | Admin     |
| CHK-031        | Checklist   | User acceptance testing           | As a business owner, I want final approval               | UAT completed by stakeholders                                    | Admin     |
| CHK-032        | Checklist   | Go-live approval                  | As a project manager, I need launch authorization        | Sign-off from all stakeholders                                   | Admin     |

### 18.4 Pre-Launch Checklist

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                         PRE-LAUNCH CHECKLIST                                        │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  DEVELOPMENT                                                                        │
│  ☐ All modules implemented                                                          │
│  ☐ All unit tests passing (100%)                                                   │
│  ☐ All integration tests passing (100%)                                            │
│  ☐ All E2E tests passing (100%)                                                    │
│  ☐ Code review completed                                                            │
│  ☐ No critical bugs open                                                            │
│                                                                                     │
│  SECURITY                                                                           │
│  ☐ SSL certificate installed and valid                                             │
│  ☐ CSRF protection enabled                                                          │
│  ☐ SQL injection prevention verified                                               │
│  ☐ XSS protection verified                                                          │
│  ☐ Session security configured (5-min timeout)                                     │
│  ☐ Password hashing verified (bcrypt)                                              │
│  ☐ API rate limiting enabled                                                        │
│  ☐ Firewall rules configured                                                        │
│  ☐ Fail2ban active                                                                  │
│                                                                                     │
│  PERFORMANCE                                                                        │
│  ☐ Page load time < 2 seconds                                                      │
│  ☐ API response time < 200ms                                                       │
│  ☐ Database queries optimized                                                       │
│  ☐ Images optimized (WebP, lazy loading)                                           │
│  ☐ Caching configured (Redis)                                                       │
│  ☐ CDN configured (if applicable)                                                  │
│                                                                                     │
│  PAYMENTS                                                                           │
│  ☐ Stripe live mode tested                                                          │
│  ☐ PayPal live mode tested                                                          │
│  ☐ Afterpay live mode tested                                                        │
│  ☐ Cash on Delivery flow verified                                                  │
│  ☐ Refund process tested                                                            │
│  ☐ Webhooks configured and verified                                                │
│                                                                                     │
│  OPERATIONS                                                                         │
│  ☐ Database backup automated                                                        │
│  ☐ File backup automated                                                            │
│  ☐ Backup restore tested                                                            │
│  ☐ Error monitoring configured (Sentry)                                            │
│  ☐ Uptime monitoring configured                                                    │
│  ☐ Log rotation configured                                                          │
│                                                                                     │
│  DOCUMENTATION                                                                      │
│  ☐ API documentation complete                                                       │
│  ☐ User guide complete                                                              │
│  ☐ Admin guide complete                                                             │
│  ☐ Deployment runbook complete                                                     │
│  ☐ Troubleshooting guide complete                                                  │
│                                                                                     │
│  LEGAL & COMPLIANCE                                                                 │
│  ☐ Privacy policy published                                                         │
│  ☐ Terms of service published                                                       │
│  ☐ Cookie consent implemented                                                       │
│  ☐ Data retention policy defined                                                   │
│                                                                                     │
│  FINAL APPROVAL                                                                     │
│  ☐ Developer sign-off                                                               │
│  ☐ QA sign-off                                                                      │
│  ☐ Security sign-off                                                                │
│  ☐ Business owner sign-off                                                          │
│  ☐ GO-LIVE APPROVED                                                                 │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 3C Summary

### Modules Covered

| #   | Module               | Requirements       | Priority |
| --- | -------------------- | ------------------ | -------- |
| 16  | System Settings      | SET-001 to SET-030 | P1       |
| 17  | Deployment & Go-Live | DEP-001 to DEP-030 | P0       |
| 18  | Final Checklist      | CHK-001 to CHK-032 | P0       |

### Total Requirements in Part 3C: 92

### Cumulative Total (Parts 1+2+3A+3B+3C): 396

---

## Document Summary

### Complete Module Overview

| Part | #   | Module                         | Requirements                        | Priority |
| ---- | --- | ------------------------------ | ----------------------------------- | -------- |
| 1    | 1   | Development Environment        | DEV-ENV-001 to DEV-ENV-010 (10)     | P0       |
| 1    | 2   | Project Initialization         | PROJ-INIT-001 to PROJ-INIT-015 (15) | P0       |
| 1    | 3   | Database Design & Setup        | DB-001 to DB-024 (24)               | P0       |
| 1    | 4   | Authentication & Authorization | AUTH-001 to AUTH-020 (20)           | P0       |
| 1    | 5   | Landing Page                   | LAND-001 to LAND-020 (20)           | P1       |
| 2    | 6   | Shop & Product Catalog         | SHOP-001 to SHOP-028 (28)           | P0       |
| 2    | 7   | Cart                           | CART-001 to CART-023 (23)           | P0       |
| 2    | 8   | Checkout                       | CHK-001 to CHK-030 (30)             | P0       |
| 2    | 9   | Customer Dashboard             | CUST-001 to CUST-027 (27)           | P1       |
| 3A   | 10  | Staff Dashboard                | STF-001 to STF-022 (22)             | P1       |
| 3A   | 11  | Admin Dashboard                | ADM-001 to ADM-030 (30)             | P1       |
| 3B   | 12  | Inventory Management           | INV-001 to INV-018 (18)             | P1       |
| 3B   | 13  | Delivery Management            | DEL-001 to DEL-019 (19)             | P1       |
| 3B   | 14  | Reports & Analytics            | RPT-001 to RPT-022 (22)             | P2       |
| 3C   | 15  | System Settings                | SET-001 to SET-030 (30)             | P1       |
| 3C   | 16  | Deployment & Go-Live           | DEP-001 to DEP-030 (30)             | P0       |
| 3C   | 17  | Final Checklist                | CHK-001 to CHK-032 (32)             | P0       |

### Total Requirements: 396

### Requirements by Priority

| Priority      | Count   | Percentage |
| ------------- | ------- | ---------- |
| P0 - Critical | 202     | 51%        |
| P1 - High     | 172     | 43%        |
| P2 - Medium   | 22      | 6%         |
| **Total**     | **396** | **100%**   |

### Technology Stack Summary

| Layer          | Technology                                                       |
| -------------- | ---------------------------------------------------------------- |
| **Frontend**   | Vue.js 3 + Vite + Tailwind CSS + shadcn/ui + Headless UI + Pinia |
| **Backend**    | Laravel 12.x + PHP 8.2+ + Laravel Sanctum                        |
| **Database**   | MySQL 8.0 (`my_zambezimeats`)                                    |
| **Real-Time**  | Server-Sent Events (SSE)                                         |
| **Payments**   | Stripe, PayPal, Afterpay, Cash on Delivery                       |
| **Currency**   | AU$ (default) + US$ via ExchangeRate-API                         |
| **Deployment** | CyberPanel VPS + OpenLiteSpeed                                   |

### Key Design Decisions

| Decision            | Implementation                                 |
| ------------------- | ---------------------------------------------- |
| Session Security    | 5-minute auto-logout on inactivity             |
| API Versioning      | All endpoints under `/api/v1/`                 |
| Search Optimization | Limited filters (Category, Price, Stock, Sort) |
| Bulk Operations     | Restricted to activity logs only               |
| Document Actions    | View (new tab) + Download options              |
| Testing Standard    | 100% test pass rate per module                 |
| Documentation       | `/docs/{module_name}/` folder structure        |
| Tests               | `/tests/{module_name}/` folder structure       |

---

## Version History

| Version | Date              | Author  | Changes                                    |
| ------- | ----------------- | ------- | ------------------------------------------ |
| 1.0     | December 12, 2025 | bguvava | Initial MVP document with 396 requirements |

---

## Appendices

### Appendix A: Glossary

| Term  | Definition                            |
| ----- | ------------------------------------- |
| MVP   | Minimum Viable Product                |
| SPA   | Single Page Application               |
| API   | Application Programming Interface     |
| CRUD  | Create, Read, Update, Delete          |
| POD   | Proof of Delivery                     |
| SSE   | Server-Sent Events                    |
| CSRF  | Cross-Site Request Forgery            |
| OWASP | Open Web Application Security Project |

### Appendix B: Reference Documents

| Document            | Location                                 |
| ------------------- | ---------------------------------------- |
| Project Description | `.github/prompts/project_description.md` |
| User Requirements   | `.github/user_requirements.txt`          |
| API Documentation   | `/docs/api/`                             |
| Database Schema     | `/docs/database/`                        |
| Deployment Guide    | `/docs/deployment/`                      |

### Appendix C: Contact Information

| Role          | Contact       |
| ------------- | ------------- |
| Developer     | bguvava       |
| Project Owner | Zambezi Meats |

---

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                     │
│                          END OF MVP DOCUMENT                                        │
│                                                                                     │
│                        Total Requirements: 396                                      │
│                        Total Modules: 17                                            │
│                                                                                     │
│                     ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━                              │
│                                                                                     │
│                       Developed with ❤️ by bguvava                                  │
│                                                                                     │
│                         © 2025 Zambezi Meats                                        │
│                        All Rights Reserved                                          │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```
