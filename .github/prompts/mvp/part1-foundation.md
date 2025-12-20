# Zambezi Meats MVP - Part 1: Foundation Module

## Module Overview

| Field             | Value                |
| ----------------- | -------------------- |
| **Module Name**   | FOUNDATION           |
| **Priority**      | P0 - Critical        |
| **Dependencies**  | None                 |
| **Documentation** | `/docs/foundation/`  |
| **Tests**         | `/tests/foundation/` |
| **Database Name** | `my_zambezimeats`    |

This module combines:

- Development Environment Setup (DEV-ENV)
- Project Initialization (PROJ-INIT)
- Database Design & Setup (DATABASE)

**Total Requirements: 49**

---

## 1.1 Development Environment Setup

### Objectives

1. Set up consistent development environment across all developers
2. Install and configure all required tools and extensions
3. Establish coding standards and linting rules
4. Configure VS Code for optimal Laravel + Vue.js development

### Success Criteria

| Criteria                      | Target                  |
| ----------------------------- | ----------------------- |
| All tools installed           | 100%                    |
| VS Code extensions configured | 100%                    |
| Linting passes                | 0 errors                |
| Environment variables set     | All required            |
| Local server running          | Both frontend & backend |

### Requirements

| Requirement ID | Description                                | User Story                                                                     | Expected Outcome                                                                                                                | Role      |
| -------------- | ------------------------------------------ | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------- | --------- |
| DEV-ENV-001    | Install PHP 8.2+ with required extensions  | As a developer, I need PHP 8.2+ installed with all Laravel-required extensions | PHP runs with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, gd                                 | Developer |
| DEV-ENV-002    | Install Composer 2.x                       | As a developer, I need Composer to manage PHP dependencies                     | Composer is globally accessible via CLI                                                                                         | Developer |
| DEV-ENV-003    | Install Node.js 20 LTS + npm               | As a developer, I need Node.js for frontend build tools                        | Node.js and npm are globally accessible via CLI                                                                                 | Developer |
| DEV-ENV-004    | Install MySQL 8.0                          | As a developer, I need MySQL for database operations                           | MySQL server running on port 3306, accessible via CLI and GUI                                                                   | Developer |
| DEV-ENV-005    | Configure VS Code with required extensions | As a developer, I need VS Code configured for Laravel + Vue development        | Extensions installed: Volar, PHP Intelephense, Laravel Extra Intellisense, Tailwind CSS IntelliSense, ESLint, Prettier, GitLens | Developer |
| DEV-ENV-006    | Set up Git with proper configuration       | As a developer, I need version control configured                              | Git configured with user name, email, and .gitignore                                                                            | Developer |
| DEV-ENV-007    | Configure ESLint and Prettier              | As a developer, I need consistent code formatting                              | ESLint + Prettier configurations in place, format on save enabled                                                               | Developer |
| DEV-ENV-008    | Install Laravel Installer                  | As a developer, I need to quickly scaffold Laravel projects                    | `laravel new` command available globally                                                                                        | Developer |
| DEV-ENV-009    | Set up local hosts entry                   | As a developer, I need a local domain for testing                              | `zambezimeats.local` pointing to 127.0.0.1                                                                                      | Developer |
| DEV-ENV-010    | Create environment documentation           | As a developer, I need setup instructions for onboarding                       | Complete README with setup steps in `/docs/foundation/`                                                                         | Developer |

### VS Code Extensions Required

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

## 1.2 Project Initialization

### Objectives

1. Initialize Laravel 12.x backend project
2. Initialize Vue.js 3 + Vite frontend project
3. Configure project structure according to specifications
4. Set up API versioning structure
5. Configure CORS and sanctum for SPA authentication

### Success Criteria

| Criteria                | Target     |
| ----------------------- | ---------- |
| Laravel project created | ✓          |
| Vue.js project created  | ✓          |
| API versioning in place | `/api/v1/` |
| Sanctum configured      | ✓          |
| CORS configured         | ✓          |
| Both servers running    | ✓          |

### Requirements

| Requirement ID  | Description                                       | User Story                                                     | Expected Outcome                                                                      | Role      |
| --------------- | ------------------------------------------------- | -------------------------------------------------------------- | ------------------------------------------------------------------------------------- | --------- |
| PROJ-INIT-001   | Create Laravel 12.x backend project               | As a developer, I need a Laravel project with proper structure | Laravel project created in `/backend` with standard structure                         | Developer |
| PROJ-INIT-002   | Create Vue.js 3 + Vite frontend project           | As a developer, I need a Vue.js SPA project                    | Vue.js project created in `/frontend` with Vite, Tailwind CSS, shadcn/ui              | Developer |
| PROJ-INIT-003   | Configure API versioning folder structure         | As a developer, I need versioned API routes                    | Routes organized as `/api/v1/` with versioned controllers                             | Developer |
| PROJ-INIT-004   | Install and configure Laravel Sanctum             | As a developer, I need SPA authentication                      | Sanctum installed, configured for cookie-based SPA auth                               | Developer |
| PROJ-INIT-005   | Configure CORS for frontend-backend communication | As a developer, I need cross-origin requests to work           | CORS configured to allow frontend origin                                              | Developer |
| PROJ-INIT-006   | Set up environment files                          | As a developer, I need separate configs for dev/staging/prod   | `.env.example` with all required variables documented                                 | Developer |
| PROJ-INIT-007   | Install frontend dependencies                     | As a developer, I need all frontend packages                   | Pinia, Axios, VeeValidate, Zod, Day.js, currency.js, Vue Sonner, Lucide Vue installed | Developer |
| PROJ-INIT-008   | Configure Tailwind CSS with custom theme          | As a developer, I need branded styling                         | Tailwind configured with Zambezi Meats color palette                                  | Developer |
| PROJ-INIT-009   | Set up shadcn/ui components                       | As a developer, I need UI component library                    | shadcn/ui initialized with base components                                            | Developer |
| PROJ-INIT-010   | Configure Pinia store structure                   | As a developer, I need state management                        | Pinia stores organized by domain (auth, cart, products, etc.)                         | Developer |
| PROJ-INIT-012.x | Set up Axios instance with interceptors           | As a developer, I need configured HTTP client                  | Axios instance with base URL, auth interceptors, error handling                       | Developer |
| PROJ-INIT-012   | Create base layout components                     | As a developer, I need layout templates                        | Guest layout, Customer layout, Staff layout, Admin layout                             | Developer |
| PROJ-INIT-013   | Configure Vue Router with guards                  | As a developer, I need routing with protection                 | Routes defined with auth guards for role-based access                                 | Developer |
| PROJ-INIT-014   | Set up session timeout (5 minutes)                | As a developer, I need auto-logout for security                | Session expires after 5 minutes of inactivity, user redirected to login               | Developer |
| PROJ-INIT-015   | Create project documentation                      | As a developer, I need project setup docs                      | README.md with complete setup and run instructions                                    | Developer |

### Directory Structure

```
zambezi-meats/
├── .github/
│   ├── prompts/
│   │   ├── mvp/
│   │   │   ├── part1-foundation.md
│   │   │   ├── part2-auth-landing.md
│   │   │   ├── part3-shop-cart.md
│   │   │   ├── part4-checkout.md
│   │   │   ├── part5-customer-staff.md
│   │   │   ├── part6-admin.md
│   │   │   ├── part7-inventory-delivery.md
│   │   │   ├── part8-reports-settings.md
│   │   │   └── part9-deployment-checklist.md
│   │   └── product_mvp.md (index)
│   └── workflows/
├── backend/                          # Laravel 12.x
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/
│   │   │   │       └── V1/          # API Version 1
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
└── tests/                            # Tests by module
```

---

## 1.3 Database Design & Setup

### Objectives

1. Design normalized database schema
2. Create all migrations in logical order
3. Implement proper relationships and constraints
4. Create seeders for testing data
5. Document all tables and relationships

### Success Criteria

| Criteria                        | Target                |
| ------------------------------- | --------------------- |
| All migrations run successfully | 100%                  |
| Foreign key constraints valid   | 100%                  |
| Seeders execute without errors  | 100%                  |
| Indexes optimized               | All search fields     |
| Documentation complete          | ERD + Data Dictionary |

### Requirements

| Requirement ID | Description                         | User Story                                         | Expected Outcome                                                                                                                                                     | Role      |
| -------------- | ----------------------------------- | -------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------- |
| DB-001         | Create database `my_zambezimeats`   | As a developer, I need a dedicated database        | MySQL database created with UTF8MB4 charset                                                                                                                          | Developer |
| DB-002         | Create `users` table                | As a developer, I need user storage                | Users table with: id, name, email, password, role, phone, avatar, is_active, currency_preference, timestamps                                                         | Developer |
| DB-003         | Create `categories` table           | As a developer, I need product categories          | Categories table with: id, name, slug, description, image, parent_id, sort_order, is_active, timestamps                                                              | Developer |
| DB-004         | Create `products` table             | As a developer, I need product storage             | Products table with: id, category_id, name, slug, description, price_per_kg, stock_quantity, unit, image, gallery, is_featured, is_active, timestamps                | Developer |
| DB-005         | Create `product_images` table       | As a developer, I need multiple product images     | Product images table with: id, product_id, image_path, sort_order, timestamps                                                                                        | Developer |
| DB-006         | Create `addresses` table            | As a developer, I need customer addresses          | Addresses table with: id, user_id, label, street, suburb, city, state, postcode, country, is_default, timestamps                                                     | Developer |
| DB-007         | Create `delivery_zones` table       | As a developer, I need delivery zone config        | Zones table with: id, name, postcodes (JSON), is_free_delivery, min_order_amount, delivery_fee, timestamps                                                           | Developer |
| DB-008         | Create `orders` table               | As a developer, I need order storage               | Orders table with: id, user_id, order_number, status, subtotal, delivery_fee, total, currency, exchange_rate, delivery_address, notes, assigned_staff_id, timestamps | Developer |
| DB-009         | Create `order_items` table          | As a developer, I need order line items            | Order items table with: id, order_id, product_id, product_name, price_per_kg, quantity_kg, line_total, timestamps                                                    | Developer |
| DB-010         | Create `order_status_history` table | As a developer, I need order status tracking       | Status history with: id, order_id, status, notes, changed_by, timestamps                                                                                             | Developer |
| DB-011         | Create `payments` table             | As a developer, I need payment records             | Payments table with: id, order_id, payment_method, transaction_id, amount, currency, status, timestamps                                                              | Developer |
| DB-012         | Create `inventory_logs` table       | As a developer, I need stock tracking              | Inventory logs with: id, product_id, type, quantity, reason, user_id, timestamps                                                                                     | Developer |
| DB-013         | Create `delivery_proofs` table      | As a developer, I need POD storage                 | Delivery proofs with: id, order_id, signature_path, photo_path, notes, captured_by, timestamps                                                                       | Developer |
| DB-014         | Create `wishlists` table            | As a developer, I need wishlist storage            | Wishlists with: id, user_id, product_id, timestamps                                                                                                                  | Developer |
| DB-015         | Create `notifications` table        | As a developer, I need notification storage        | Notifications with: id, user_id, type, title, message, data, read_at, timestamps                                                                                     | Developer |
| DB-016         | Create `activity_logs` table        | As a developer, I need audit logging               | Activity logs with: id, user_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent, timestamps                                            | Developer |
| DB-017         | Create `settings` table             | As a developer, I need system settings             | Settings with: id, key, value, type, group, timestamps                                                                                                               | Developer |
| DB-018         | Create `currency_rates` table       | As a developer, I need exchange rate storage       | Currency rates with: id, base_currency, target_currency, rate, fetched_at, timestamps                                                                                | Developer |
| DB-019         | Create `promotions` table           | As a developer, I need promotional campaigns       | Promotions with: id, name, code, type, value, min_order, start_date, end_date, is_active, timestamps                                                                 | Developer |
| DB-020         | Create `support_tickets` table      | As a developer, I need customer support storage    | Support tickets with: id, user_id, order_id, subject, message, status, priority, timestamps                                                                          | Developer |
| DB-021         | Create `ticket_replies` table       | As a developer, I need ticket conversation storage | Ticket replies with: id, ticket_id, user_id, message, timestamps                                                                                                     | Developer |
| DB-022         | Create database seeders             | As a developer, I need test data                   | Seeders for: admin user, sample categories, sample products, delivery zones, settings                                                                                | Developer |
| DB-023         | Create database documentation       | As a developer, I need schema documentation        | ERD diagram and data dictionary in `/docs/foundation/`                                                                                                               | Developer |
| DB-024         | Optimize indexes                    | As a developer, I need fast queries                | Indexes on: email, slug, order_number, status, created_at, foreign keys                                                                                              | Developer |

### Entity Relationship Overview

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

## Part 1 Summary

| Section                 | Requirements | IDs                            |
| ----------------------- | ------------ | ------------------------------ |
| Development Environment | 10           | DEV-ENV-001 to DEV-ENV-010     |
| Project Initialization  | 15           | PROJ-INIT-001 to PROJ-INIT-015 |
| Database Design & Setup | 24           | DB-001 to DB-024               |
| **Total**               | **49**       |                                |

---

**Next:** [Part 2: Authentication & Landing Module](part2-auth-landing.md)
