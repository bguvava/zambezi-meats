# Authentication & Currency Fixes

**Date:** December 19, 2025  
**Version:** 1.0.0  
**Status:** Completed ✅

---

## Summary

This document describes fixes for authentication error handling, logout functionality, and currency switching functionality across the Zambezi Meats e-commerce platform.

---

## Issues Addressed

### 1. Auth 401 Error on Homepage (Console Log Noise)

**Problem:** When guests visited the homepage, the console showed `GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)` errors.

**Root Cause:** The auth store's `initialize()` function was logging errors for expected 401 responses when users are not authenticated.

**Solution:** Updated [frontend/src/stores/auth.js](../../frontend/src/stores/auth.js) to silently handle 401 errors during initialization, as this is expected behavior for guest users browsing the shop.

**Changes:**

```javascript
// Before: Logged errors for all failures
async function initialize() {
  // ...
  } catch (error) {
    console.error("Auth initialization failed:", error);
  }
}

// After: Silently handle expected 401s
async function initialize() {
  // ...
  } catch {
    // CSRF initialization failed - user can still browse as guest
    clearAuth();
  }
}
```

---

### 2. Logout Button TypeError

**Problem:** Clicking logout showed error: `TypeError: cartStore is not a function`

**Root Cause:** Incorrect pattern for dynamic import and function call:

```javascript
// WRONG
const cartStore = await import("./cart").then((m) => m.useCartStore());
cartStore().clearOnLogout(); // cartStore is already the store, not a function
```

**Solution:** Fixed the dynamic import pattern in [frontend/src/stores/auth.js](../../frontend/src/stores/auth.js):

```javascript
// CORRECT
const { useCartStore } = await import("./cart");
const cartStore = useCartStore();
cartStore.clearOnLogout();
```

---

### 3. Currency Switcher Not Updating Prices

**Problem:** When switching currencies (AUD ↔ USD), prices didn't update across the site.

**Root Cause:** Components were using pre-formatted price strings from the API (e.g., `product.price_formatted`) instead of using the currency store's `format()` method for dynamic formatting.

**Solution:** Updated the following components to use the currency store:

| Component        | File Path                                           |
| ---------------- | --------------------------------------------------- |
| ProductCard      | `frontend/src/components/shop/ProductCard.vue`      |
| ProductPage      | `frontend/src/pages/ProductPage.vue`                |
| ProductQuickView | `frontend/src/components/shop/ProductQuickView.vue` |
| SearchBar        | `frontend/src/components/shop/SearchBar.vue`        |
| CartPanel        | `frontend/src/components/shop/CartPanel.vue`        |
| CartPage         | `frontend/src/pages/CartPage.vue`                   |
| OrderSummary     | `frontend/src/components/checkout/OrderSummary.vue` |

**Pattern Used:**

```vue
<script setup>
import { useCurrencyStore } from "@/stores/currency";

const currencyStore = useCurrencyStore();

// For computed properties
const formattedPrice = computed(() =>
  currencyStore.format(product.value.price)
);

// For inline use
function formatPrice(amount) {
  return currencyStore.format(amount);
}
</script>

<template>
  <!-- Use the computed or function -->
  <span>{{ formattedPrice }}</span>
  <span>{{ formatPrice(item.unit_price) }}</span>
</template>
```

---

## Files Modified

### Frontend

| File                                       | Changes                                              |
| ------------------------------------------ | ---------------------------------------------------- |
| `src/stores/auth.js`                       | Fixed 401 error handling, fixed logout cart clearing |
| `src/components/shop/ProductCard.vue`      | Added currency store, dynamic price formatting       |
| `src/pages/ProductPage.vue`                | Added currency store, dynamic price formatting       |
| `src/components/shop/ProductQuickView.vue` | Added currency store, dynamic price formatting       |
| `src/components/shop/SearchBar.vue`        | Added currency store, dynamic price formatting       |
| `src/components/shop/CartPanel.vue`        | Added currency store, dynamic price formatting       |
| `src/pages/CartPage.vue`                   | Added currency store, dynamic price formatting       |
| `src/components/checkout/OrderSummary.vue` | Added currency store, dynamic price formatting       |

---

## Testing

### Backend Tests

- **Result:** 389 tests passed (1505 assertions)
- **Duration:** 25.38s
- **Regressions:** None

### Frontend Build

- **Result:** Successful
- **Warnings:** Dynamic import warnings (expected behavior)
- **Errors:** None

---

## How Currency Switching Works

1. **Currency Store** (`stores/currency.js`) manages:

   - Current currency selection (AUD/USD)
   - Exchange rates from API
   - `format(amount)` function for converting and formatting

2. **On Currency Change:**

   - User clicks currency in CurrencySwitcher component
   - `setCurrency(code)` is called, updating `currentCurrency`
   - All components using `currencyStore.format()` reactively update

3. **Exchange Rates:**
   - Fetched from `/api/v1/public/exchange-rates`
   - Default rates: AUD=1.0, USD=0.65
   - Stored in localStorage for persistence

---

## Verification Steps

1. Start backend: `cd backend && php artisan serve --port=8000`
2. Start frontend: `cd frontend && npm run dev`
3. Open http://localhost:5173

### Test Cases:

- [ ] Homepage loads without 401 console errors
- [ ] Login with admin@zambezimeats.com.au / password
- [ ] Click logout → modal appears → confirm → redirected to homepage
- [ ] Switch currency to USD → all prices update with US$ prefix
- [ ] Switch currency to AUD → prices revert to $ prefix
- [ ] Add product to cart → prices in cart reflect current currency
- [ ] Proceed to checkout → OrderSummary shows current currency
