# Zambezi Meats - Quick Start Prompts

> **Purpose:** Ready-to-use prompts for each development phase. Copy and paste directly.

---

## 📋 PHASE 1: Foundation

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer working on the Zambezi Meats Online Butchery Store - a high-end, gourmet e-commerce platform.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part1-foundation.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 1 - Foundation |
| **Module** | DEV-ENV + PROJ-INIT + DATABASE |
| **MVP Part** | part1-foundation.md |
| **Priority** | P0 - Critical |
| **Requirements** | DEV-ENV-001 to DB-024 (49 total) |

### Task
Set up the complete development foundation:
1. Initialize Laravel 12.x backend in `/backend`
2. Initialize Vue.js 3 + Vite frontend in `/frontend`
3. Create ALL database migrations for `my_zambezimeats`
4. Create models with relationships
5. Create factories and seeders
6. Write 100% test coverage
7. Document in `/docs/foundation/`

### Quality Standards
- 100% test coverage (not 98%)
- Follow `coding_style.json` exactly
- API versioning: `/api/v1/`
- Documentation in `/docs/foundation/`

### Technology Stack
- Backend: Laravel 12.x, PHP 8.2+, MySQL 8.0
- Frontend: Vue.js 3, Vite, Tailwind CSS, shadcn/ui, Pinia
- Auth: Laravel Sanctum (cookie-based)
- Real-time: SSE (NOT WebSockets)

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 2: Authentication & Landing

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part2-auth-landing.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 2 - Auth & Landing |
| **Module** | AUTH + LANDING |
| **MVP Part** | part2-auth-landing.md |
| **Priority** | P0 - Critical |
| **Requirements** | AUTH-001 to LAND-020 (40 total) |

### Task
Implement complete authentication and landing page:
1. Laravel Sanctum SPA authentication
2. Register/Login/Logout endpoints
3. 5-minute session timeout with auto-logout
4. Role-based middleware (Guest, Customer, Staff, Admin)
5. Password reset flow
6. Vue auth pages (login, register, forgot password)
7. Pinia auth store
8. Landing page with shop-first approach
9. Write 100% test coverage
10. Document in `/docs/auth/` and `/docs/landing/`

### Key Business Rules
- Session timeout: 5 minutes of inactivity
- Handle 419 CSRF errors gracefully
- Handle 401 unauthorized errors
- Redirect by role after login

### Quality Standards
- 100% test coverage
- Modern, premium UI/UX
- Mobile responsive
- Follow `coding_style.json`

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 3: Shop & Cart

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part3-shop-cart.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 3 - Shop & Cart |
| **Module** | SHOP + CART |
| **MVP Part** | part3-shop-cart.md |
| **Priority** | P0 - Critical |
| **Requirements** | SHOP-001 to CART-023 (51 total) |

### Task
Build the main shop and cart functionality:
1. Product catalog with grid/list view
2. Category filtering
3. Search with instant results
4. Price range filter
5. Sort functionality
6. Real-time stock display
7. Product quick-view modal
8. Product detail page
9. Multi-currency (AU$/US$)
10. Persistent shopping cart
11. Cart add/update/remove
12. Guest cart → merge on login
13. Write 100% test coverage
14. Document in `/docs/shop/` and `/docs/cart/`

### Key Business Rules
- Price per kg model
- Real-time stock to prevent overselling
- Currency conversion via ExchangeRate-API

### Quality Standards
- 100% test coverage
- Page load < 1.5s
- Search response < 500ms
- Premium, gourmet design aesthetic

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 4: Checkout

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part4-checkout.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 4 - Checkout |
| **Module** | CHECKOUT |
| **MVP Part** | part4-checkout.md |
| **Priority** | P0 - Critical |
| **Requirements** | CHK-001 to CHK-030 (30 total) |

### Task
Implement complete checkout flow:
1. Multi-step checkout (Delivery → Payment → Review → Confirm)
2. Address form with Google Places autocomplete
3. Delivery zone validation
4. Delivery fee calculation (free $100+ in zones, $0.15/km outside)
5. Stripe integration (AU$/US$)
6. PayPal integration
7. Afterpay integration (AU$ only)
8. Cash on Delivery option
9. Order review step
10. Guest checkout with account creation option
11. Stock reservation during checkout (15 min)
12. Graceful error handling (no 500s)
13. Write 100% test coverage
14. Document in `/docs/checkout/`

### Key Business Rules
- Minimum order: $100 AUD
- Free delivery: $100+ in defined zones
- Outside zones: $0.15 per km
- Stock reserved for 15 minutes

### Quality Standards
- 100% test coverage
- Payment success rate > 98%
- All errors handled gracefully
- Smooth, premium checkout UX

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 5: Customer & Staff Dashboards

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part5-customer-staff.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 5 - Customer & Staff |
| **Module** | CUSTOMER + STAFF |
| **MVP Part** | part5-customer-staff.md |
| **Priority** | P1 - High |
| **Requirements** | CUST-001 to STAFF-024 (47 total) |

### Task
Build customer and staff dashboards:

**Customer Dashboard:**
1. Dashboard overview with stats
2. Order history with filtering
3. Order detail with status timeline
4. Reorder functionality
5. Profile management
6. Address management (CRUD)
7. Wishlist page
8. Notification preferences
9. Support ticket submission

**Staff Dashboard:**
1. Order queue management
2. Order assignment
3. Delivery tracking
4. Proof of delivery (signature + photo)
5. Waste/damaged goods logging
6. Real-time alerts (SSE)

Write 100% test coverage and document in `/docs/customer/` and `/docs/staff/`

### Quality Standards
- 100% test coverage
- Dashboard load < 1 second
- Modern, intuitive dashboards
- Mobile responsive

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 6: Admin Dashboard

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part6-admin.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 6 - Admin Dashboard |
| **Module** | ADMIN |
| **MVP Part** | part6-admin.md |
| **Priority** | P0 - Critical |
| **Requirements** | ADMIN-001 to ADMIN-028 (28 total) |

### Task
Build comprehensive admin dashboard:
1. Dashboard overview with KPIs
2. Real-time order alerts (SSE)
3. Revenue charts (7/30 days)
4. Order management (CRUD + lifecycle)
5. Product management (CRUD)
6. Category management
7. Customer management
8. Staff management
9. Refund processing
10. Activity logs
11. Write 100% test coverage
12. Document in `/docs/admin/`

### Key Rules
- NO bulk operations except Activity Logs
- Single delete only for products/orders/customers
- SSE for real-time alerts (NOT WebSockets)

### Quality Standards
- 100% test coverage
- Dashboard load < 2 seconds
- Report generation < 5 seconds
- Professional admin interface

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 7: Inventory & Delivery

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part7-inventory-delivery.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 7 - Inventory & Delivery |
| **Module** | INVENTORY + DELIVERY |
| **MVP Part** | part7-inventory-delivery.md |
| **Priority** | P1 - High |
| **Requirements** | INV-001 to DEL-019 (37 total) |

### Task
Implement inventory and delivery management:

**Inventory:**
1. Stock levels dashboard
2. Stock receive/adjust forms
3. Auto-deduct on order
4. Restore on cancel
5. Low stock alerts
6. Inventory history log
7. Waste management

**Delivery:**
1. Delivery dashboard
2. Delivery zones CRUD
3. Fee calculation by zone/distance
4. Staff assignment
5. Proof of delivery view
6. Delivery map (Google Maps)

Write 100% test coverage and document in `/docs/inventory/` and `/docs/delivery/`

### Key Business Rules
- Stock accuracy: 99.9%
- Auto-deduct when status = "Preparing"
- Free delivery: $100+ in zones
- Outside zones: $0.15/km from store

### Quality Standards
- 100% test coverage
- Real-time stock sync < 1 second
- Accurate fee calculations

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 8: Reports & Settings

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer continuing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part8-reports-settings.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 8 - Reports & Settings |
| **Module** | REPORTS + SETTINGS |
| **MVP Part** | part8-reports-settings.md |
| **Priority** | P2 - Medium |
| **Requirements** | RPT-001 to SET-030 (52 total) |

### Task
Build reports and settings modules:

**Reports:**
1. Reports dashboard
2. Sales summary report
3. Revenue by period
4. Product sales report
5. Customer analytics
6. Staff performance
7. Inventory report
8. Financial summary
9. Date range selector
10. Charts (Chart.js)
11. PDF export (View + Download)

**Settings:**
1. Store information
2. Operating hours
3. Payment gateway credentials
4. Email templates
5. Currency settings
6. Delivery settings
7. Session/security settings
8. Settings change history

Write 100% test coverage and document in `/docs/reports/` and `/docs/settings/`

### Quality Standards
- 100% test coverage
- Report generation < 5 seconds
- PDF export works (View opens in tab, Download saves)
- Changes take effect immediately

Begin by reading the context files and presenting your implementation plan.
```

---

## 📋 PHASE 9: Deployment & Final

```
## 🎯 PROJECT CONTEXT

You are an Expert Full-Stack Developer completing the Zambezi Meats Online Butchery Store.

### REQUIRED: Read These Files First
- `.github/prompts/settings.yml` - Project configuration
- `.github/prompts/coding_style.json` - Coding standards
- `.github/prompts/copilot_instructions.md` - AI guidelines
- `.github/prompts/mvp/part9-deployment-checklist.md` - Current requirements

### Current Phase
| Field | Value |
|-------|-------|
| **Phase** | 9 - Deployment |
| **Module** | DEPLOYMENT + CHECKLIST |
| **MVP Part** | part9-deployment-checklist.md |
| **Priority** | P0 - Critical |
| **Requirements** | DEP-001 to CHK-032 (62 total) |

### Task
Prepare and execute production deployment:

**Deployment:**
1. VPS provisioning
2. CyberPanel + OpenLiteSpeed setup
3. SSL certificate (Let's Encrypt)
4. PHP 8.2 configuration
5. MySQL production setup
6. Laravel deployment + optimization
7. Vue.js production build
8. Queue workers (Supervisor)
9. Cron jobs for scheduler
10. Log rotation
11. Backup automation
12. Monitoring setup

**Final Checklist:**
1. Verify all modules complete
2. Run full test suite (100% pass)
3. Security audit
4. Performance benchmark
5. Payment testing (live mode)
6. Cross-browser testing
7. Mobile testing
8. Documentation verification
9. UAT sign-off
10. Go-live approval

Document in `/docs/deployment/`

### Success Criteria
- Server response < 200ms
- Page load < 2 seconds
- SSL: A+ rating
- All tests: 100% pass
- Zero critical bugs

Begin by reading the context files and presenting your deployment plan.
```

---

## 🔄 SESSION CONTINUATION

Use when starting a new chat session:

```
## 🔄 CONTINUING: Zambezi Meats Development

I'm continuing development of the Zambezi Meats Online Butchery Store.

### Read These Files First
- `.github/prompts/settings.yml`
- `.github/prompts/coding_style.json`
- `.github/prompts/copilot_instructions.md`
- `.github/prompts/mvp/[CURRENT_PART_FILE].md`

### Current State
| Field | Value |
|-------|-------|
| **Phase** | [NUMBER] |
| **Module** | [NAME] |
| **Last Completed** | [TASK] |
| **Next Task** | [TASK] |
| **Test Coverage** | [PERCENTAGE] |

### Continue With
[SPECIFIC TASK TO CONTINUE]

### Remember
- 100% test coverage required
- Documentation in /docs/{module}/
- API versioning: /api/v1/
- Follow coding_style.json strictly

Review the files and continue from where we left off.
```
