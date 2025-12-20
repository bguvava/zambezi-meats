# Zambezi Meats - AI Agent Skills & Expertise Guide

> **Purpose:** This document defines the specialized skills, domain expertise, and workflows required to develop the Zambezi Meats online butchery store from start to finish without losing context.

---

## 1. Core Technical Skills

### 1.1 Backend Development (Laravel 12.x)

#### Required Expertise

| Skill                     | Proficiency | Application                                            |
| ------------------------- | ----------- | ------------------------------------------------------ |
| Laravel 12.x Architecture | Expert      | MVC structure, service providers, facades              |
| PHP 8.2+ Features         | Expert      | Typed properties, enums, attributes, match expressions |
| Eloquent ORM              | Expert      | Relationships, scopes, accessors, mutators, observers  |
| Laravel Sanctum           | Expert      | SPA authentication, cookie-based sessions, CSRF        |
| API Development           | Expert      | RESTful design, versioning, resources, pagination      |
| Database Migrations       | Expert      | Schema design, foreign keys, indexes, rollbacks        |
| Form Requests             | Expert      | Validation rules, authorization, custom messages       |
| Queue System              | Advanced    | Job dispatching, workers, failed jobs, retries         |
| Event System              | Advanced    | Events, listeners, broadcasting                        |
| Testing (PHPUnit)         | Expert      | Feature tests, unit tests, mocking, factories          |

#### Key Workflows

```
1. Creating New Feature:
   ├── Create migration (if new table)
   ├── Create/update Model with relationships
   ├── Create Form Request for validation
   ├── Create Controller with actions
   ├── Create API Resource for responses
   ├── Define routes in api.php
   ├── Create Service class (if complex logic)
   ├── Write tests (feature + unit)
   └── Document endpoint

2. Authentication Flow:
   ├── Sanctum configuration
   ├── CSRF token handling
   ├── Session management (5-min timeout)
   ├── Role-based middleware
   └── Login/logout/register endpoints

3. Error Handling:
   ├── Custom exception classes
   ├── Global exception handler
   ├── Logging configuration
   └── User-friendly responses
```

### 1.2 Frontend Development (Vue.js 3)

#### Required Expertise

| Skill                  | Proficiency | Application                                       |
| ---------------------- | ----------- | ------------------------------------------------- |
| Vue 3 Composition API  | Expert      | `<script setup>`, refs, reactive, computed        |
| Pinia State Management | Expert      | Stores, actions, getters, persistence             |
| Vue Router             | Expert      | Navigation guards, nested routes, lazy loading    |
| Axios HTTP             | Expert      | Interceptors, error handling, CSRF                |
| Tailwind CSS           | Expert      | Utility classes, responsive design, custom config |
| shadcn/ui              | Advanced    | Component customization, theming                  |
| Headless UI            | Advanced    | Accessible components, transitions                |
| Vite Build             | Advanced    | Configuration, optimization, environment          |
| Testing (Vitest)       | Advanced    | Unit tests, component tests, mocking              |

#### Key Workflows

```
1. Creating New Component:
   ├── Define props and emits
   ├── Set up reactive state
   ├── Implement computed properties
   ├── Create methods/handlers
   ├── Add template with Tailwind
   ├── Write component tests
   └── Export and register

2. State Management:
   ├── Create Pinia store
   ├── Define state (refs/reactive)
   ├── Define getters (computed)
   ├── Define actions (async functions)
   └── Persist if needed (cart, auth)

3. API Integration:
   ├── Create API service module
   ├── Configure Axios instance
   ├── Handle CSRF tokens
   ├── Implement error handling
   └── Add loading states
```

### 1.3 Database Design (MySQL 8.0)

#### Required Expertise

| Skill              | Proficiency | Application                               |
| ------------------ | ----------- | ----------------------------------------- |
| Schema Design      | Expert      | Normalization, relationships, constraints |
| Query Optimization | Advanced    | Indexes, EXPLAIN, query profiling         |
| Migrations         | Expert      | Version control, rollbacks, seeders       |
| Transactions       | Expert      | ACID compliance, deadlock handling        |
| Data Types         | Expert      | Proper type selection, precision          |

#### Database Schema Knowledge

```
Key Tables:
├── users (with roles)
├── products
├── categories
├── orders
├── order_items
├── carts
├── cart_items
├── addresses
├── deliveries
├── inventory_movements
├── payments
├── settings
└── activity_logs
```

---

## 2. Domain-Specific Expertise

### 2.1 E-Commerce Domain

#### Required Knowledge

| Area                 | Details                                                       |
| -------------------- | ------------------------------------------------------------- |
| Product Catalog      | Categories, variants, pricing per weight (kg)                 |
| Shopping Cart        | Persistence, guest carts, merge on login                      |
| Checkout Flow        | Multi-step, address validation, payment selection             |
| Order Lifecycle      | Pending → Accepted → Preparing → Out for Delivery → Delivered |
| Payment Processing   | Stripe, PayPal, Afterpay integration                          |
| Inventory Management | Real-time tracking, stock alerts, auto-deduction              |
| Delivery Zones       | Postcode-based fees, distance calculation                     |

#### Business Rules Implementation

```php
// Example: Delivery Fee Calculation
public function calculateDeliveryFee(string $postcode, float $orderTotal): float
{
    // Check if in free delivery zone
    if ($this->isInFreeDeliveryZone($postcode) && $orderTotal >= 100) {
        return 0.00;
    }

    // Calculate by distance
    $distance = $this->calculateDistance($postcode);
    return $distance * 0.15; // $0.15 per km
}
```

### 2.2 Butchery/Food Industry

#### Required Knowledge

| Area                 | Details                                                 |
| -------------------- | ------------------------------------------------------- |
| Product Types        | Beef, lamb, chicken, pork, specialty cuts               |
| Pricing Model        | Price per kilogram with weight selection                |
| Inventory            | Perishable goods, expiration tracking, waste management |
| Food Safety          | Compliance features, traceability                       |
| Quality Presentation | High-end imagery, detailed descriptions                 |

### 2.3 Australian Market

#### Required Knowledge

| Area            | Details                                        |
| --------------- | ---------------------------------------------- |
| Currency        | AU$ primary, US$ secondary                     |
| Address Format  | Australian postcode system (4 digits)          |
| Payment Methods | Afterpay (AU-specific), local bank integration |
| Compliance      | Australian consumer law, privacy regulations   |
| Time Zone       | AEST/AEDT handling                             |

---

## 3. Integration Skills

### 3.1 Payment Gateways

#### Stripe Integration

```javascript
// Expertise required:
- Stripe Elements setup
- Payment Intents API
- Webhook handling
- Error handling
- Currency conversion
- Refund processing
```

#### PayPal Integration

```javascript
// Expertise required:
- PayPal JavaScript SDK
- Order creation
- Capture/authorize flows
- Webhook events
- Sandbox testing
```

#### Afterpay Integration

```javascript
// Expertise required:
- Afterpay widget
- Order limits (AU$ only)
- Installment display
- Merchant portal
```

### 3.2 External APIs

| API              | Purpose              | Skills Required                |
| ---------------- | -------------------- | ------------------------------ |
| ExchangeRate-API | Currency conversion  | REST API, caching              |
| Google Places    | Address autocomplete | Places SDK, API key management |
| Google Maps      | Delivery distance    | Distance Matrix API            |
| SMTP/Email       | Transactional emails | Laravel Mail, templates        |

### 3.3 Real-Time Features (SSE)

```php
// Server-Sent Events expertise:
- Laravel SSE implementation
- Event broadcasting
- Connection management
- Reconnection handling
- Client-side EventSource
```

---

## 4. DevOps & Deployment Skills

### 4.1 Server Administration

| Skill         | Platform      | Application                 |
| ------------- | ------------- | --------------------------- |
| Linux         | Ubuntu 22.04  | Server management           |
| CyberPanel    | VPS Control   | Website management          |
| OpenLiteSpeed | Web Server    | Configuration, optimization |
| MySQL         | Database      | Administration, backups     |
| SSL           | Let's Encrypt | Certificate management      |

### 4.2 Deployment Workflow

```
1. Pre-Deployment:
   ├── Run all tests locally
   ├── Build frontend assets
   ├── Update environment configs
   └── Create database backup

2. Deployment:
   ├── Pull latest code
   ├── Install dependencies (composer, npm)
   ├── Run migrations
   ├── Clear and rebuild caches
   ├── Restart queue workers
   └── Verify deployment

3. Post-Deployment:
   ├── Run smoke tests
   ├── Monitor error logs
   ├── Check performance metrics
   └── Verify webhooks
```

### 4.3 Monitoring & Maintenance

| Tool              | Purpose                          |
| ----------------- | -------------------------------- |
| Sentry            | Error tracking                   |
| UptimeRobot       | Availability monitoring          |
| Laravel Telescope | Debug & profiling (dev)          |
| Log Management    | Daily rotation, 30-day retention |

---

## 5. Testing Expertise

### 5.1 Backend Testing (PHPUnit)

```php
// Required test types:
class ProductTest extends TestCase
{
    // Feature Tests
    public function test_can_list_products(): void { }
    public function test_can_filter_by_category(): void { }
    public function test_requires_auth_for_admin(): void { }

    // Unit Tests
    public function test_price_calculation(): void { }
    public function test_stock_deduction(): void { }
}
```

### 5.2 Frontend Testing (Vitest)

```javascript
// Required test types:
describe("ProductCard", () => {
  it("renders product information", () => {});
  it("adds to cart on click", () => {});
  it("shows stock status correctly", () => {});
});
```

### 5.3 E2E Testing (Playwright)

```javascript
// Critical path tests:
test("complete checkout flow", async ({ page }) => {
  // Browse → Add to Cart → Checkout → Payment → Confirmation
});
```

---

## 6. Security Skills

### 6.1 Application Security

| Area             | Implementation                 |
| ---------------- | ------------------------------ |
| Authentication   | Sanctum, session management    |
| Authorization    | Policies, gates, middleware    |
| CSRF             | Token validation, SPA handling |
| XSS              | Output escaping, CSP headers   |
| SQL Injection    | Prepared statements, Eloquent  |
| Input Validation | Form Requests, sanitization    |

### 6.2 Infrastructure Security

| Area     | Implementation              |
| -------- | --------------------------- |
| HTTPS    | SSL certificates, HSTS      |
| Firewall | UFW configuration           |
| Fail2ban | Intrusion prevention        |
| Secrets  | Environment variables, .env |

---

## 7. Workflow Checklists

### 7.1 New Feature Development

```markdown
□ Review MVP requirement (e.g., SHOP-001)
□ Design database schema changes
□ Create migration
□ Create/update Model
□ Create Form Request
□ Create Controller
□ Create API Resource
□ Define routes
□ Write backend tests
□ Create Vue component
□ Integrate with store
□ Add to router
□ Write frontend tests
□ Test manually
□ Update documentation
□ Commit with conventional message
```

### 7.2 Bug Fix Process

```markdown
□ Reproduce the bug
□ Identify root cause
□ Write failing test
□ Implement fix
□ Verify test passes
□ Test related functionality
□ Update documentation if needed
□ Commit with "fix:" prefix
```

### 7.3 Code Review Checklist

```markdown
□ Follows coding standards
□ Proper error handling
□ Tests included
□ Documentation updated
□ No security vulnerabilities
□ Performance considered
□ Mobile responsive (frontend)
□ Accessible (WCAG 2.1 AA)
```

---

## 8. Context Maintenance Strategies

### 8.1 Before Starting Work

1. **Read Settings:** Check `.github/prompts/settings.yml`
2. **Review MVP:** Open relevant part from `mvp/` folder
3. **Check Style:** Reference `coding_style.json`
4. **Understand Scope:** Know what module you're in

### 8.2 During Development

1. **Follow Patterns:** Use established code patterns
2. **Stay Consistent:** Match existing code style
3. **Reference IDs:** Use requirement IDs in commits
4. **Test Continuously:** Run tests frequently

### 8.3 Avoiding Hallucination

1. **Verify Against Docs:** Always check MVP requirements
2. **Use Exact Names:** Database is `my_zambezimeats`, not variations
3. **Follow Architecture:** SSE not WebSockets, Sanctum not JWT
4. **Check Business Rules:** $100 minimum, 5-min timeout, etc.

---

## 9. Expertise Level Summary

| Category        | Skills                                    | Level    |
| --------------- | ----------------------------------------- | -------- |
| **Backend**     | Laravel 12.x, PHP 8.2+, MySQL, API Design | Expert   |
| **Frontend**    | Vue 3, Tailwind, Pinia, Vite              | Expert   |
| **Database**    | Schema Design, Optimization, Migrations   | Expert   |
| **E-Commerce**  | Cart, Checkout, Payments, Orders          | Expert   |
| **Integration** | Stripe, PayPal, Google APIs               | Advanced |
| **Testing**     | PHPUnit, Vitest, Playwright               | Expert   |
| **DevOps**      | Linux, CyberPanel, Deployment             | Advanced |
| **Security**    | Auth, CSRF, XSS, SQL Injection            | Expert   |

---

## 10. Quick Reference Commands

### Backend (Laravel)

```bash
# Development
php artisan serve
php artisan migrate
php artisan db:seed
php artisan queue:work

# Testing
php artisan test
php artisan test --filter=ProductTest

# Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend (Vue/Vite)

```bash
# Development
npm run dev

# Build
npm run build
npm run preview

# Testing
npm run test
npm run test:coverage
```

### Database

```sql
-- Always use this database
USE my_zambezimeats;

-- Check structure
DESCRIBE products;
SHOW INDEX FROM products;
```

---

> **Final Note:** This skills guide ensures consistent, high-quality development of the Zambezi Meats platform. Always reference the MVP documentation for specific requirements and maintain the premium quality expected of a gourmet butchery brand.
