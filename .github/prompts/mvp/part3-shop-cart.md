# Zambezi Meats MVP - Part 3: Shop & Cart Module

## Module Overview

| Field | Value |
|-------|-------|
| **Module Name** | SHOP-CART |
| **Priority** | P0 - Critical |
| **Dependencies** | AUTH-LANDING, FOUNDATION |
| **Documentation** | `/docs/shop/`, `/docs/cart/` |
| **Tests** | `/tests/shop/`, `/tests/cart/` |

This module combines:
- Shop & Product Catalog (SHOP)
- Cart (CART)

**Total Requirements: 51**

---

## 3.1 Shop & Product Catalog

### Objectives

1. Create the primary shop interface as the main destination from landing page
2. Display products with categories, filtering, and search
3. Implement multi-currency display (AU$/US$)
4. Show real-time stock availability
5. Provide quick-view and full product detail modals
6. Optimize for performance with lazy loading and pagination

### Success Criteria

| Criteria | Target |
|----------|--------|
| Page load time | < 1.5 seconds |
| Products per page | 12-24 (configurable) |
| Search response time | < 500ms |
| Filter response time | < 300ms |
| Mobile responsive | 100% |
| Test coverage | 100% |

### Search Filter Optimization

> **Note:** Limited filters to reduce database queries and enhance data retrieval speeds.

| Filter | Type | Indexed |
|--------|------|---------|
| Category | Single select | ✅ |
| Price Range | Min/Max slider | ✅ |
| Availability | In Stock only | ✅ |
| Sort By | Price, Name, Newest | ✅ |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| SHOP-001 | Create shop page layout | As a customer, I want to browse all products easily | Grid/list view of products with sidebar categories, header with search | Guest/Customer |
| SHOP-002 | Implement category sidebar/navigation | As a customer, I want to filter by meat type | Clickable category list with product counts, collapsible on mobile | Guest/Customer |
| SHOP-003 | Create product card component | As a customer, I want to see product info at a glance | Card showing: image, name, price/kg, stock status, quick add button | Guest/Customer |
| SHOP-004 | Implement product search | As a customer, I want to find specific products quickly | Search bar with instant results dropdown, searches name and description | Guest/Customer |
| SHOP-005 | Implement price range filter | As a customer, I want to filter by budget | Dual-handle slider for min/max price, updates results in real-time | Guest/Customer |
| SHOP-006 | Implement sort functionality | As a customer, I want to sort products | Dropdown: Price Low-High, Price High-Low, Name A-Z, Newest First | Guest/Customer |
| SHOP-007 | Implement "In Stock Only" filter | As a customer, I want to see only available products | Toggle to hide out-of-stock items | Guest/Customer |
| SHOP-008 | Create product quick-view modal | As a customer, I want to see more details without leaving shop | Modal with larger image, full description, add to cart with quantity | Guest/Customer |
| SHOP-009 | Create full product detail page | As a customer, I want comprehensive product information | Full page with gallery, description, nutrition info, related products | Guest/Customer |
| SHOP-010 | Implement product image gallery | As a customer, I want to see multiple product images | Thumbnail gallery with zoom on hover, lightbox on click | Guest/Customer |
| SHOP-011 | Display currency toggle | As a customer, I want to view prices in my currency | Header dropdown to switch AU$/US$, all prices update instantly | Guest/Customer |
| SHOP-012 | Show real-time stock levels | As a customer, I want to know product availability | Stock indicator: "In Stock", "Low Stock (X left)", "Out of Stock" | Guest/Customer |
| SHOP-013 | Implement pagination | As a customer, I want to browse many products efficiently | Page numbers or infinite scroll with "Load More" button | Guest/Customer |
| SHOP-014 | Create "Add to Cart" functionality | As a customer, I want to add products to my cart | Weight input (kg) + Add button, updates cart count in header | Guest/Customer |
| SHOP-015 | Implement wishlist toggle | As a customer, I want to save products for later | Heart icon to add/remove from wishlist (requires login) | Customer |
| SHOP-016 | Show featured products section | As a customer, I want to see popular/recommended items | Highlighted section at top for featured products | Guest/Customer |
| SHOP-017 | Display promotional banners | As a customer, I want to see current deals | Banner area for promotions, linked to filtered results | Guest/Customer |
| SHOP-018 | Implement breadcrumb navigation | As a customer, I want to know where I am | Breadcrumbs: Home > Shop > Category > Product | Guest/Customer |
| SHOP-019 | Create empty state for no results | As a customer, I want feedback when search finds nothing | Friendly message with suggestions or "Clear Filters" option | Guest/Customer |
| SHOP-020 | Implement skeleton loading | As a customer, I want visual feedback while loading | Skeleton placeholders for products during API calls | Guest/Customer |
| SHOP-021 | Create products API endpoint | As a developer, I need a products listing API | `GET /api/v1/products` with filtering, sorting, pagination | Developer |
| SHOP-022 | Create single product API endpoint | As a developer, I need product detail API | `GET /api/v1/products/{slug}` returns full product data | Developer |
| SHOP-023 | Create categories API endpoint | As a developer, I need categories listing API | `GET /api/v1/categories` returns all active categories | Developer |
| SHOP-024 | Create featured products API endpoint | As a developer, I need featured products API | `GET /api/v1/products/featured` returns featured items | Developer |
| SHOP-025 | Create products Pinia store | As a developer, I need state management for products | Store with products, categories, filters, loading states | Developer |
| SHOP-026 | Implement URL query sync | As a customer, I want to share filtered results | Filters sync to URL params, shareable links work | Guest/Customer |
| SHOP-027 | Optimize images for web | As a developer, I need fast image loading | Images served in WebP format, multiple sizes, lazy loaded | Developer |
| SHOP-028 | Write shop module tests | As a developer, I need 100% test coverage | Unit tests for components, integration tests for API, E2E tests | Developer |

### Shop API Endpoints

| Method | Endpoint | Description | Auth | Filters |
|--------|----------|-------------|------|---------|
| GET | `/api/v1/products` | List products | No | category, min_price, max_price, in_stock, sort, page |
| GET | `/api/v1/products/featured` | Featured products | No | limit |
| GET | `/api/v1/products/{slug}` | Product detail | No | - |
| GET | `/api/v1/categories` | List categories | No | - |
| GET | `/api/v1/categories/{slug}` | Category with products | No | - |

### Shop Page Wireframe

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  HEADER                                                                             │
│  Logo   Shop  About  Contact              🔍 Search...     AU$/US$  🛒(3)  Login   │
├─────────────────────────────────────────────────────────────────────────────────────┤
│  Home > Shop > Beef                                                                 │
├────────────────┬────────────────────────────────────────────────────────────────────┤
│  CATEGORIES    │  ┌─────────────────────────────────────────────────────────────┐   │
│                │  │ 🔍 Search products...                    Sort: [Price ▼]   │   │
│  ☑ All (45)    │  └─────────────────────────────────────────────────────────────┘   │
│  ☐ Beef (12)   │                                                                    │
│  ☐ Chicken (8) │  ┌─────────────────────────────────────────────────────────────┐   │
│  ☐ Lamb (10)   │  │ FEATURED: Premium Wagyu Beef - 20% OFF This Week!           │   │
│  ☐ Pork (7)    │  └─────────────────────────────────────────────────────────────┘   │
│  ☐ Seafood (5) │                                                                    │
│  ☐ Sausages(3) │   ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐  │
│                │   │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │
│  ────────────  │   │            │  │            │  │            │  │            │  │
│  PRICE RANGE   │   │ Ribeye     │  │ T-Bone     │  │ Scotch     │  │ Rump       │  │
│  $0 ━━━━━ $100 │   │ $45.99/kg  │  │ $38.99/kg  │  │ $52.99/kg  │  │ $28.99/kg  │  │
│                │   │ ● In Stock │  │ ● Low (3)  │  │ ● In Stock │  │ ○ Out      │  │
│  ────────────  │   │ [Add ♡]    │  │ [Add ♡]    │  │ [Add ♡]    │  │ [♡]        │  │
│  ☑ In Stock    │   └────────────┘  └────────────┘  └────────────┘  └────────────┘  │
│                │                                                                    │
│                │   ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐  │
│                │   │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │   [IMG]    │  │
│                │   │ ...        │  │ ...        │  │ ...        │  │ ...        │  │
│                │   └────────────┘  └────────────┘  └────────────┘  └────────────┘  │
│                │                                                                    │
│                │              ◀  1  2  3  4  5  ...  12  ▶                         │
└────────────────┴────────────────────────────────────────────────────────────────────┘
```

---

## 3.2 Cart Module

### Objectives

1. Create persistent shopping cart (localStorage + database for logged-in users)
2. Display cart as slide-out panel for seamless shopping
3. Calculate totals with real-time currency conversion
4. Validate stock availability before checkout
5. Support guest checkout with cart preservation

### Success Criteria

| Criteria | Target |
|----------|--------|
| Cart persistence | 7 days for guests, indefinite for users |
| Update response time | < 200ms |
| Stock validation | Real-time on checkout |
| Currency conversion | Accurate to 2 decimals |
| Test coverage | 100% |

### Requirements

| Requirement ID | Description | User Story | Expected Outcome | Role |
|----------------|-------------|------------|------------------|------|
| CART-001 | Create cart slide-out panel | As a customer, I want to view my cart without leaving the page | Slide-out panel from right with cart items, totals, checkout button | Guest/Customer |
| CART-002 | Display cart items list | As a customer, I want to see what's in my cart | List showing: product image, name, price/kg, quantity, line total, remove button | Guest/Customer |
| CART-003 | Implement quantity adjustment | As a customer, I want to change quantities | +/- buttons or input field to adjust kg, minimum 0.5kg increments | Guest/Customer |
| CART-004 | Implement item removal | As a customer, I want to remove items | X button removes item with confirmation, cart updates | Guest/Customer |
| CART-005 | Calculate and display subtotal | As a customer, I want to see my total | Running subtotal calculated from all line items | Guest/Customer |
| CART-006 | Display delivery fee estimate | As a customer, I want to know delivery costs | Show "Free delivery on orders $100+" or estimated fee | Guest/Customer |
| CART-007 | Display order total | As a customer, I want to see final total | Subtotal + Delivery Fee = Total, in selected currency | Guest/Customer |
| CART-008 | Show minimum order warning | As a customer, I want to know if I meet minimum | Warning if cart < $100: "Add $X more for delivery" | Guest/Customer |
| CART-009 | Display currency conversion | As a customer, I want to see prices in my currency | All prices converted if US$ selected, show rate used | Guest/Customer |
| CART-010 | Implement cart persistence (localStorage) | As a guest, I want my cart saved | Cart saved to localStorage, persists across sessions | Guest |
| CART-011 | Sync cart to database for logged-in users | As a customer, I want my cart synced to my account | Cart synced to database on login, merge with localStorage | Customer |
| CART-012 | Validate stock on add to cart | As a customer, I want to know if item is available | Check stock before adding, show error if insufficient | Guest/Customer |
| CART-013 | Validate stock on checkout | As a customer, I want to know if items still available | Re-validate all items before checkout, remove unavailable | Guest/Customer |
| CART-014 | Update cart icon badge | As a customer, I want to see cart item count | Header cart icon shows number of items | Guest/Customer |
| CART-015 | Create "Continue Shopping" button | As a customer, I want to add more items | Button closes cart panel, returns to shop | Guest/Customer |
| CART-016 | Create "Proceed to Checkout" button | As a customer, I want to complete my order | Button validates cart and navigates to checkout | Guest/Customer |
| CART-017 | Handle empty cart state | As a customer, I want feedback when cart is empty | Message: "Your cart is empty" with "Start Shopping" link | Guest/Customer |
| CART-018 | Implement cart animation | As a customer, I want visual feedback | Items animate when added/removed, totals animate on change | Guest/Customer |
| CART-019 | Create cart API endpoints | As a developer, I need cart CRUD APIs | `POST/GET/PUT/DELETE /api/v1/cart` endpoints | Developer |
| CART-020 | Create cart Pinia store | As a developer, I need cart state management | Store with items, totals, add/remove/update actions | Developer |
| CART-021 | Handle price changes | As a customer, I want current prices | If product price changed since added, show notification | Guest/Customer |
| CART-022 | Implement "Save for Later" | As a customer, I want to move items to wishlist | Option to move item from cart to wishlist | Customer |
| CART-023 | Write cart module tests | As a developer, I need 100% test coverage | Unit tests for calculations, integration tests for sync | Developer |

### Cart API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/cart` | Get user's cart | Yes |
| POST | `/api/v1/cart/items` | Add item to cart | Yes |
| PUT | `/api/v1/cart/items/{id}` | Update cart item quantity | Yes |
| DELETE | `/api/v1/cart/items/{id}` | Remove item from cart | Yes |
| DELETE | `/api/v1/cart` | Clear entire cart | Yes |
| POST | `/api/v1/cart/validate` | Validate cart for checkout | Yes |
| POST | `/api/v1/cart/sync` | Sync localStorage cart on login | Yes |

### Cart Panel Wireframe

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                          [X] Close  │
│  🛒 YOUR CART (3 items)                                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Ribeye Steak                                    [🗑️]   │ │
│  │        $45.99/kg                                               │ │
│  │        Qty: [-] 1.5 kg [+]                    = $68.99         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Chicken Breast                                  [🗑️]   │ │
│  │        $18.99/kg                                               │ │
│  │        Qty: [-] 2.0 kg [+]                    = $37.98         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ [IMG]  Lamb Chops                                      [🗑️]   │ │
│  │        $32.99/kg  ⚠️ Price updated                             │ │
│  │        Qty: [-] 1.0 kg [+]                    = $32.99         │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Subtotal                                            AU$ 139.96     │
│  Delivery                                            FREE ✓         │
│  ─────────────────────────────────────────────────────────────────  │
│  TOTAL                                               AU$ 139.96     │
│                                                                     │
│  💱 View in US$: ~$91.17 (Rate: 0.6514)                            │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              PROCEED TO CHECKOUT                            │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ← Continue Shopping                                                │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Part 3 Summary

| Section | Requirements | IDs |
|---------|--------------|-----|
| Shop & Product Catalog | 28 | SHOP-001 to SHOP-028 |
| Cart | 23 | CART-001 to CART-023 |
| **Total** | **51** | |

---

**Previous:** [Part 2: Authentication & Landing Module](part2-auth-landing.md)

**Next:** [Part 4: Checkout Module](part4-checkout.md)
