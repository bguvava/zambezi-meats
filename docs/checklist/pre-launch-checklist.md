# Zambezi Meats - Final Pre-Launch Checklist

## Overview

This document provides a comprehensive checklist for verifying all MVP requirements are complete before going live. Each section corresponds to the requirement IDs from the MVP specification (CHK-001 to CHK-032).

---

## Module Verification Checklist

### CHK-001: Development Environment Module (DEV-ENV)

| ID          | Requirement                 | Status | Verified By | Date |
| ----------- | --------------------------- | ------ | ----------- | ---- |
| DEV-ENV-001 | PHP 8.2+ installed          | ☐      |             |      |
| DEV-ENV-002 | Composer installed          | ☐      |             |      |
| DEV-ENV-003 | Node.js 18+ installed       | ☐      |             |      |
| DEV-ENV-004 | MySQL 8.0 installed         | ☐      |             |      |
| DEV-ENV-005 | Git configured              | ☐      |             |      |
| DEV-ENV-006 | VS Code with extensions     | ☐      |             |      |
| DEV-ENV-007 | XAMPP/Laragon configured    | ☐      |             |      |
| DEV-ENV-008 | Postman installed           | ☐      |             |      |
| DEV-ENV-009 | Redis installed             | ☐      |             |      |
| DEV-ENV-010 | Browser DevTools configured | ☐      |             |      |

**Sign-off:** ************\_************ Date: ******\_******

---

### CHK-002: Project Initialization Module (PROJ-INIT)

| ID            | Requirement                  | Status | Verified By | Date |
| ------------- | ---------------------------- | ------ | ----------- | ---- |
| PROJ-INIT-001 | Laravel 12.x project created | ☐      |             |      |
| PROJ-INIT-002 | Vue.js 3 frontend created    | ☐      |             |      |
| PROJ-INIT-003 | API versioning configured    | ☐      |             |      |
| PROJ-INIT-004 | Sanctum installed            | ☐      |             |      |
| PROJ-INIT-005 | CORS configured              | ☐      |             |      |
| PROJ-INIT-006 | Environment files configured | ☐      |             |      |
| PROJ-INIT-007 | Git repository initialized   | ☐      |             |      |
| PROJ-INIT-008 | Testing framework configured | ☐      |             |      |
| PROJ-INIT-009 | Code formatting configured   | ☐      |             |      |
| PROJ-INIT-010 | IDE configuration            | ☐      |             |      |
| PROJ-INIT-011 | Tailwind CSS configured      | ☐      |             |      |
| PROJ-INIT-012 | Pinia store configured       | ☐      |             |      |
| PROJ-INIT-013 | Axios configured             | ☐      |             |      |
| PROJ-INIT-014 | Vue Router configured        | ☐      |             |      |
| PROJ-INIT-015 | Vite configured              | ☐      |             |      |

**Sign-off:** ************\_************ Date: ******\_******

---

### CHK-003: Database Module (DATABASE)

| ID     | Requirement                     | Status | Verified By | Date |
| ------ | ------------------------------- | ------ | ----------- | ---- |
| DB-001 | Users table created             | ☐      |             |      |
| DB-002 | Categories table created        | ☐      |             |      |
| DB-003 | Products table created          | ☐      |             |      |
| DB-004 | Product images table created    | ☐      |             |      |
| DB-005 | Carts table created             | ☐      |             |      |
| DB-006 | Cart items table created        | ☐      |             |      |
| DB-007 | Orders table created            | ☐      |             |      |
| DB-008 | Order items table created       | ☐      |             |      |
| DB-009 | Payments table created          | ☐      |             |      |
| DB-010 | Addresses table created         | ☐      |             |      |
| DB-011 | Delivery zones table created    | ☐      |             |      |
| DB-012 | Promotions table created        | ☐      |             |      |
| DB-013 | Wishlists table created         | ☐      |             |      |
| DB-014 | Inventory logs table created    | ☐      |             |      |
| DB-015 | Waste logs table created        | ☐      |             |      |
| DB-016 | Proof of delivery table created | ☐      |             |      |
| DB-017 | Activity logs table created     | ☐      |             |      |
| DB-018 | Notifications table created     | ☐      |             |      |
| DB-019 | Settings table created          | ☐      |             |      |
| DB-020 | Exchange rates table created    | ☐      |             |      |
| DB-021 | Support tickets table created   | ☐      |             |      |
| DB-022 | Email templates table created   | ☐      |             |      |
| DB-023 | Scheduled reports table created | ☐      |             |      |
| DB-024 | All foreign keys configured     | ☐      |             |      |

**Sign-off:** ************\_************ Date: ******\_******

---

### CHK-004: Authentication Module (AUTH)

| ID       | Requirement               | Status | Tests Pass | Date |
| -------- | ------------------------- | ------ | ---------- | ---- |
| AUTH-001 | Registration endpoint     | ☐      | ☐          |      |
| AUTH-002 | Login endpoint            | ☐      | ☐          |      |
| AUTH-003 | Logout endpoint           | ☐      | ☐          |      |
| AUTH-004 | Role middleware           | ☐      | ☐          |      |
| AUTH-005 | Session refresh           | ☐      | ☐          |      |
| AUTH-006 | Password reset request    | ☐      | ☐          |      |
| AUTH-007 | Password reset confirm    | ☐      | ☐          |      |
| AUTH-008 | Email verification        | ☐      | ☐          |      |
| AUTH-009 | Get user endpoint         | ☐      | ☐          |      |
| AUTH-010 | 5-minute session timeout  | ☐      | ☐          |      |
| AUTH-011 | CSRF protection           | ☐      | ☐          |      |
| AUTH-012 | Rate limiting             | ☐      | ☐          |      |
| AUTH-013 | Password hashing (bcrypt) | ☐      | ☐          |      |
| AUTH-014 | Secure session cookies    | ☐      | ☐          |      |
| AUTH-015 | SPA authentication flow   | ☐      | ☐          |      |

**Test Coverage:** **_% | **Tests Passing:** _**/\_\_\_

**Sign-off:** ************\_************ Date: ******\_******

---

### CHK-005 to CHK-015: Feature Modules

For brevity, verify each module has:

| Module    | CHK ID  | Requirements Count | Tests Passing | Coverage |
| --------- | ------- | ------------------ | ------------- | -------- |
| LANDING   | CHK-005 | 20                 | **_/_**       | \_\_\_%  |
| SHOP      | CHK-006 | 28                 | **_/_**       | \_\_\_%  |
| CART      | CHK-007 | 23                 | **_/_**       | \_\_\_%  |
| CHECKOUT  | CHK-008 | 30                 | **_/_**       | \_\_\_%  |
| CUSTOMER  | CHK-009 | 23                 | **_/_**       | \_\_\_%  |
| STAFF     | CHK-010 | 24                 | **_/_**       | \_\_\_%  |
| ADMIN     | CHK-011 | 28                 | **_/_**       | \_\_\_%  |
| INVENTORY | CHK-012 | 18                 | **_/_**       | \_\_\_%  |
| DELIVERY  | CHK-013 | 19                 | **_/_**       | \_\_\_%  |
| REPORTS   | CHK-014 | 22                 | **_/_**       | \_\_\_%  |
| SETTINGS  | CHK-015 | 30                 | **_/_**       | \_\_\_%  |

---

### CHK-016: Deployment Module (DEPLOYMENT)

| ID      | Requirement                 | Status | Verified By | Date |
| ------- | --------------------------- | ------ | ----------- | ---- |
| DEP-001 | VPS provisioned             | ☐      |             |      |
| DEP-002 | CyberPanel installed        | ☐      |             |      |
| DEP-003 | DNS configured              | ☐      |             |      |
| DEP-004 | SSL certificate installed   | ☐      |             |      |
| DEP-005 | PHP 8.2 configured          | ☐      |             |      |
| DEP-006 | MySQL database created      | ☐      |             |      |
| DEP-007 | Git access configured       | ☐      |             |      |
| DEP-008 | Directory structure created | ☐      |             |      |
| DEP-009 | Environment variables set   | ☐      |             |      |
| DEP-010 | Laravel deployed            | ☐      |             |      |
| DEP-011 | Laravel optimizations       | ☐      |             |      |
| DEP-012 | Queue workers running       | ☐      |             |      |
| DEP-013 | Scheduler configured        | ☐      |             |      |
| DEP-014 | Frontend built              | ☐      |             |      |
| DEP-015 | Frontend deployed           | ☐      |             |      |
| DEP-016 | vHost configured            | ☐      |             |      |
| DEP-017 | Migrations run              | ☐      |             |      |
| DEP-018 | Seeders run                 | ☐      |             |      |
| DEP-019 | Payment webhooks configured | ☐      |             |      |
| DEP-020 | Log rotation configured     | ☐      |             |      |
| DEP-021 | Error monitoring (Sentry)   | ☐      |             |      |
| DEP-022 | Uptime monitoring           | ☐      |             |      |
| DEP-023 | Database backups            | ☐      |             |      |
| DEP-024 | File backups                | ☐      |             |      |
| DEP-025 | Deployment script           | ☐      |             |      |
| DEP-026 | Rollback procedure          | ☐      |             |      |
| DEP-027 | Firewall configured         | ☐      |             |      |
| DEP-028 | Fail2ban configured         | ☐      |             |      |
| DEP-029 | Smoke tests passing         | ☐      |             |      |
| DEP-030 | Documentation complete      | ☐      |             |      |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-017: Full Test Suite

```bash
php artisan test
```

| Test Suite | Tests      | Passing    | Coverage     |
| ---------- | ---------- | ---------- | ------------ |
| Unit       | \_\_\_     | ☐ 100%     | \_\_\_%      |
| Feature    | \_\_\_     | ☐ 100%     | \_\_\_%      |
| Production | \_\_\_     | ☐ 100%     | \_\_\_%      |
| **TOTAL**  | **\_\_\_** | **☐ 100%** | **\_\_\_% ** |

**Command Output:**

```
Tests: ___ passed (___ assertions)
Duration: ___s
```

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-018: Security Audit

### OWASP Top 10 Verification

| Vulnerability                   | Status   | Notes                               |
| ------------------------------- | -------- | ----------------------------------- |
| A01 - Broken Access Control     | ☐ Secure | Role middleware on all routes       |
| A02 - Cryptographic Failures    | ☐ Secure | HTTPS, bcrypt passwords             |
| A03 - Injection                 | ☐ Secure | Eloquent ORM, parameterized queries |
| A04 - Insecure Design           | ☐ Secure | Rate limiting, input validation     |
| A05 - Security Misconfiguration | ☐ Secure | Debug off, secure headers           |
| A06 - Vulnerable Components     | ☐ Secure | Dependencies updated                |
| A07 - Auth Failures             | ☐ Secure | Sanctum, session timeout            |
| A08 - Data Integrity            | ☐ Secure | CSRF tokens, signed routes          |
| A09 - Logging Failures          | ☐ Secure | Activity logs, Sentry               |
| A10 - SSRF                      | ☐ Secure | No user-controlled URLs             |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-019: SSL Configuration

```bash
curl -vI https://zambezimeats.com.au 2>&1 | grep SSL
```

| Check              | Status         |
| ------------------ | -------------- |
| SSL Labs Grade     | ☐ A+           |
| HTTPS Enforced     | ☐              |
| HSTS Header        | ☐              |
| Secure Cookies     | ☐              |
| Certificate Valid  | ☐              |
| Certificate Expiry | ****\_\_\_**** |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-020 to CHK-022: Payment Testing

### Stripe (CHK-020)

| Test                    | Status | Transaction ID |
| ----------------------- | ------ | -------------- |
| Test payment successful | ☐      |                |
| Test payment declined   | ☐      |                |
| Webhook received        | ☐      |                |
| Refund successful       | ☐      |                |

### PayPal (CHK-021)

| Test                    | Status | Transaction ID |
| ----------------------- | ------ | -------------- |
| Test payment successful | ☐      |                |
| Test payment cancelled  | ☐      |                |
| Webhook received        | ☐      |                |
| Refund successful       | ☐      |                |

### Afterpay (CHK-022)

| Test                    | Status | Transaction ID |
| ----------------------- | ------ | -------------- |
| Test payment successful | ☐      |                |
| Installments displayed  | ☐      |                |
| Webhook received        | ☐      |                |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-023: Email Delivery

| Email Type         | Sent | Received | Rendered |
| ------------------ | ---- | -------- | -------- |
| Welcome email      | ☐    | ☐        | ☐        |
| Order confirmation | ☐    | ☐        | ☐        |
| Order shipped      | ☐    | ☐        | ☐        |
| Password reset     | ☐    | ☐        | ☐        |

**Email Provider:** ******\_******
**SPF Record:** ☐ | **DKIM:** ☐ | **DMARC:** ☐

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-024: Performance Benchmarks

### API Response Times

```bash
ab -n 100 -c 10 https://zambezimeats.com.au/api/v1/products
```

| Endpoint              | Target  | Actual   | Status |
| --------------------- | ------- | -------- | ------ |
| /api/v1/health        | < 50ms  | \_\_\_ms | ☐      |
| /api/v1/products      | < 200ms | \_\_\_ms | ☐      |
| /api/v1/products/{id} | < 100ms | \_\_\_ms | ☐      |
| /api/v1/categories    | < 100ms | \_\_\_ms | ☐      |

### Page Load Times

| Page         | Target | Actual  | Status |
| ------------ | ------ | ------- | ------ |
| Homepage     | < 2s   | \_\_\_s | ☐      |
| Shop page    | < 2s   | \_\_\_s | ☐      |
| Product page | < 2s   | \_\_\_s | ☐      |
| Checkout     | < 2s   | \_\_\_s | ☐      |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-025: Mobile Responsiveness

| Device      | Page     | Status | Notes |
| ----------- | -------- | ------ | ----- |
| iPhone 14   | Homepage | ☐      |       |
| iPhone 14   | Shop     | ☐      |       |
| iPhone 14   | Checkout | ☐      |       |
| Samsung S23 | Homepage | ☐      |       |
| Samsung S23 | Shop     | ☐      |       |
| Samsung S23 | Checkout | ☐      |       |
| iPad        | Homepage | ☐      |       |
| iPad        | Shop     | ☐      |       |
| iPad        | Checkout | ☐      |       |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-026: Cross-Browser Testing

| Browser | Version | Homepage | Shop | Checkout | Admin |
| ------- | ------- | -------- | ---- | -------- | ----- |
| Chrome  | Latest  | ☐        | ☐    | ☐        | ☐     |
| Firefox | Latest  | ☐        | ☐    | ☐        | ☐     |
| Safari  | Latest  | ☐        | ☐    | ☐        | ☐     |
| Edge    | Latest  | ☐        | ☐    | ☐        | ☐     |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-027: Accessibility Audit

WCAG 2.1 Level AA Compliance:

| Criterion                 | Status | Notes                    |
| ------------------------- | ------ | ------------------------ |
| 1.1.1 Non-text Content    | ☐      | Alt text on images       |
| 1.4.3 Contrast Ratio      | ☐      | 4.5:1 minimum            |
| 2.1.1 Keyboard Navigation | ☐      | All interactive elements |
| 2.4.1 Skip Links          | ☐      | Skip to main content     |
| 3.1.1 Language            | ☐      | lang="en" on html        |
| 4.1.2 ARIA                | ☐      | Proper roles/labels      |

**Tool Used:** ******\_****** (e.g., axe, WAVE)
**Issues Found:** **_ | **Issues Resolved:** _**

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-028: SEO Verification

| Item              | Status | Notes                   |
| ----------------- | ------ | ----------------------- |
| Meta titles       | ☐      | Unique per page         |
| Meta descriptions | ☐      | < 160 chars             |
| Open Graph tags   | ☐      | For social sharing      |
| Canonical URLs    | ☐      | Prevent duplicates      |
| Sitemap.xml       | ☐      | Generated and submitted |
| Robots.txt        | ☐      | Configured correctly    |
| Structured data   | ☐      | Product schema          |
| 404 page          | ☐      | Custom, helpful         |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-029: Backup Verification

| Test                       | Status | Date |
| -------------------------- | ------ | ---- |
| Database backup created    | ☐      |      |
| Database backup verified   | ☐      |      |
| Database restore tested    | ☐      |      |
| File backup created        | ☐      |      |
| File backup verified       | ☐      |      |
| File restore tested        | ☐      |      |
| Automated backup scheduled | ☐      |      |
| Off-site copy created      | ☐      |      |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-030: Documentation Complete

| Document          | Location                          | Status |
| ----------------- | --------------------------------- | ------ |
| Deployment README | /docs/deployment/README.md        | ☐      |
| API Endpoints     | /docs/deployment/api-endpoints.md | ☐      |
| User Guide        | /docs/user-guide.md               | ☐      |
| Admin Guide       | /docs/admin-guide.md              | ☐      |
| Troubleshooting   | /docs/troubleshooting.md          | ☐      |
| Security Guide    | /docs/security.md                 | ☐      |

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-031: User Acceptance Testing

| Scenario               | Tester | Status | Notes |
| ---------------------- | ------ | ------ | ----- |
| Browse products        |        | ☐      |       |
| Add to cart            |        | ☐      |       |
| Complete checkout      |        | ☐      |       |
| Track order            |        | ☐      |       |
| Manage profile         |        | ☐      |       |
| Staff order processing |        | ☐      |       |
| Admin dashboard        |        | ☐      |       |
| Reports generation     |        | ☐      |       |

**UAT Completed:** ☐
**Issues Found:** **_
**Issues Resolved:** _**

**Sign-off:** ************\_************ Date: ******\_******

---

## CHK-032: Go-Live Approval

### Final Sign-Off

| Role             | Name | Signature | Date |
| ---------------- | ---- | --------- | ---- |
| Developer        |      |           |      |
| QA Lead          |      |           |      |
| Security Officer |      |           |      |
| Business Owner   |      |           |      |
| Project Manager  |      |           |      |

### Go-Live Decision

☐ **APPROVED** - Proceed with go-live

☐ **CONDITIONAL** - Proceed with noted issues to address post-launch

☐ **NOT APPROVED** - Address issues before re-evaluation

**Notes:**

---

---

---

### Go-Live Date & Time

**Planned Go-Live:** **********\_\_**********

**Actual Go-Live:** **********\_\_**********

---

## Post-Launch Monitoring (First 24 Hours)

| Check                   | Time | Status | Notes |
| ----------------------- | ---- | ------ | ----- |
| Health endpoint         | +0h  | ☐      |       |
| Health endpoint         | +1h  | ☐      |       |
| Health endpoint         | +4h  | ☐      |       |
| Health endpoint         | +8h  | ☐      |       |
| Health endpoint         | +24h | ☐      |       |
| Error rate (Sentry)     | +24h | ☐      |       |
| Response times          | +24h | ☐      |       |
| First order placed      |      | ☐      |       |
| First payment processed |      | ☐      |       |

---

## Document Information

| Field   | Value             |
| ------- | ----------------- |
| Version | 1.0               |
| Created | December 2024     |
| Author  | bguvava           |
| Project | Zambezi Meats MVP |
