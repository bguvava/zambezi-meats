# Checkout Module Documentation

## Overview

The Checkout Module handles the complete order fulfillment process, from address validation through payment processing to order confirmation. It supports multiple payment gateways and provides a seamless multi-step checkout experience.

## Requirements Coverage

| Requirement | Description                                                       | Status |
| ----------- | ----------------------------------------------------------------- | ------ |
| CHK-001     | 4-step checkout flow (Delivery → Payment → Review → Confirmation) | ✅     |
| CHK-002     | Progress indicator showing current step                           | ✅     |
| CHK-003     | Toggle between saved addresses and new address entry              | ✅     |
| CHK-004     | Saved address selection with visual cards                         | ✅     |
| CHK-005     | New address form with Australian format                           | ✅     |
| CHK-006     | Validate delivery zone and show availability                      | ✅     |
| CHK-007     | Calculate and display delivery fee                                | ✅     |
| CHK-008     | Free delivery threshold messaging                                 | ✅     |
| CHK-009     | Stripe payment integration                                        | ✅     |
| CHK-010     | PayPal payment integration                                        | ✅     |
| CHK-011     | Afterpay payment integration                                      | ✅     |
| CHK-012     | Cash on Delivery option                                           | ✅     |
| CHK-013     | Secure payment form                                               | ✅     |
| CHK-014     | Payment error handling                                            | ✅     |
| CHK-015     | Promo code validation and discount display                        | ✅     |
| CHK-016     | Order summary during review step                                  | ✅     |
| CHK-017     | Edit option for each section in review                            | ✅     |
| CHK-018     | Terms and conditions checkbox                                     | ✅     |
| CHK-019     | Order confirmation with order number                              | ✅     |
| CHK-020     | Order status tracker display                                      | ✅     |
| CHK-021     | Email confirmation (configured via Mail)                          | ✅     |
| CHK-022     | Stock reservation during checkout                                 | ✅     |
| CHK-023     | Session timeout handling                                          | ✅     |
| CHK-024     | Checkout state persistence in Pinia                               | ✅     |
| CHK-025     | Order creation in database                                        | ✅     |
| CHK-026     | Unique order number generation (ZM-YYYYMMDD-XXXX)                 | ✅     |
| CHK-027     | RESTful checkout API endpoints                                    | ✅     |
| CHK-028     | Payment webhook handlers                                          | ✅     |
| CHK-029     | Backend validation                                                | ✅     |
| CHK-030     | API rate limiting                                                 | ✅     |

## Architecture

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── CheckoutController.php    # Main checkout endpoints
│   │   ├── PaymentController.php     # Payment processing
│   │   └── WebhookController.php     # Payment webhooks
│   ├── Requests/Api/V1/
│   │   ├── ValidateAddressRequest.php
│   │   ├── CalculateDeliveryFeeRequest.php
│   │   ├── ValidatePromoRequest.php
│   │   ├── CreateOrderRequest.php
│   │   └── ProcessPaymentRequest.php
│   └── Resources/Api/V1/
│       ├── OrderResource.php
│       ├── OrderItemResource.php
│       ├── PaymentResource.php
│       └── DeliveryZoneResource.php
├── Services/
│   ├── StockReservationService.php
│   └── Payment/
│       ├── PaymentServiceInterface.php
│       ├── StripePaymentService.php
│       ├── PayPalPaymentService.php
│       ├── AfterpayPaymentService.php
│       └── CashOnDeliveryService.php
└── Models/
    ├── Order.php
    ├── OrderItem.php
    ├── Payment.php
    ├── Address.php
    ├── DeliveryZone.php
    └── Promotion.php
```

### Frontend Structure

```
frontend/src/
├── stores/
│   └── checkout.js            # Pinia checkout state
├── pages/
│   └── CheckoutPage.vue       # Main checkout page
└── components/checkout/
    ├── StepIndicator.vue      # Progress steps
    ├── DeliveryStep.vue       # Step 1: Delivery
    ├── AddressForm.vue        # New address form
    ├── SavedAddresses.vue     # Saved address selection
    ├── PaymentStep.vue        # Step 2: Payment
    ├── PaymentMethodSelector.vue
    ├── PromoCodeInput.vue     # Promo code entry
    ├── ReviewStep.vue         # Step 3: Review
    ├── ConfirmationStep.vue   # Step 4: Confirmation
    └── OrderSummary.vue       # Order totals sidebar
```

## API Endpoints

### Public Endpoints (No Authentication)

#### Validate Address

```http
POST /api/v1/checkout/validate-address
```

**Request:**

```json
{
    "postcode": "3000",
    "suburb": "Melbourne",
    "state": "VIC"
}
```

**Response:**

```json
{
    "success": true,
    "delivers": true,
    "message": "Great news! We deliver to your area.",
    "zone": {
        "id": 1,
        "name": "Melbourne CBD",
        "delivery_fee": 9.95,
        "free_delivery_threshold": 100.0,
        "estimated_days": 1
    }
}
```

#### Calculate Delivery Fee

```http
POST /api/v1/checkout/calculate-fee
```

**Request:**

```json
{
    "postcode": "3000",
    "suburb": "Melbourne",
    "subtotal": 80.0
}
```

**Response:**

```json
{
    "success": true,
    "fee": 9.95,
    "fee_formatted": "$9.95",
    "is_free": false,
    "zone_name": "Melbourne CBD",
    "estimated_days": 1,
    "free_delivery_threshold": 100.0,
    "amount_to_free_delivery": 20.0,
    "message": "Add $20.00 more for FREE delivery!"
}
```

#### Validate Promo Code

```http
POST /api/v1/checkout/validate-promo
```

**Request:**

```json
{
    "code": "SAVE10",
    "subtotal": 100.0
}
```

**Response:**

```json
{
    "success": true,
    "valid": true,
    "message": "Promo code applied! You save $10.00",
    "code": "SAVE10",
    "name": "10% Off",
    "type": "percentage",
    "value": 10,
    "discount": 10.0,
    "discount_formatted": "-$10.00"
}
```

#### Get Payment Methods

```http
GET /api/v1/checkout/payment-methods?subtotal=100&currency=AUD
```

**Response:**

```json
{
    "success": true,
    "methods": [
        {
            "id": "stripe",
            "name": "Credit/Debit Card",
            "description": "Pay securely with Visa, Mastercard, or American Express",
            "icon": "credit-card",
            "enabled": true
        },
        {
            "id": "paypal",
            "name": "PayPal",
            "description": "Pay with your PayPal account",
            "icon": "paypal",
            "enabled": true
        },
        {
            "id": "afterpay",
            "name": "Afterpay",
            "description": "Buy now, pay later in 4 interest-free installments",
            "icon": "afterpay",
            "enabled": true,
            "installments": 25.0,
            "min_amount": 35.0,
            "max_amount": 2000.0
        },
        {
            "id": "cod",
            "name": "Cash on Delivery",
            "description": "Pay with cash when your order arrives",
            "icon": "cash",
            "enabled": true,
            "max_amount": 500.0
        }
    ]
}
```

### Authenticated Endpoints

#### Get Checkout Session

```http
GET /api/v1/checkout/session
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "cart": {
    "item_count": 3,
    "subtotal": 150.00,
    "subtotal_formatted": "$150.00"
  },
  "addresses": [...],
  "default_address": {...},
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "0412345678"
  }
}
```

#### Create Order

```http
POST /api/v1/checkout/create-order
Authorization: Bearer {token}
```

**Request (New Address):**

```json
{
    "street_address": "123 Test Street",
    "apartment": "Unit 5",
    "suburb": "Melbourne",
    "state": "VIC",
    "postcode": "3000",
    "promo_code": "SAVE10",
    "delivery_instructions": "Leave at front door",
    "notes": "Please call before delivery",
    "scheduled_date": "2025-01-20",
    "scheduled_time_slot": "10:00 AM - 2:00 PM"
}
```

**Request (Saved Address):**

```json
{
    "address_id": 5,
    "suburb": "Melbourne",
    "state": "VIC",
    "postcode": "3000"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Order created successfully.",
  "order": {
    "id": 123,
    "order_number": "ZM-20250115-A1B2",
    "status": "pending",
    "subtotal": 150.00,
    "delivery_fee": 0.00,
    "discount": 15.00,
    "total": 135.00,
    "items": [...],
    "address": {...}
  }
}
```

### Payment Endpoints

#### Process Stripe Payment

```http
POST /api/v1/checkout/payment/stripe
Authorization: Bearer {token}
```

**Request:**

```json
{
    "order_id": 123
}
```

**Response:**

```json
{
    "success": true,
    "requires_action": true,
    "client_secret": "pi_xxx_secret_xxx",
    "payment_intent_id": "pi_xxx"
}
```

#### Confirm Stripe Payment

```http
POST /api/v1/checkout/payment/stripe/confirm
Authorization: Bearer {token}
```

**Request:**

```json
{
    "payment_intent_id": "pi_xxx"
}
```

#### Process PayPal Payment

```http
POST /api/v1/checkout/payment/paypal
Authorization: Bearer {token}
```

**Response:**

```json
{
    "success": true,
    "approval_url": "https://www.sandbox.paypal.com/checkoutnow?token=xxx",
    "paypal_order_id": "xxx"
}
```

#### Process Afterpay Payment

```http
POST /api/v1/checkout/payment/afterpay
Authorization: Bearer {token}
```

**Response:**

```json
{
    "success": true,
    "redirect_url": "https://portal.sandbox.afterpay.com/checkout?token=xxx",
    "token": "xxx"
}
```

#### Process Cash on Delivery

```http
POST /api/v1/checkout/payment/cod
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "message": "Order confirmed. Please have $135.00 ready when your order arrives.",
  "order": {...}
}
```

### Webhook Endpoints

#### Stripe Webhook

```http
POST /api/v1/webhooks/stripe
```

Handles events:

-   `payment_intent.succeeded` - Updates payment to completed
-   `payment_intent.payment_failed` - Updates payment to failed
-   `charge.refunded` - Updates payment to refunded

#### PayPal Webhook

```http
POST /api/v1/webhooks/paypal
```

Handles events:

-   `PAYMENT.CAPTURE.COMPLETED` - Updates payment to completed
-   `PAYMENT.CAPTURE.DENIED` - Updates payment to failed
-   `PAYMENT.CAPTURE.REFUNDED` - Updates payment to refunded

## Payment Gateway Setup

### Stripe Configuration

1. Create a Stripe account at [stripe.com](https://stripe.com)
2. Get your API keys from the Stripe Dashboard
3. Add to `.env`:

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

4. Set up webhook endpoint in Stripe Dashboard:
    - URL: `https://yourdomain.com/api/v1/webhooks/stripe`
    - Events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`

### PayPal Configuration

1. Create a PayPal Developer account at [developer.paypal.com](https://developer.paypal.com)
2. Create a REST API app
3. Add to `.env`:

```env
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=xxx
PAYPAL_CLIENT_SECRET=xxx
PAYPAL_WEBHOOK_ID=xxx
```

4. Set up webhook in PayPal Developer Dashboard:
    - URL: `https://yourdomain.com/api/v1/webhooks/paypal`
    - Events: `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`, `PAYMENT.CAPTURE.REFUNDED`

### Afterpay Configuration

1. Apply for Afterpay merchant account at [afterpay.com](https://www.afterpay.com/en-AU/for-retailers)
2. Add to `.env`:

```env
AFTERPAY_MERCHANT_ID=xxx
AFTERPAY_SECRET_KEY=xxx
AFTERPAY_REGION=AU
AFTERPAY_ENVIRONMENT=sandbox
```

### Cash on Delivery Configuration

```env
COD_ENABLED=true
COD_MAX_AMOUNT=500.00
```

## Mock Mode

All payment services include mock mode for development. When API keys are not configured, services return mock responses:

-   **Stripe**: Returns mock payment intent with test client secret
-   **PayPal**: Returns mock approval URL
-   **Afterpay**: Returns mock redirect URL
-   **COD**: Always enabled, creates pending payment record

## Stock Reservation

The `StockReservationService` reserves product stock during checkout:

-   **Duration**: 15 minutes
-   **Storage**: Laravel Cache (Redis recommended for production)
-   **Auto-release**: Expired reservations are automatically released

```php
// Reserve stock
$stockService->reserve($productId, $quantity, $orderId);

// Release stock (on order cancellation)
$stockService->release($orderId);

// Check availability
$available = $stockService->getAvailableStock($productId);
```

## Checkout Flow

```
┌─────────────────┐
│  Cart Page      │
│  [Proceed to    │
│   Checkout]     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Step 1: Delivery│
│ • Select Address│
│ • Enter New     │
│ • Validate Zone │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Step 2: Payment │
│ • Select Method │
│ • Enter Promo   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Step 3: Review  │
│ • Confirm Order │
│ • Accept Terms  │
│ [Place Order]   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Payment Gateway │
│ (Stripe/PayPal/ │
│  Afterpay/COD)  │
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Step 4: Confirmation│
│ • Order Number      │
│ • Status Tracker    │
│ • Email Sent        │
└─────────────────────┘
```

## Testing

### Test Summary

| Layer               | Test File                                         | Tests   | Status   |
| ------------------- | ------------------------------------------------- | ------- | -------- |
| Backend API         | `tests/Feature/Api/V1/CheckoutControllerTest.php` | 29      | ✅ Pass  |
| Frontend Store      | `src/tests/checkout/checkoutStore.spec.js`        | 96      | ✅ Pass  |
| Frontend Components | `src/tests/checkout/checkoutComponents.spec.js`   | 25      | ✅ Pass  |
| **Total**           |                                                   | **150** | **100%** |

### Backend Tests

```bash
# Run checkout controller tests
cd backend
php vendor/bin/phpunit tests/Feature/Api/V1/CheckoutControllerTest.php --no-coverage
```

**Test Coverage:**

-   Address validation (deliverable/non-deliverable zones)
-   Delivery fee calculation (free/paid thresholds)
-   Promo code validation (valid/invalid/expired/exhausted/minimum order)
-   Checkout session retrieval
-   Order creation (with address ID, with new address)
-   Stock validation and reservation
-   Unauthenticated access rejection

### Frontend Store Tests

```bash
# Run checkout store tests
cd frontend
npx vitest run src/tests/checkout/checkoutStore.spec.js
```

**Test Coverage:**

-   Initial state (27 tests for all state properties)
-   Computed properties (subtotal, total, isDeliveryValid, isPaymentValid, canProceedToPayment, canProceedToReview)
-   Step navigation (goToStep, nextStep, previousStep)
-   Address selection and clearing
-   Checkout initialization with saved addresses
-   Payment methods loading
-   Address validation with zone info
-   Delivery fee calculation with free delivery threshold
-   Promo code validation (valid/invalid/API errors)
-   Order creation with addressId/new address/promo code
-   Payment processing (Stripe, COD, error handling)
-   Payment confirmation
-   State reset

### Frontend Component Tests

```bash
# Run checkout component tests
cd frontend
npx vitest run src/tests/checkout/checkoutComponents.spec.js
```

**Components Tested:**

-   **StepIndicator** (5 tests): Step rendering, current step highlighting, completed step styling
-   **PromoCodeInput** (8 tests): Input rendering, validation, success/error states, loading spinner
-   **PaymentMethodSelector** (4 tests): Method rendering, selection highlighting, event emission
-   **OrderSummary** (8 tests): Summary display, delivery fee, promo discount, totals

### Run All Tests

```bash
# Full test suite (backend + frontend)
# Backend
cd backend && php vendor/bin/phpunit tests/Feature/Api/V1/CheckoutControllerTest.php --no-coverage

# Frontend
cd frontend && npx vitest run src/tests/checkout
```

### Test Coverage

-   Address validation (deliverable/non-deliverable zones)
-   Delivery fee calculation (free/paid)
-   Promo code validation (valid/invalid/expired/exhausted)
-   Order creation (with/without address, with/without promo)
-   Stock validation
-   All payment methods (Stripe, PayPal, Afterpay, COD)
-   Webhook event handling
-   Authorization checks

## Error Handling

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Human-readable error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

## Security Considerations

1. **Authentication**: All order/payment endpoints require Sanctum authentication
2. **Authorization**: Users can only access their own orders
3. **Webhook Security**: Stripe/PayPal webhooks verify signatures in production
4. **Rate Limiting**: API endpoints are rate-limited (60 requests/minute)
5. **Input Validation**: All requests are validated via Form Request classes
6. **HTTPS**: Payment data only transmitted over HTTPS
7. **PCI Compliance**: Card data never touches our servers (Stripe Elements/PayPal.js)
