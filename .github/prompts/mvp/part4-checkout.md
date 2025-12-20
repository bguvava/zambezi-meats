# Zambezi Meats MVP - Part 4: Checkout Module

## Module Overview

| Field | Value |
|-------|-------|
| **Module Name** | CHECKOUT |
| **Priority** | P0 - Critical |
| **Dependencies** | CART, AUTH |
| **Documentation** | `/docs/checkout/` |
| **Tests** | `/tests/checkout/` |

**Total Requirements: 30**

---

## 4.1 Checkout Module

### Objectives

1. Create streamlined multi-step checkout process
2. Implement secure payment processing (Stripe, PayPal, Afterpay)
3. Validate delivery address and calculate fees
4. Support guest checkout with account creation option
5. Handle payment errors gracefully (prevent 500 errors)

### Success Criteria

| Criteria | Target |
|----------|--------|
| Checkout completion rate | > 75% |
| Payment success rate | > 98% |
| Error handling | All errors graceful |
| Mobile responsive | 100% |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| CHK-001 | Create checkout page layout | As a customer, I want a clear checkout process | Multi-step form: Delivery → Payment → Review → Confirm | Guest/Customer |
| CHK-002 | Implement step indicator | As a customer, I want to know my progress | Progress bar showing: 1. Delivery 2. Payment 3. Review 4. Done | Guest/Customer |
| CHK-003 | Create delivery address form | As a customer, I want to enter my delivery address | Form: street, suburb, city, state, postcode with validation | Guest/Customer |
| CHK-004 | Implement address autocomplete | As a customer, I want easy address entry | Google Places autocomplete for Australian addresses | Guest/Customer |
| CHK-005 | Display saved addresses for logged-in users | As a customer, I want to use saved addresses | Dropdown/cards of saved addresses with "Add New" option | Customer |
| CHK-006 | Validate delivery zone | As a customer, I want to know if you deliver to me | Check postcode against delivery zones, show fee or "Sorry, we don't deliver here" | Guest/Customer |
| CHK-007 | Calculate and display delivery fee | As a customer, I want to see delivery cost | Free if $100+ and in zone, otherwise calculate by distance | Guest/Customer |
| CHK-008 | Create payment method selection | As a customer, I want to choose payment method | Options: Credit Card, PayPal, Afterpay, Cash on Delivery | Guest/Customer |
| CHK-009 | Integrate Stripe payment | As a customer, I want to pay by card | Stripe Elements for secure card input, supports AU$/US$ | Guest/Customer |
| CHK-010 | Integrate PayPal payment | As a customer, I want to pay via PayPal | PayPal button, redirect to PayPal, return to confirm | Guest/Customer |
| CHK-011 | Integrate Afterpay payment | As a customer, I want buy-now-pay-later option | Afterpay widget (AU$ only), show installment amounts | Customer |
| CHK-012 | Implement Cash on Delivery | As a customer, I want to pay on delivery | COD option (AU$ only), no upfront payment required | Customer |
| CHK-013 | Create order review step | As a customer, I want to review before paying | Summary: items, delivery address, payment method, totals | Guest/Customer |
| CHK-014 | Implement order notes field | As a customer, I want to add special instructions | Optional textarea for delivery/order notes | Guest/Customer |
| CHK-015 | Create promo code input | As a customer, I want to apply discounts | Input field with "Apply" button, validates code, shows discount | Guest/Customer |
| CHK-016 | Handle payment success | As a customer, I want confirmation of payment | Success page with order number, email confirmation sent | Guest/Customer |
| CHK-017 | Handle payment failure | As a customer, I want clear feedback if payment fails | Error message with retry option, no order created | Guest/Customer |
| CHK-018 | Handle 419 CSRF errors | As a customer, I want session issues handled | If 419 occurs, refresh CSRF token and prompt retry | Guest/Customer |
| CHK-019 | Handle 500 server errors | As a customer, I want graceful error handling | Friendly error message, log error, suggest contact support | Guest/Customer |
| CHK-020 | Create guest checkout flow | As a guest, I want to checkout without account | Allow checkout without login, offer account creation post-checkout | Guest |
| CHK-021 | Offer account creation post-checkout | As a guest, I want to create account after ordering | "Create account to track your order" with pre-filled email | Guest |
| CHK-022 | Reserve stock during checkout | As a customer, I want my items held while I pay | Stock temporarily reserved for 15 minutes during checkout | Guest/Customer |
| CHK-023 | Send order confirmation email | As a customer, I want email confirmation | Email with order details, items, totals, delivery info | Guest/Customer |
| CHK-024 | Send order alert to admin | As a business owner, I want new order notifications | Email + dashboard alert when new order placed | Admin |
| CHK-025 | Create order in database | As a developer, I need order record created | Order created with items, status "pending", payment record | Developer |
| CHK-026 | Generate unique order number | As a developer, I need order identification | Format: ZM-YYYYMMDD-XXXX (e.g., ZM-20251212-0001) | Developer |
| CHK-027 | Create checkout API endpoints | As a developer, I need checkout APIs | Endpoints for address validation, fee calculation, order creation | Developer |
| CHK-028 | Create payment webhook handlers | As a developer, I need payment confirmation | Webhooks for Stripe, PayPal to confirm payment status | Developer |
| CHK-029 | Create checkout Pinia store | As a developer, I need state management | Store for checkout steps, form data, payment state | Developer |
| CHK-030 | Write checkout module tests | As a developer, I need 100% test coverage | Unit, integration, E2E tests for all checkout flows | Developer |

### Checkout API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/checkout/validate-address` | Validate delivery address | No |
| POST | `/api/v1/checkout/calculate-fee` | Calculate delivery fee | No |
| POST | `/api/v1/checkout/validate-promo` | Validate promo code | No |
| POST | `/api/v1/checkout/create-order` | Create order | Yes* |
| POST | `/api/v1/checkout/payment/stripe` | Process Stripe payment | Yes* |
| POST | `/api/v1/checkout/payment/paypal` | Initiate PayPal payment | Yes* |
| POST | `/api/v1/checkout/payment/afterpay` | Initiate Afterpay payment | Yes |
| POST | `/api/v1/webhooks/stripe` | Stripe webhook | No |
| POST | `/api/v1/webhooks/paypal` | PayPal webhook | No |

*Guest checkout uses session-based identification

### Checkout Flow Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  CHECKOUT                                                                           │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐       │
│  │ 1. Delivery │ ──► │ 2. Payment  │ ──► │ 3. Review   │ ──► │ 4. Complete │       │
│  │     ✓       │     │    ●        │     │             │     │             │       │
│  └─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘       │
│                                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                     │
│  DELIVERY ADDRESS                           │  ORDER SUMMARY                        │
│                                             │                                       │
│  ┌───────────────────────────────────────┐  │  ┌───────────────────────────────┐   │
│  │ 📍 Saved Addresses                    │  │  │ Ribeye Steak (1.5kg)  $68.99 │   │
│  │                                       │  │  │ Chicken Breast (2kg)  $37.98 │   │
│  │  ○ Home - 123 Main St, Engadine 2233 │  │  │ Lamb Chops (1kg)      $32.99 │   │
│  │  ○ Work - 456 Office Rd, Sydney 2000 │  │  │                              │   │
│  │  ○ + Add New Address                 │  │  │ ─────────────────────────────│   │
│  └───────────────────────────────────────┘  │  │ Subtotal           AU$139.96 │   │
│                                             │  │ Delivery               FREE  │   │
│  ┌───────────────────────────────────────┐  │  │ ─────────────────────────────│   │
│  │ Or enter new address:                 │  │  │ TOTAL             AU$139.96 │   │
│  │                                       │  │  └───────────────────────────────┘   │
│  │ Street: [________________________]    │  │                                       │
│  │ Suburb: [__________] State: [NSW ▼]  │  │  ┌───────────────────────────────┐   │
│  │ Postcode: [____]                      │  │  │ Promo Code: [_______] [Apply]│   │
│  └───────────────────────────────────────┘  │  └───────────────────────────────┘   │
│                                             │                                       │
│  PAYMENT METHOD                             │                                       │
│  ┌───────────────────────────────────────┐  │                                       │
│  │  ● Credit/Debit Card                  │  │                                       │
│  │    ┌───────────────────────────────┐  │  │                                       │
│  │    │ Card Number                   │  │  │                                       │
│  │    │ [________________________]    │  │  │                                       │
│  │    │ MM/YY [____] CVV [___]        │  │  │                                       │
│  │    └───────────────────────────────┘  │  │                                       │
│  │                                       │  │                                       │
│  │  ○ PayPal                             │  │                                       │
│  │  ○ Afterpay (4 x $34.99)              │  │                                       │
│  │  ○ Cash on Delivery                   │  │                                       │
│  └───────────────────────────────────────┘  │                                       │
│                                             │                                       │
│  ORDER NOTES (Optional)                     │                                       │
│  ┌───────────────────────────────────────┐  │                                       │
│  │ Please ring doorbell...               │  │                                       │
│  └───────────────────────────────────────┘  │                                       │
│                                             │                                       │
│  ┌───────────────────────────────────────────────────────────────────────────────┐ │
│  │                           PLACE ORDER - AU$139.96                             │ │
│  └───────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                     │
└───────────────────────────────────────────┴─────────────────────────────────────────┘
```

### Order Confirmation Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                     │
│                              ✓ ORDER CONFIRMED                                      │
│                                                                                     │
│                         Thank you for your order, John!                             │
│                                                                                     │
│                     Order Number: ZM-20251212-0024                                  │
│                                                                                     │
│  ───────────────────────────────────────────────────────────────────────────────   │
│                                                                                     │
│  We've sent a confirmation email to john@example.com                               │
│                                                                                     │
│  WHAT'S NEXT?                                                                       │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                              │
│  │ Accepted │→│ Preparing│→│ Delivery │→│ Delivered│                              │
│  │    ●     │ │          │ │          │ │          │                              │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                              │
│                                                                                     │
│  ORDER DETAILS                                                                      │
│  ┌─────────────────────────────────────────────────────────────────────────────┐   │
│  │ Ribeye Steak              1.5 kg x $45.99/kg              = $68.99         │   │
│  │ Chicken Breast            2.0 kg x $18.99/kg              = $37.98         │   │
│  │ Lamb Chops                1.0 kg x $32.99/kg              = $32.99         │   │
│  │ ─────────────────────────────────────────────────────────────────────────  │   │
│  │ Subtotal                                                     $139.96       │   │
│  │ Delivery                                                     FREE          │   │
│  │ ─────────────────────────────────────────────────────────────────────────  │   │
│  │ TOTAL                                                        AU$139.96     │   │
│  └─────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                     │
│  DELIVERY ADDRESS                          PAYMENT METHOD                           │
│  John Smith                                Visa ****4242                            │
│  123 Main Street                                                                    │
│  Engadine, NSW 2233                                                                 │
│                                                                                     │
│  ┌────────────────────┐  ┌────────────────────┐                                    │
│  │   VIEW MY ORDERS   │  │   CONTINUE SHOPPING │                                    │
│  └────────────────────┘  └────────────────────┘                                    │
│                                                                                     │
│  ───────────────────────────────────────────────────────────────────────────────   │
│  Not registered? Create an account to track your orders easily.                    │
│  ┌────────────────────┐                                                            │
│  │   CREATE ACCOUNT   │                                                            │
│  └────────────────────┘                                                            │
│                                                                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Part 4 Summary

| Section | Requirements | IDs |
|---------|--------------|-----|
| Checkout | 30 | CHK-001 to CHK-030 |
| **Total** | **30** | |

---

**Previous:** [Part 3: Shop & Cart Module](part3-shop-cart.md)

**Next:** [Part 5: Customer & Staff Dashboards](part5-customer-staff.md)
