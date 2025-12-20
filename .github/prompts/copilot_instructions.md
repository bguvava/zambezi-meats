# Zambezi Meats - AI Copilot Instructions

> **Purpose:** This document guides AI coding assistants (GitHub Copilot, Claude, etc.) to maintain context, follow project standards, and deliver consistent, high-quality code for the Zambezi Meats online butchery store.

---

## 1. Project Identity

### Who We Are

- **Project:** Zambezi Meats Online Butchery Store
- **Company:** Zambezi Meats
- **Location:** 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia
- **Developer:** bguvava (www.bguvava.com)
- **Style:** High-end/Gourmet online butchery

### Brand Guidelines

- **Logo:** `.github/official_logo.png`
- **Color Palette:** Dark reds, browns, cream/beige (derived from logo)
- **Tone:** Professional, premium, trustworthy
- **Target Market:** Sydney locals and surrounding areas

---

## 2. Technology Context

### Backend Stack

| Component | Technology      | Version            |
| --------- | --------------- | ------------------ |
| Framework | Laravel         | 12.x               |
| Language  | PHP             | 8.2+               |
| Database  | MySQL           | 8.0                |
| Auth      | Laravel Sanctum | Cookie-based SPA   |
| Real-time | SSE             | Server-Sent Events |
| Queue     | Laravel Queue   | Supervisor         |

### Frontend Stack

| Component | Technology              | Version             |
| --------- | ----------------------- | ------------------- |
| Framework | Vue.js                  | 3 (Composition API) |
| Build     | Vite                    | Latest              |
| CSS       | Tailwind CSS            | Latest              |
| UI        | shadcn/ui + Headless UI | Latest              |
| State     | Pinia                   | Latest              |
| HTTP      | Axios                   | Latest              |

### Database

- **Name:** `my_zambezimeats`
- **Charset:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

---

## 3. Architecture Guidelines

### Application Type

- **SPA (Single Page Application)** with dashboard-centric design
- **Shop-First Approach:** Users land on shop page, not a traditional landing page
- **Modal Windows:** Use modals for forms and details within dashboards

### API Structure

```
/api/v1/                    # All endpoints versioned
├── auth/                   # Authentication
├── products/               # Public product endpoints
├── cart/                   # Shopping cart
├── checkout/               # Checkout process
├── customer/               # Customer dashboard
├── staff/                  # Staff dashboard
└── admin/                  # Admin dashboard
```

### User Roles

1. **Guest** - Browse shop, add to cart, checkout
2. **Customer** - Full shopping experience + dashboard
3. **Staff** - Order processing, deliveries, waste logging
4. **Admin** - Full system control

---

## 4. Coding Standards

### Always Follow

```yaml
PHP:
  - PSR-12 standard
  - Strict types declaration
  - Type hints for all parameters and returns
  - Form Requests for validation
  - API Resources for responses

Vue.js:
  - Composition API with <script setup>
  - Single-file components
  - Props validation
  - Emit declarations

Database:
  - snake_case for tables and columns
  - Explicit foreign keys
  - Proper indexes
  - Soft deletes where appropriate
```

### File Naming Conventions

```
Backend:
  Controllers:  PascalCaseController.php
  Models:       PascalCase.php
  Migrations:   yyyy_mm_dd_hhmmss_description.php
  Requests:     PascalCaseRequest.php
  Resources:    PascalCaseResource.php

Frontend:
  Components:   PascalCase.vue
  Composables:  useCamelCase.js
  Stores:       camelCase.js
  Utils:        camelCase.js
```

---

## 5. Key Business Rules

### MUST IMPLEMENT

| Rule            | Value        | Context                   |
| --------------- | ------------ | ------------------------- |
| Session Timeout | 5 minutes    | Auto-logout on inactivity |
| Minimum Order   | $100 AUD     | For delivery orders       |
| Free Delivery   | Orders $100+ | In specified zones        |
| Delivery Fee    | $0.15/km     | Outside free zones        |
| Stock Reserve   | 15 minutes   | During checkout           |
| Operating Hours | 7am - 6pm    | Daily                     |

### Currency Support

- **Primary:** AU$ (AUD) - Default
- **Secondary:** US$ (USD) - Via ExchangeRate-API
- All prices stored in AUD, converted on display

### Payment Methods

1. Stripe (Card payments) - AU$/US$
2. PayPal - AU$/US$
3. Afterpay - AU$ only
4. Cash on Delivery - AU$ only

---

## 6. Response Guidelines

### When Asked About Features

1. Check MVP requirements in `.github/prompts/mvp/` parts
2. Reference specific requirement IDs (e.g., SHOP-001, AUTH-005)
3. Follow the documented API endpoints
4. Maintain test coverage at 100%

### When Writing Code

1. Follow `coding_style.json` strictly
2. Include proper error handling
3. Add appropriate comments
4. Write corresponding tests
5. Update documentation

### When Debugging

1. Check for CSRF issues (419 errors)
2. Verify session timeout handling
3. Ensure proper role authorization
4. Check database constraints

---

## 7. Do's and Don'ts

### ✅ ALWAYS DO

- Use Laravel Sanctum for authentication
- Use Server-Sent Events (SSE) for real-time features
- Follow REST conventions for API endpoints
- Validate on both client and server
- Handle all HTTP error codes gracefully
- Write tests before or with features
- Use database transactions for multi-step operations
- Log important events and errors
- Use queues for heavy operations (emails, reports)

### ❌ NEVER DO

- Use WebSockets (use SSE instead)
- Skip CSRF protection
- Store passwords in plain text
- Allow bulk operations (except Activity Logs)
- Bypass role-based authorization
- Return 500 errors to users (handle gracefully)
- Hardcode credentials or API keys
- Skip database migrations
- Ignore mobile responsiveness
- Change database name from `my_zambezimeats`

---

## 8. Common Patterns

### API Response Format

```php
// Success
return response()->json([
    'data' => $resource,
    'message' => 'Operation successful'
], 200);

// Error
return response()->json([
    'message' => 'Human readable error',
    'errors' => ['field' => ['Specific error']]
], 422);
```

### Vue Component Structure

```vue
<script setup>
// 1. Imports
import { ref, computed, onMounted } from 'vue'
import { useProductStore } from '@/stores/product'

// 2. Props & Emits
const props = defineProps({
  productId: { type: Number, required: true }
})
const emit = defineEmits(['updated'])

// 3. Stores & Composables
const productStore = useProductStore()

// 4. Reactive State
const loading = ref(false)
const product = ref(null)

// 5. Computed
const formattedPrice = computed(() => /* ... */)

// 6. Methods
const fetchProduct = async () => { /* ... */ }

// 7. Lifecycle
onMounted(() => fetchProduct())
</script>

<template>
  <!-- Template content -->
</template>

<style scoped>
/* Scoped styles if needed */
</style>
```

### Controller Method Pattern

```php
public function store(StoreProductRequest $request): JsonResponse
{
    try {
        DB::beginTransaction();

        $product = $this->productService->create($request->validated());

        DB::commit();

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Product created successfully'
        ], 201);

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Product creation failed', ['error' => $e->getMessage()]);

        return response()->json([
            'message' => 'Failed to create product. Please try again.'
        ], 500);
    }
}
```

---

## 9. Reference Documents

### Primary Documentation

| Document     | Purpose               | Location                            |
| ------------ | --------------------- | ----------------------------------- |
| MVP Index    | Requirements overview | `.github/prompts/product_mvp.md`    |
| Settings     | Project configuration | `.github/prompts/settings.yml`      |
| Coding Style | Code standards        | `.github/prompts/coding_style.json` |
| Skills       | Development expertise | `.github/prompts/skills.md`         |

### MVP Parts (396 Requirements)

| Part | Content                        | File                                |
| ---- | ------------------------------ | ----------------------------------- |
| 1    | Foundation (49 reqs)           | `mvp/part1-foundation.md`           |
| 2    | Auth & Landing (40 reqs)       | `mvp/part2-auth-landing.md`         |
| 3    | Shop & Cart (51 reqs)          | `mvp/part3-shop-cart.md`            |
| 4    | Checkout (30 reqs)             | `mvp/part4-checkout.md`             |
| 5    | Customer & Staff (47 reqs)     | `mvp/part5-customer-staff.md`       |
| 6    | Admin (28 reqs)                | `mvp/part6-admin.md`                |
| 7    | Inventory & Delivery (37 reqs) | `mvp/part7-inventory-delivery.md`   |
| 8    | Reports & Settings (52 reqs)   | `mvp/part8-reports-settings.md`     |
| 9    | Deployment (62 reqs)           | `mvp/part9-deployment-checklist.md` |

---

## 10. Quick Reference

### Important IDs

- **Database:** `my_zambezimeats`
- **API Prefix:** `/api/v1/`
- **Session Timeout:** 5 minutes
- **Logo:** `.github/official_logo.png`

### Key Endpoints Pattern

```
GET    /api/v1/{resource}          # List
GET    /api/v1/{resource}/{id}     # Show
POST   /api/v1/{resource}          # Create
PUT    /api/v1/{resource}/{id}     # Update
DELETE /api/v1/{resource}/{id}     # Delete
```

### Error Handling Codes

| Code | Meaning       | Action                |
| ---- | ------------- | --------------------- |
| 419  | CSRF Mismatch | Refresh token, retry  |
| 401  | Unauthorized  | Redirect to login     |
| 403  | Forbidden     | Show permission error |
| 404  | Not Found     | Show not found page   |
| 422  | Validation    | Show field errors     |
| 500  | Server Error  | Show friendly message |

---

## 11. Context Maintenance

### Before Each Task

1. Review relevant MVP part document
2. Check `settings.yml` for project rules
3. Follow `coding_style.json` for formatting
4. Reference this document for guidelines

### During Development

1. Maintain consistent naming conventions
2. Write tests alongside features
3. Document API changes
4. Update relevant documentation

### After Completion

1. Verify all tests pass
2. Check coding standards
3. Update progress in documentation
4. Commit with conventional message format

---

> **Remember:** This is a high-end, gourmet online butchery store. Every piece of code should reflect the quality and professionalism of the Zambezi Meats brand.
