# Payment Gateway Integration Guide

> **Document Type:** Integration Guide  
> **Last Updated:** December 2024  
> **Covers:** Stripe, PayPal, Afterpay, Cash on Delivery

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Stripe Integration](#stripe-integration)
4. [PayPal Integration](#paypal-integration)
5. [Afterpay Integration](#afterpay-integration)
6. [Cash on Delivery](#cash-on-delivery)
7. [Frontend Integration](#frontend-integration)
8. [Testing](#testing)
9. [Production Checklist](#production-checklist)

---

## Overview

Zambezi Meats supports four payment methods:

| Gateway              | Currencies | Features                                  |
| -------------------- | ---------- | ----------------------------------------- |
| **Stripe**           | AUD, USD   | Credit/Debit Cards, Apple Pay, Google Pay |
| **PayPal**           | AUD, USD   | PayPal Account, Credit/Debit Cards        |
| **Afterpay**         | AUD only   | Buy Now, Pay Later (4 installments)       |
| **Cash on Delivery** | AUD only   | Pay cash on delivery                      |

All payment gateway credentials are stored in the **Settings** table and managed through the Admin Settings page.

---

## Architecture

### Backend Components

```
backend/app/Services/Payment/
├── PaymentServiceInterface.php   # Interface all gateways implement
├── StripePaymentService.php      # Stripe implementation
├── PayPalPaymentService.php      # PayPal implementation
├── AfterpayPaymentService.php    # Afterpay implementation
└── CashOnDeliveryService.php     # COD implementation

backend/app/Services/
└── SettingsService.php           # Provides cached access to payment settings
```

### Settings Keys (Database)

| Setting Key             | Type    | Group   | Description                     |
| ----------------------- | ------- | ------- | ------------------------------- |
| `stripe_enabled`        | boolean | payment | Enable/disable Stripe           |
| `stripe_public_key`     | string  | payment | Stripe publishable key          |
| `stripe_secret_key`     | string  | payment | Stripe secret key               |
| `stripe_webhook_secret` | string  | payment | Stripe webhook signing secret   |
| `stripe_mode`           | string  | payment | `test` or `live`                |
| `paypal_enabled`        | boolean | payment | Enable/disable PayPal           |
| `paypal_client_id`      | string  | payment | PayPal client ID                |
| `paypal_secret`         | string  | payment | PayPal client secret            |
| `paypal_mode`           | string  | payment | `sandbox` or `live`             |
| `afterpay_enabled`      | boolean | payment | Enable/disable Afterpay         |
| `afterpay_merchant_id`  | string  | payment | Afterpay merchant ID            |
| `afterpay_secret`       | string  | payment | Afterpay secret key             |
| `cod_enabled`           | boolean | payment | Enable/disable Cash on Delivery |

---

## Stripe Integration

### Step 1: Create Stripe Account

1. Go to [https://dashboard.stripe.com/register](https://dashboard.stripe.com/register)
2. Complete account registration
3. Verify your email and business information

### Step 2: Get API Keys

1. Log into Stripe Dashboard
2. Navigate to **Developers → API Keys**
3. Copy:
   - **Publishable key** (starts with `pk_test_` or `pk_live_`)
   - **Secret key** (starts with `sk_test_` or `sk_live_`)

### Step 3: Configure Webhook

1. Navigate to **Developers → Webhooks**
2. Click **Add endpoint**
3. Set endpoint URL: `https://yourdomain.com/api/v1/webhooks/stripe`
4. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Copy the **Signing secret** (starts with `whsec_`)

### Step 4: Configure in Admin Panel

1. Log in as Admin
2. Go to **Settings → Payment Methods**
3. Enable Stripe toggle
4. Enter:
   - Publishable Key
   - Secret Key
   - Webhook Secret
   - Mode: `test` for development, `live` for production
5. Click **Save**

### Step 5: Install Stripe PHP SDK (if not installed)

```bash
cd backend
composer require stripe/stripe-php
```

### Stripe Payment Flow

```
1. Customer selects Stripe at checkout
2. Frontend calls POST /api/v1/checkout/initiate-payment
3. Backend creates PaymentIntent, returns client_secret
4. Frontend uses Stripe.js to collect card details
5. Stripe processes payment
6. Webhook confirms payment (payment_intent.succeeded)
7. Order status updated to "Confirmed"
```

### Code Example: Initiate Stripe Payment

```php
// StripePaymentService.php
public function initiatePayment(Order $order, array $paymentData = []): array
{
    $paymentIntent = $this->stripe->paymentIntents->create([
        'amount' => (int) ($order->total * 100), // Convert to cents
        'currency' => strtolower($order->currency),
        'metadata' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ],
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    return [
        'success' => true,
        'client_secret' => $paymentIntent->client_secret,
        'publishable_key' => $this->settings->getStripePublicKey(),
    ];
}
```

---

## PayPal Integration

### Step 1: Create PayPal Business Account

1. Go to [https://www.paypal.com/au/business](https://www.paypal.com/au/business)
2. Sign up for a Business account
3. Complete verification

### Step 2: Create REST API App

1. Go to [https://developer.paypal.com/dashboard/applications/sandbox](https://developer.paypal.com/dashboard/applications/sandbox)
2. Click **Create App**
3. Name your app (e.g., "Zambezi Meats")
4. Copy:
   - **Client ID**
   - **Client Secret**

### Step 3: Configure Webhook (Optional but Recommended)

1. In the app settings, go to **Webhooks**
2. Add webhook URL: `https://yourdomain.com/api/v1/webhooks/paypal`
3. Subscribe to events:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`

### Step 4: Configure in Admin Panel

1. Log in as Admin
2. Go to **Settings → Payment Methods**
3. Enable PayPal toggle
4. Enter:
   - Client ID
   - Client Secret
   - Mode: `sandbox` for development, `live` for production
5. Click **Save**

### PayPal Payment Flow

```
1. Customer selects PayPal at checkout
2. Frontend calls POST /api/v1/checkout/initiate-payment
3. Backend creates PayPal Order, returns approval_url
4. Customer redirected to PayPal to authorize
5. PayPal redirects back to confirm URL with token
6. Backend captures payment
7. Order status updated to "Confirmed"
```

### Code Example: Initiate PayPal Payment

```php
// PayPalPaymentService.php
public function initiatePayment(Order $order, array $paymentData = []): array
{
    $response = Http::withToken($this->getAccessToken())
        ->post("{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $order->currency,
                    'value' => number_format($order->total, 2, '.', ''),
                ],
                'reference_id' => $order->order_number,
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ]);

    $approvalUrl = collect($response->json('links'))
        ->firstWhere('rel', 'approve')['href'];

    return [
        'success' => true,
        'approval_url' => $approvalUrl,
    ];
}
```

---

## Afterpay Integration

### Step 1: Apply for Afterpay Merchant Account

1. Go to [https://www.afterpay.com/en-AU/for-retailers](https://www.afterpay.com/en-AU/for-retailers)
2. Click **Get Started**
3. Complete the application form
4. Wait for approval (can take 1-5 business days)

### Step 2: Get API Credentials

Once approved:

1. Log into Afterpay Merchant Portal
2. Navigate to **Settings → API Configuration**
3. Copy:
   - **Merchant ID**
   - **Secret Key**

### Step 3: Configure in Admin Panel

1. Log in as Admin
2. Go to **Settings → Payment Methods**
3. Enable Afterpay toggle
4. Enter:
   - Merchant ID
   - Secret Key
5. Click **Save**

### Afterpay Payment Flow

```
1. Customer selects Afterpay at checkout (AUD orders only)
2. Frontend calls POST /api/v1/checkout/initiate-payment
3. Backend creates Afterpay checkout, returns redirect_url
4. Customer redirected to Afterpay to authorize
5. Afterpay redirects back with token
6. Backend captures payment
7. Order status updated to "Confirmed"
```

### Afterpay Restrictions

- **AUD only** - Afterpay only supports Australian Dollar
- **Order limits**: $50 - $1,000 (configurable by Afterpay)
- **No refunds via API** - Refunds must be processed through Afterpay portal

### Displaying Installments

```javascript
// Frontend: Show installment price
const installment = (orderTotal / 4).toFixed(2);
// "4 interest-free payments of $25.00"
```

---

## Cash on Delivery

### Configuration

1. Log in as Admin
2. Go to **Settings → Payment Methods**
3. Enable "Cash on Delivery" toggle
4. Click **Save**

### COD Restrictions

- **AUD only** - COD only available for Australian Dollar orders
- **Maximum order**: $500 (configurable)
- **Delivery zones**: COD may be restricted to certain zones

### COD Payment Flow

```
1. Customer selects COD at checkout
2. Order is created with status "Confirmed"
3. Payment record created with status "Pending"
4. Delivery driver collects cash on delivery
5. Staff marks payment as "Collected" via Staff Dashboard
6. Order status updated to "Delivered"
```

---

## Frontend Integration

### Loading Payment Settings

The frontend `appSettings` store loads payment availability:

```javascript
// src/stores/appSettings.js
const stripeEnabled = computed(() => settings.value.stripe_enabled ?? false);
const paypalEnabled = computed(() => settings.value.paypal_enabled ?? false);
const afterpayEnabled = computed(
  () => settings.value.afterpay_enabled ?? false
);
const codEnabled = computed(() => settings.value.cod_enabled ?? false);

const enabledPaymentMethods = computed(() => {
  const methods = [];
  if (stripeEnabled.value) methods.push("stripe");
  if (paypalEnabled.value) methods.push("paypal");
  if (afterpayEnabled.value) methods.push("afterpay");
  if (codEnabled.value) methods.push("cod");
  return methods;
});
```

### Stripe Elements Integration

```vue
<!-- CheckoutPage.vue -->
<script setup>
import { loadStripe } from "@stripe/stripe-js";

const stripe = await loadStripe(publishableKey);
const elements = stripe.elements();
const cardElement = elements.create("card");
cardElement.mount("#card-element");

// On submit
const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
  payment_method: { card: cardElement },
});
</script>
```

### PayPal Button Integration

```vue
<!-- CheckoutPage.vue -->
<script setup>
// After initiating payment, redirect to PayPal
window.location.href = approvalUrl;

// On return, capture payment
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get("token");
await api.post("/checkout/confirm-payment", {
  gateway: "paypal",
  token,
});
</script>
```

---

## Testing

### Test Mode Credentials

#### Stripe Test Cards

| Card Number           | Scenario                      |
| --------------------- | ----------------------------- |
| `4242 4242 4242 4242` | Successful payment            |
| `4000 0000 0000 9995` | Declined (insufficient funds) |
| `4000 0000 0000 0002` | Declined (generic)            |

Use any future expiry date and any 3-digit CVC.

#### PayPal Sandbox

1. Create sandbox accounts at [https://developer.paypal.com/dashboard/accounts](https://developer.paypal.com/dashboard/accounts)
2. Use sandbox buyer account email/password during checkout

#### Afterpay Sandbox

1. Use sandbox credentials from Afterpay
2. Test with sandbox buyer accounts provided by Afterpay

### Running Payment Tests

```bash
# Backend tests
cd backend
php artisan test --filter=Payment

# Test specific gateway
php artisan test --filter=Stripe
php artisan test --filter=PayPal
php artisan test --filter=Afterpay
```

---

## Production Checklist

### Before Going Live

- [ ] **Stripe**

  - [ ] Switch to live API keys (`pk_live_`, `sk_live_`)
  - [ ] Configure production webhook URL
  - [ ] Set mode to `live` in Settings
  - [ ] Verify webhook is receiving events

- [ ] **PayPal**

  - [ ] Switch to live credentials
  - [ ] Set mode to `live` in Settings
  - [ ] Verify payment capture works

- [ ] **Afterpay**

  - [ ] Get production credentials from Afterpay
  - [ ] Configure production mode
  - [ ] Test with real transactions

- [ ] **General**
  - [ ] Enable HTTPS
  - [ ] Verify CSRF protection
  - [ ] Test complete checkout flow
  - [ ] Verify email notifications work
  - [ ] Test refund process
  - [ ] Set up monitoring/alerts for failed payments

### Security Considerations

1. **Never expose secret keys** in frontend code
2. **Always use HTTPS** in production
3. **Validate webhook signatures** to prevent tampering
4. **Store credentials encrypted** (consider using Laravel's encrypted casts)
5. **Log all payment events** for auditing
6. **Implement rate limiting** on payment endpoints

---

## Troubleshooting

### Common Issues

| Issue                           | Solution                                                      |
| ------------------------------- | ------------------------------------------------------------- |
| Stripe: "Invalid API key"       | Check Settings for correct keys, verify mode matches key type |
| PayPal: "Authentication failed" | Verify client ID/secret, check mode (sandbox vs live)         |
| Afterpay: "Merchant not found"  | Verify merchant ID, ensure account is approved                |
| Payments stuck as "Pending"     | Check webhook configuration, verify webhook secret            |
| Currency mismatch               | Afterpay only supports AUD; verify currency before initiating |

### Debugging

Enable payment logging in `.env`:

```
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

Check logs:

```bash
tail -f storage/logs/laravel.log | grep -i payment
```

---

## API Endpoints

| Endpoint                            | Method | Description                     |
| ----------------------------------- | ------ | ------------------------------- |
| `/api/v1/checkout/initiate-payment` | POST   | Start payment for current order |
| `/api/v1/checkout/confirm-payment`  | POST   | Confirm/capture payment         |
| `/api/v1/webhooks/stripe`           | POST   | Stripe webhook receiver         |
| `/api/v1/webhooks/paypal`           | POST   | PayPal webhook receiver         |
| `/api/v1/admin/orders/{id}/refund`  | POST   | Process refund (admin only)     |
| `/api/v1/settings/public`           | GET    | Get enabled payment methods     |

---

## Summary

1. **Configure credentials** in Admin Settings → Payment Methods
2. **Set correct mode** (test/sandbox for development, live for production)
3. **Configure webhooks** for automatic payment confirmation
4. **Test thoroughly** before going live
5. **Monitor payments** and set up alerts for failures

For support, contact the development team or refer to:

- [Stripe Documentation](https://stripe.com/docs)
- [PayPal Developer Docs](https://developer.paypal.com/docs)
- [Afterpay Developer Docs](https://developers.afterpay.com)
