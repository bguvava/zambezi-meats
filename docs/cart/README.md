# Cart Module Documentation

## Overview

The Cart module provides shopping cart functionality with persistent storage, real-time updates, stock validation, and seamless guest-to-authenticated user cart synchronization.

## Requirements Coverage

| Requirement | Description                              | Status |
| ----------- | ---------------------------------------- | ------ |
| CART-001    | Add items to cart                        | ✅     |
| CART-002    | Update item quantities                   | ✅     |
| CART-003    | Remove items from cart                   | ✅     |
| CART-004    | Clear entire cart                        | ✅     |
| CART-005    | Persist cart in localStorage (guest)     | ✅     |
| CART-006    | Persist cart in database (authenticated) | ✅     |
| CART-007    | Sync cart on login                       | ✅     |
| CART-008    | Cart item count badge                    | ✅     |
| CART-009    | Cart total calculation                   | ✅     |
| CART-010    | Slide-out cart panel                     | ✅     |
| CART-011    | Full cart page                           | ✅     |
| CART-012    | Quantity step controls (0.5kg)           | ✅     |
| CART-013    | Stock validation on add                  | ✅     |
| CART-014    | Stock validation on checkout             | ✅     |
| CART-015    | Price change detection                   | ✅     |
| CART-016    | Out of stock item handling               | ✅     |
| CART-017    | Minimum order threshold ($100)           | ✅     |
| CART-018    | Order summary display                    | ✅     |
| CART-019    | Proceed to checkout button               | ✅     |
| CART-020    | Save for later functionality             | ✅     |
| CART-021    | Mobile-responsive cart                   | ✅     |
| CART-022    | Empty cart state                         | ✅     |
| CART-023    | Cart animation/feedback                  | ✅     |

## Architecture

### Backend

#### Controller

**CartController** (`app/Http/Controllers/Api/V1/CartController.php`)

Handles all cart-related API endpoints:

- `show()` - Get current user's cart
- `addItem()` - Add product to cart
- `updateItem()` - Update cart item quantity
- `removeItem()` - Remove item from cart
- `clear()` - Clear entire cart
- `validate()` - Validate cart for checkout
- `sync()` - Sync localStorage cart with server
- `saveForLater()` - Move item to saved list

#### Form Requests

| Request                 | Purpose                       |
| ----------------------- | ----------------------------- |
| `AddToCartRequest`      | Validate product_id, quantity |
| `UpdateCartItemRequest` | Validate quantity updates     |
| `SyncCartRequest`       | Validate cart sync payload    |

#### API Resources

| Resource           | Purpose                           |
| ------------------ | --------------------------------- |
| `CartResource`     | Transform cart with items, totals |
| `CartItemResource` | Transform individual cart item    |

### Frontend

#### Pinia Store

**Cart Store** (`stores/cart.js`)

State:

```javascript
{
  items: [],
  isOpen: false,
  isLoading: false,
  isSyncing: false,
  lastSyncedAt: null,
  error: null
}
```

Getters:

```javascript
{
  itemCount, // Total items in cart
    subtotal, // Sum of all item prices
    formattedSubtotal,
    formattedTotal,
    hasItems,
    meetsMinimumOrder; // Subtotal >= $100
}
```

Actions:

- `initialize()` - Load from localStorage or API
- `addItem(product, quantity)` - Add to cart
- `updateQuantity(itemId, quantity)` - Update quantity
- `removeItem(itemId)` - Remove from cart
- `clearCart()` - Remove all items
- `syncWithServer()` - Sync localStorage to API
- `validateCart()` - Check stock/prices
- `saveForLater(itemId)` - Move to saved
- `toggleCart()` - Open/close panel
- `openCart()` / `closeCart()` - Panel control

#### Vue Components

| Component       | Location           | Purpose               |
| --------------- | ------------------ | --------------------- |
| `CartPanel.vue` | `components/shop/` | Slide-out cart drawer |

#### Pages

| Page           | Route   | Purpose        |
| -------------- | ------- | -------------- |
| `CartPage.vue` | `/cart` | Full cart view |

## API Endpoints

All cart endpoints require authentication.

```
GET /api/v1/cart
  Returns: CartResource with items and totals

POST /api/v1/cart/items
  Body: { product_id: int, quantity: float }
  Returns: CartResource

PUT /api/v1/cart/items/{id}
  Body: { quantity: float }
  Returns: CartResource

DELETE /api/v1/cart/items/{id}
  Returns: CartResource

DELETE /api/v1/cart
  Returns: { message: "Cart cleared" }

POST /api/v1/cart/validate
  Returns: { valid: bool, issues: array }

POST /api/v1/cart/sync
  Body: { items: array }
  Returns: CartResource

POST /api/v1/cart/items/{id}/save-for-later
  Returns: { message: "Item saved for later" }
```

## Cart Persistence Strategy

### Guest Users

1. Cart stored in localStorage as JSON
2. Each item has temporary `id` (Date.now())
3. On login, cart synced to server
4. localStorage cleared after successful sync

```javascript
// localStorage structure
{
  "zambezi_cart": {
    "items": [
      {
        "id": 1699999999999,
        "product_id": 123,
        "product": { /* product data */ },
        "quantity": 1.5,
        "price": 24.99
      }
    ]
  }
}
```

### Authenticated Users

1. Cart stored in database
2. API calls on every action
3. Real-time stock validation
4. Price change detection

### Sync Flow

```
Guest adds items → localStorage
         ↓
User logs in
         ↓
Cart store detects auth change
         ↓
POST /api/v1/cart/sync with localStorage items
         ↓
Server merges carts (combines quantities)
         ↓
Clear localStorage
         ↓
Use server cart going forward
```

## Component Usage

### CartPanel (Global)

The CartPanel is included in `App.vue` and controlled via the cart store:

```vue
// In any component import { useCartStore } from '@/stores/cart' const cart =
useCartStore() // Open cart panel cart.openCart() // Toggle cart panel
cart.toggleCart() // Add item (auto-opens panel) cart.addItem(product, quantity)
```

### Cart in Header

The HeaderNav.vue includes a cart button that shows item count and opens the panel:

```vue
<button @click="cartStore.toggleCart()">
  <CartIcon />
  <span v-if="cartStore.itemCount > 0">
    {{ cartStore.itemCount }}
  </span>
</button>
```

## Minimum Order Threshold

The system enforces a $100 AUD minimum order:

```javascript
// In cart store
meetsMinimumOrder: (state) => {
  return state.subtotal >= 100
}

// In checkout button
<button
  :disabled="!cart.meetsMinimumOrder"
  @click="proceedToCheckout"
>
  Checkout
</button>
```

Visual feedback shows progress toward minimum:

```vue
<div v-if="!meetsMinimumOrder" class="warning">
  Add ${{ (100 - subtotal).toFixed(2) }} more to checkout
</div>
```

## Quantity Controls

Products use 0.5kg step increments:

```javascript
const QUANTITY_STEP = 0.5;
const MIN_QUANTITY = 0.5;
const MAX_QUANTITY = 50;

const decreaseQuantity = () => {
  if (quantity.value > MIN_QUANTITY) {
    quantity.value -= QUANTITY_STEP;
  }
};

const increaseQuantity = () => {
  if (quantity.value < MAX_QUANTITY) {
    quantity.value += QUANTITY_STEP;
  }
};
```

## Validation

### Add to Cart Validation

```javascript
// Frontend check before API call
if (quantity > product.stock_quantity) {
  toast.error("Not enough stock available");
  return;
}
```

### Checkout Validation

```javascript
const { valid, issues } = await cart.validateCart();

if (!valid) {
  issues.forEach((issue) => {
    if (issue.type === "out_of_stock") {
      toast.error(`${issue.product} is out of stock`);
    } else if (issue.type === "price_changed") {
      toast.warning(`${issue.product} price has changed`);
    }
  });
}
```

## Mobile Responsiveness

### Cart Panel

- Full width on mobile (< 640px)
- 400px width on desktop
- Slide-in from right
- Close button and overlay click

### Cart Page

- Single column on mobile
- Two column (items + summary) on desktop
- Sticky order summary on large screens

## User Feedback

### Add to Cart Animation

```vue
<button @click="addToCart" :class="{ 'animate-bounce': justAdded }">
  {{ justAdded ? '✓ Added' : 'Add to Cart' }}
</button>
```

### Toast Notifications

```javascript
// Success
toast.success("Added to cart");

// Error
toast.error("Failed to add item");

// Warning
toast.warning("Price has changed");
```

## Testing

### Test Summary

| Category            | File                     | Tests   | Status |
| ------------------- | ------------------------ | ------- | ------ |
| Backend Controller  | `CartControllerTest.php` | 36      | ✅     |
| Frontend Cart Store | `cartStore.spec.js`      | 81      | ✅     |
| Frontend CartPanel  | `CartPanel.spec.js`      | 47      | ✅     |
| Frontend CartPage   | `CartPage.spec.js`       | 53      | ✅     |
| **Total**           |                          | **217** | ✅     |

### Backend Tests

```bash
cd backend && php vendor/bin/phpunit tests/Feature/Api/V1/CartControllerTest.php
```

**Coverage Areas (36 tests, 83 assertions):**

- **Cart Display**: Empty cart, cart with items, user isolation
- **Add to Cart**: New items, existing items (quantity merge), stock validation
- **Update Quantity**: Valid updates, stock limits, decimal quantities
- **Remove Items**: Single item, last item
- **Clear Cart**: Full cart clearing
- **Validation**: Stock changes, price changes, unavailable products
- **Sync**: Guest cart merge, empty sync, quantity validation
- **Save for Later**: Move to wishlist, item removal from cart
- **Authentication**: All endpoints require auth

### Frontend Tests

```bash
cd frontend && npx vitest run src/tests/cart
```

#### Cart Store Tests (`cartStore.spec.js` - 81 tests)

**Coverage Areas:**

- **Initial State**: Empty items, loading states, minimum order constant
- **Computed Properties**: itemCount, totalQuantity, subtotal, isEmpty, meetsMinimumOrder
- **addItem Action**: New items, existing items, stock validation, cart panel opening
- **updateQuantity Action**: Valid updates, stock caps, item removal at zero
- **removeItem Action**: Item removal, non-existent items
- **clearCart Action**: Full clearing, localStorage cleanup
- **localStorage Persistence**: Save, load, invalid JSON handling
- **Cart Panel Controls**: Toggle, open, close
- **Helper Functions**: getItem, isInCart, getQuantity
- **clearOnLogout Action**: Items, error, lastSyncedAt clearing
- **API Integration**: Authenticated sync, error handling
- **syncWithServer Action**: Fetch on empty, post on items, isSyncing state
- **validateCart Action**: Valid/invalid results, error handling
- **saveForLater Action**: API call, item removal, error handling
- **Guest User Behavior**: No API calls for guest operations
- **Edge Cases**: Decimal quantities, large quantities, missing images

#### CartPanel Tests (`CartPanel.spec.js` - 47 tests)

**Coverage Areas:**

- **Panel Visibility**: Render when open, close button, backdrop
- **Empty Cart State**: Message display, Start Shopping button
- **Loading State**: Spinner, loading animation
- **Cart Items Display**: Count, names, prices, quantities, line totals
- **Quantity Controls**: Decrease/increase buttons, disabled states
- **Remove Functionality**: Button presence, store action calls
- **Cart Summary**: Subtotal, delivery message
- **Minimum Order Warning**: Warning display, amount needed, disabled checkout
- **Checkout Flow**: Button states, authenticated vs guest routing
- **Continue Shopping**: Button and navigation
- **View Full Cart**: Link to /cart page
- **Clear Cart**: Button and store action
- **Stock Warning**: Quantity exceeds stock display
- **Product Images**: Display with fallback
- **Transitions**: Animation classes

#### CartPage Tests (`CartPage.spec.js` - 53 tests)

**Coverage Areas:**

- **Page Header**: Title, item count, singular/plural
- **Initialization**: Store initialize on mount
- **Loading State**: Spinner, animation
- **Empty Cart State**: Message, explanation, Start Shopping link
- **Cart Items Display**: Column headers, product details, prices, quantities
- **Quantity Controls**: Buttons, store actions, disabled states
- **Remove Item**: Button, store action
- **Order Summary**: Heading, subtotal, delivery, total, taxes, security badge
- **Minimum Order Warning**: Warning message, amount needed
- **Checkout Button**: Button states, disabled when below minimum
- **Continue Shopping**: Link to shop
- **Clear Cart**: Button and action
- **Stock Warning**: Quantity exceeds stock
- **Product Images**: Display with fallback
- **Layout**: Grid, sticky summary
- **Uncategorized Products**: Fallback text
- **Responsive Design**: Mobile/desktop classes

### Running All Cart Tests

```bash
# Backend (36 tests)
cd backend && php vendor/bin/phpunit tests/Feature/Api/V1/CartControllerTest.php --no-coverage

# Frontend (181 tests)
cd frontend && npx vitest run src/tests/cart

# Full Suite
cd frontend && npx vitest run
```

### Test Commands Quick Reference

```bash
# Run specific test file
cd frontend && npx vitest run src/tests/cart/cartStore.spec.js

# Run with verbose output
cd frontend && npx vitest run src/tests/cart --reporter=verbose

# Watch mode for development
cd frontend && npx vitest src/tests/cart
```

## Related Documentation

- [Shop Module](../shop/README.md)
- [Authentication](../auth/README.md)
- [Checkout](../checkout/README.md) (Phase 4)
