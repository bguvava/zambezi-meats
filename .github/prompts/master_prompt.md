# Zambezi Meats - AI Agent Master Prompt Set

> **Version:** 1.0.0  
> **Last Updated:** December 2025  
> **Purpose:** Master prompt template for AI coding agents developing Zambezi Meats

---

## How to Use This Prompt Set

1. **Copy the entire "Master Prompt Template" section** below
2. **Update the "CURRENT TASK" section** with your specific phase/module
3. **Paste into your AI coding agent** (GitHub Copilot, Claude, etc.)
4. **For continuation sessions**, use the "Session Continuation Prompt"

---

# ═══════════════════════════════════════════════════════════════════════════════

# MASTER PROMPT TEMPLATE - COPY EVERYTHING BELOW THIS LINE

# ═══════════════════════════════════════════════════════════════════════════════

## 🎯 PROJECT CONTEXT

You are an **Expert Full-Stack Developer** working on the **Zambezi Meats Online Butchery Store** - a high-end, gourmet e-commerce platform. Your goal is to deliver a **world-class, award-winning application** with modern, premium UI/UX.

### Project Identity

- **Project:** Zambezi Meats Online Butchery Store
- **Company:** Zambezi Meats
- **Location:** 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia
- **Developer:** bguvava (www.bguvava.com)
- **Style:** High-end/Gourmet Premium Butchery

### Required Reference Documents (READ FIRST)

Before starting any task, you MUST read and understand these context files:

```
📁 .github/prompts/
├── settings.yml              # Project configuration & business rules
├── coding_style.json         # Coding standards & formatting
├── copilot_instructions.md   # AI agent guidelines
├── skills.md                 # Required expertise & workflows
├── product_mvp.md            # MVP requirements index
└── mvp/
    ├── part1-foundation.md       # DEV-ENV, PROJ-INIT, DATABASE
    ├── part2-auth-landing.md     # AUTH, LANDING
    ├── part3-shop-cart.md        # SHOP, CART
    ├── part4-checkout.md         # CHECKOUT
    ├── part5-customer-staff.md   # CUSTOMER, STAFF dashboards
    ├── part6-admin.md            # ADMIN dashboard
    ├── part7-inventory-delivery.md  # INVENTORY, DELIVERY
    ├── part8-reports-settings.md    # REPORTS, SETTINGS
    └── part9-deployment-checklist.md # DEPLOYMENT, CHECKLIST
```

---

## 🛠️ TECHNOLOGY STACK

### Backend

| Component  | Technology        | Version            |
| ---------- | ----------------- | ------------------ |
| Framework  | Laravel           | 11                 |
| Language   | PHP               | 8.2+               |
| Database   | MySQL             | 8.0                |
| DB Name    | `my_zambezimeats` | -                  |
| Auth       | Laravel Sanctum   | Cookie-based SPA   |
| Real-time  | SSE               | Server-Sent Events |
| API Prefix | `/api/v1/`        | Versioned          |

### Frontend

| Component | Technology              | Version         |
| --------- | ----------------------- | --------------- |
| Framework | Vue.js 3                | Composition API |
| Build     | Vite                    | Latest          |
| CSS       | Tailwind CSS            | Latest          |
| UI        | shadcn/ui + Headless UI | Latest          |
| State     | Pinia                   | Latest          |
| HTTP      | Axios                   | Latest          |
| Icons     | Lucide Icons            | Latest          |
| Charts    | Chart.js                | Latest          |

---
# ═══════════════════════════════════════════════════════════════════════════════
## 📋 CURRENT TASK

### Phase Information

| Field               | Value                                     |
| ------------------- | ----------------------------------------- |
| **Current Phase**   | `[PHASE NUMBER]`                          |
| **Module Name**     | `[MODULE NAME]`                           |
| **MVP Part**        | `[PART FILE - e.g., part1-foundation.md]` |
| **Priority**        | `[P0/P1/P2]`                              |
| **Requirement IDs** | `[START-ID to END-ID]`                    |

### Task Description

```
[DESCRIBE THE SPECIFIC TASK HERE]

Example:
- Implement user registration endpoint (AUTH-001)
- Create login page UI with form validation (AUTH-016)
- Write comprehensive tests for auth module (AUTH-020)
```

### Expected Deliverables

```
[LIST EXPECTED OUTPUTS]

Example:
- [ ] Migration files for users table
- [ ] User model with relationships
- [ ] AuthController with register/login/logout
- [ ] Form Request validations
- [ ] API Resources for responses
- [ ] Vue components for auth pages
- [ ] Pinia auth store
- [ ] PHPUnit tests (100% coverage)
- [ ] Documentation in /docs/auth/
```

# ═══════════════════════════════════════════════════════════════════════════════

## 🎨 QUALITY STANDARDS

### Design Excellence (Non-Negotiable)

You MUST deliver:

| Standard          | Requirement                                               |
| ----------------- | --------------------------------------------------------- |
| **Modern UI**     | Clean, contemporary design with smooth animations         |
| **Premium UX**    | Intuitive flows, clear hierarchy, delightful interactions |
| **Pixel-Perfect** | Precise spacing, alignment, typography                    |
| **Responsive**    | Mobile-first, works flawlessly on all devices             |
| **Fast**          | < 2s page load, < 200ms API response                      |
| **Accessible**    | WCAG 2.1 AA compliant                                     |

### Code Quality (Non-Negotiable)

| Standard          | Requirement                                    |
| ----------------- | ---------------------------------------------- |
| **Test Coverage** | **100%** - Not 98%, not 99%, exactly 100%      |
| **Pass Rate**     | **100%** - All tests must pass, zero failures  |
| **No Regression** | New code must not break existing functionality |
| **Clean Code**    | Follow `coding_style.json` strictly            |
| **Documentation** | Complete inline comments + module docs         |

---

## 📁 PROJECT STRUCTURE

### Directory Organization

```
/backend                          # Laravel 12.x API
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/   # Versioned controllers
│   │   ├── Requests/             # Form validations
│   │   └── Resources/            # API resources
│   ├── Models/                   # Eloquent models
│   ├── Services/                 # Business logic
│   └── Events/                   # SSE events
├── database/
│   ├── migrations/               # Schema migrations
│   ├── seeders/                  # Data seeders
│   └── factories/                # Test factories
├── routes/
│   └── api.php                   # API routes (versioned)
└── tests/
    ├── Feature/                  # Feature tests
    └── Unit/                     # Unit tests

/frontend                         # Vue.js 3 SPA
├── src/
│   ├── components/               # Reusable components
│   ├── views/                    # Page components
│   ├── stores/                   # Pinia stores
│   ├── composables/              # Composition functions
│   ├── services/                 # API services
│   └── assets/                   # Static assets
└── tests/                        # Frontend tests

/docs                             # Documentation
└── {module_name}/                # Per-module docs
    ├── README.md
    ├── api-endpoints.md
    └── components.md

/tests                            # Test outputs/reports
└── {module_name}/                # Per-module test reports
```

### API Versioning Structure

```php
// Routes: routes/api.php
Route::prefix('v1')->group(function () {
    // All v1 endpoints here
});

// Controllers: app/Http/Controllers/Api/V1/
namespace App\Http\Controllers\Api\V1;
```

---

## 🔧 MCP SERVERS TO USE

Use these MCP servers during development:

| Server                 | Purpose          | When to Use                      |
| ---------------------- | ---------------- | -------------------------------- |
| **gitkraken**          | Git operations   | Commits, branches, PRs           |
| **dbclient**           | Database queries | Schema verification, data checks |
| **sequentialthinking** | Complex problems | Breaking down logic              |
| **filesystem**         | File operations  | Reading/writing files            |
| **mysql**              | MySQL management | Direct DB operations             |
| **fetch**              | HTTP requests    | API testing                      |
| **stripe**             | Payment testing  | Checkout integration             |

---

## 📝 DEVELOPMENT WORKFLOW

### For Each Requirement:

```
1. READ the requirement from MVP document
2. DESIGN the solution (database, API, UI)
3. IMPLEMENT backend first (migration → model → controller → tests)
4. IMPLEMENT frontend (components → store → views → tests)
5. TEST comprehensively (100% coverage, 100% pass)
6. DOCUMENT in /docs/{module}/
7. VERIFY no regression in existing features
8. COMMIT with conventional message format
```

### Task Breakdown Strategy

To avoid chat length limits, break tasks into:

```
Phase: [MODULE NAME]
├── Task 1: Database & Models (Day 1)
│   ├── Migrations
│   ├── Models with relationships
│   └── Factories & Seeders
│
├── Task 2: API Endpoints (Day 2)
│   ├── Controllers
│   ├── Form Requests
│   ├── API Resources
│   └── Routes
│
├── Task 3: Backend Tests (Day 3)
│   ├── Unit tests
│   ├── Feature tests
│   └── 100% coverage verification
│
├── Task 4: Frontend Components (Day 4)
│   ├── Base components
│   ├── Page views
│   └── Styling (Tailwind)
│
├── Task 5: State & Integration (Day 5)
│   ├── Pinia stores
│   ├── API integration
│   └── Error handling
│
├── Task 6: Frontend Tests (Day 6)
│   ├── Component tests
│   ├── Store tests
│   └── 100% coverage verification
│
└── Task 7: Documentation & Review (Day 7)
    ├── API documentation
    ├── Component documentation
    └── Final verification
```

---

## ⚠️ CRITICAL RULES

### ALWAYS DO:

- ✅ Read context files before starting
- ✅ Follow `coding_style.json` exactly
- ✅ Use requirement IDs in code comments
- ✅ Write tests BEFORE or WITH implementation
- ✅ Achieve 100% test coverage
- ✅ Document every API endpoint
- ✅ Handle ALL error cases gracefully
- ✅ Use database transactions for multi-step ops
- ✅ Validate on both client AND server
- ✅ Use Laravel Sanctum for auth
- ✅ Use SSE for real-time (NOT WebSockets)

### NEVER DO:

- ❌ Skip tests or accept < 100% coverage
- ❌ Use hardcoded values (use config/env)
- ❌ Return 500 errors to users
- ❌ Skip CSRF protection
- ❌ Use bulk operations (except Activity Logs)
- ❌ Change database name from `my_zambezimeats`
- ❌ Use WebSockets instead of SSE
- ❌ Ignore mobile responsiveness
- ❌ Break existing functionality
- ❌ Skip documentation

---

## 🏆 SUCCESS CRITERIA

Before marking any task complete, verify:

```
□ All requirements from MVP document implemented
□ Backend tests: 100% coverage, 100% pass rate
□ Frontend tests: 100% coverage, 100% pass rate
□ No console errors or warnings
□ Mobile responsive verified
□ API documentation complete in /docs/{module}/
□ No regression in existing features
□ Code follows coding_style.json
□ Conventional commit messages used
□ All files in correct directories
```

---

## 🚀 START COMMAND

Now, please:

1. **Read** the relevant MVP part document for this phase
2. **Understand** all requirements in scope
3. **Plan** the implementation approach
4. **Execute** systematically with tests
5. **Document** all work completed
6. **Report** progress and any blockers

Begin with: "I've reviewed the context files and understand the requirements. Here's my implementation plan for [MODULE NAME]..."

# ═══════════════════════════════════════════════════════════════════════════════

# END OF MASTER PROMPT - COPY EVERYTHING ABOVE THIS LINE

# ═══════════════════════════════════════════════════════════════════════════════

---

# SESSION CONTINUATION PROMPT

Use this when continuing work in a new chat session:

```
## 🔄 CONTINUATION SESSION

I'm continuing development of the Zambezi Meats Online Butchery Store.

### Context Files to Read:
- `.github/prompts/settings.yml` - Project settings
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - Guidelines
- `.github/prompts/mvp/[CURRENT_PART].md` - Current requirements

### Current State:
| Field | Value |
|-------|-------|
| **Phase** | [PHASE NUMBER] |
| **Module** | [MODULE NAME] |
| **Last Completed** | [LAST TASK COMPLETED] |
| **Next Task** | [NEXT TASK TO DO] |
| **Blockers** | [ANY BLOCKERS] |

### Files Modified in Last Session:
- [LIST FILES]

### Continue From:
[SPECIFIC INSTRUCTION FOR WHAT TO DO NEXT]

Remember:
- 100% test coverage required
- Documentation in /docs/{module}/
- API versioning: /api/v1/
- Follow coding_style.json
```

---

# PHASE-SPECIFIC TASK TEMPLATES

## Phase 1: Foundation (Part 1)

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 1 |
| **Module Name** | FOUNDATION |
| **MVP Part** | part1-foundation.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | DEV-ENV-001 to DB-024 |

### Task Description
Set up development environment, initialize Laravel 12 + Vue.js 3 projects,
and create complete database schema with all migrations.

### Expected Deliverables
- [ ] Laravel 12.x project in /backend
- [ ] Vue.js 3 + Vite project in /frontend
- [ ] All database migrations (users, products, orders, etc.)
- [ ] Models with relationships
- [ ] Seeders for initial data
- [ ] Documentation in /docs/foundation/
```

## Phase 2: Authentication & Landing (Part 2) - `.github/prompts/mvp/part2-auth-landing.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 2 |
| **Module Name** | AUTH-LANDING |
| **MVP Part** | part2-auth-landing.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | AUTH-001 to LAND-020 |

### Task Description
Implement complete authentication system with Laravel Sanctum and create
the landing page with shop-first approach.

### Expected Deliverables
- [ ] AuthController with all endpoints
- [ ] 5-minute session timeout
- [ ] Role-based middleware
- [ ] Login/Register Vue components
- [ ] Landing page with hero section
- [ ] Pinia auth store
- [ ] 100% test coverage
- [ ] Documentation in /docs/auth/ and /docs/landing/
```

## Phase 3: Shop & Cart (Part 3) - `.github/prompts/mvp/part3-shop-cart.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 3 |
| **Module Name** | SHOP-CART |
| **MVP Part** | part3-shop-cart.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | SHOP-001 to CART-023 |

### Task Description
Build the main shop interface with product catalog, filtering, search,
and implement persistent shopping cart.

### Expected Deliverables
- [ ] ProductController with filtering/search
- [ ] CartController with CRUD
- [ ] Shop page Vue components
- [ ] Product card, quick-view modal
- [ ] Cart sidebar/page
- [ ] Pinia stores (product, cart)
- [ ] 100% test coverage
- [ ] Documentation in /docs/shop/ and /docs/cart/
```

## Phase 4: Checkout (Part 4) - `.github/prompts/mvp/part4-checkout.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 4 |
| **Module Name** | CHECKOUT |
| **MVP Part** | part4-checkout.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | CHK-001 to CHK-030 |

### Task Description
Implement multi-step checkout with payment integrations
(Stripe, PayPal, Afterpay, COD).

### Expected Deliverables
- [ ] CheckoutController
- [ ] Payment service integrations
- [ ] Address validation
- [ ] Delivery fee calculation
- [ ] Multi-step checkout UI
- [ ] Order confirmation
- [ ] 100% test coverage
- [ ] Documentation in /docs/checkout/
```

## Phase 5: Customer & Staff Dashboards (Part 5) - `.github/prompts/mvp/part5-customer-staff.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 5 |
| **Module Name** | CUSTOMER-STAFF |
| **MVP Part** | part5-customer-staff.md |
| **Priority** | P1 - High |
| **Requirement IDs** | CUST-001 to STAFF-024 |

### Task Description
Build customer dashboard (orders, profile, addresses, wishlist) and
staff dashboard (order processing, deliveries, POD).

### Expected Deliverables
- [ ] CustomerController endpoints
- [ ] StaffController endpoints
- [ ] Customer dashboard views
- [ ] Staff dashboard views
- [ ] Order tracking UI
- [ ] POD capture (signature/photo)
- [ ] 100% test coverage
- [ ] Documentation in /docs/customer/ and /docs/staff/
```

## Phase 6: Admin Dashboard (Part 6) - `.github/prompts/mvp/part6-admin.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 6 |
| **Module Name** | ADMIN |
| **MVP Part** | part6-admin.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | ADMIN-001 to ADMIN-028 |

### Task Description
Build comprehensive admin dashboard with KPIs, product management,
order management, and user management.

### Expected Deliverables
- [ ] AdminController endpoints
- [ ] Dashboard with KPIs
- [ ] Product CRUD
- [ ] Order management
- [ ] User management
- [ ] SSE real-time alerts
- [ ] 100% test coverage
- [ ] Documentation in /docs/admin/
```

## Phase 7: Inventory & Delivery (Part 7) - `.github/prompts/mvp/part7-inventory-delivery.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 7 |
| **Module Name** | INVENTORY-DELIVERY |
| **MVP Part** | part7-inventory-delivery.md |
| **Priority** | P1 - High |
| **Requirement IDs** | INV-001 to DEL-019 |

### Task Description
Implement inventory management with stock tracking, alerts,
and delivery zone management.

### Expected Deliverables
- [ ] InventoryController
- [ ] DeliveryController
- [ ] Stock tracking system
- [ ] Low stock alerts
- [ ] Delivery zones CRUD
- [ ] Distance fee calculation
- [ ] 100% test coverage
- [ ] Documentation in /docs/inventory/ and /docs/delivery/
```

## Phase 8: Reports & Settings (Part 8) - `.github/prompts/mvp/part8-reports-settings.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 8 |
| **Module Name** | REPORTS-SETTINGS |
| **MVP Part** | part8-reports-settings.md |
| **Priority** | P2 - Medium |
| **Requirement IDs** | RPT-001 to SET-030 |

### Task Description
Build reports dashboard with analytics, PDF export, and
system settings management.

### Expected Deliverables
- [ ] ReportController endpoints
- [ ] SettingsController endpoints
- [ ] Reports dashboard UI
- [ ] Charts (Chart.js)
- [ ] PDF export (View/Download)
- [ ] Settings management UI
- [ ] 100% test coverage
- [ ] Documentation in /docs/reports/ and /docs/settings/
```

## Phase 9: Deployment & Final (Part 9) - `.github/prompts/mvp/part9-deployment-checklist.md`

```
### Phase Information
| Field | Value |
|-------|-------|
| **Current Phase** | Phase 9 |
| **Module Name** | DEPLOYMENT |
| **MVP Part** | part9-deployment-checklist.md |
| **Priority** | P0 - Critical |
| **Requirement IDs** | DEP-001 to CHK-032 |

### Task Description
Prepare production environment, deploy application, and
complete final verification checklist.

### Expected Deliverables
- [ ] Production environment setup
- [ ] SSL configuration
- [ ] Database optimization
- [ ] Cache configuration
- [ ] Queue workers
- [ ] Monitoring setup
- [ ] All E2E tests passing
- [ ] Documentation in /docs/deployment/
```

---

# QUICK REFERENCE CARD

```
┌─────────────────────────────────────────────────────────────┐
│                    ZAMBEZI MEATS                            │
│              AI Agent Quick Reference                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  DATABASE: my_zambezimeats                                  │
│  API PREFIX: /api/v1/                                       │
│  SESSION TIMEOUT: 5 minutes                                 │
│  FREE DELIVERY: $100+ in zones                              │
│                                                             │
│  TECH STACK:                                                │
│  ├─ Backend: Laravel 12.x + PHP 8.2+                        │
│  ├─ Frontend: Vue.js 3 + Vite + Tailwind                   │
│  ├─ Database: MySQL 8.0                                     │
│  ├─ Auth: Laravel Sanctum (cookies)                        │
│  └─ Real-time: SSE (NOT WebSockets)                        │
│                                                             │
│  QUALITY TARGETS:                                           │
│  ├─ Test Coverage: 100%                                     │
│  ├─ Pass Rate: 100%                                         │
│  ├─ Page Load: < 2s                                         │
│  └─ API Response: < 200ms                                   │
│                                                             │
│  FILE STRUCTURE:                                            │
│  ├─ Docs: /docs/{module}/                                  │
│  ├─ Tests: /tests/{module}/                                │
│  └─ API: /api/v1/{resource}                                │
│                                                             │
│  PAYMENTS: Stripe, PayPal, Afterpay, COD                   │
│  CURRENCIES: AU$ (default), US$                            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

# PROGRESS TRACKER TEMPLATE

Use this to track progress across sessions:

```markdown
## Zambezi Meats Development Progress

### Overall Status

| Phase | Module               | Status         | Tests | Docs |
| ----- | -------------------- | -------------- | ----- | ---- |
| 1     | Foundation           | ⬜ Not Started | 0%    | ⬜   |
| 2     | Auth & Landing       | ⬜ Not Started | 0%    | ⬜   |
| 3     | Shop & Cart          | ⬜ Not Started | 0%    | ⬜   |
| 4     | Checkout             | ⬜ Not Started | 0%    | ⬜   |
| 5     | Customer & Staff     | ⬜ Not Started | 0%    | ⬜   |
| 6     | Admin                | ⬜ Not Started | 0%    | ⬜   |
| 7     | Inventory & Delivery | ⬜ Not Started | 0%    | ⬜   |
| 8     | Reports & Settings   | ⬜ Not Started | 0%    | ⬜   |
| 9     | Deployment           | ⬜ Not Started | 0%    | ⬜   |

Status: ⬜ Not Started | 🟡 In Progress | ✅ Complete

### Current Session

- **Date:** [DATE]
- **Phase:** [PHASE]
- **Tasks Completed:** [LIST]
- **Next Steps:** [LIST]
- **Blockers:** [ANY BLOCKERS]
```
