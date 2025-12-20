# Shop Module Documentation

## Overview

The Shop module provides the main product catalog, browsing, filtering, and search functionality for the Zambezi Meats e-commerce platform.

## Requirements Coverage

| Requirement | Description                           | Status |
| ----------- | ------------------------------------- | ------ |
| SHOP-001    | Display featured products on homepage | ✅     |
| SHOP-002    | Product grid layout with cards        | ✅     |
| SHOP-003    | Product quick view modal              | ✅     |
| SHOP-004    | Category navigation sidebar           | ✅     |
| SHOP-005    | Product filtering by category         | ✅     |
| SHOP-006    | Product filtering by price range      | ✅     |
| SHOP-007    | Product filtering by availability     | ✅     |
| SHOP-008    | Product sorting (price, name, date)   | ✅     |
| SHOP-009    | Search with autocomplete              | ✅     |
| SHOP-010    | Pagination for product lists          | ✅     |
| SHOP-011    | Product detail page                   | ✅     |
| SHOP-012    | Product image gallery                 | ✅     |
| SHOP-013    | Nutrition information display         | ✅     |
| SHOP-014    | Related products section              | ✅     |
| SHOP-015    | Add to cart from product page         | ✅     |
| SHOP-016    | Add to cart from quick view           | ✅     |
| SHOP-017    | Quantity selection (0.5kg steps)      | ✅     |
| SHOP-018    | Stock status badges                   | ✅     |
| SHOP-019    | Sale/special badges                   | ✅     |
| SHOP-020    | Mobile-responsive shop layout         | ✅     |
| SHOP-021    | Mobile filter drawer                  | ✅     |
| SHOP-022    | Loading skeletons                     | ✅     |
| SHOP-023    | Empty state handling                  | ✅     |
| SHOP-024    | Error state handling                  | ✅     |
| SHOP-025    | Breadcrumb navigation                 | ✅     |
| SHOP-026    | Category product counts               | ✅     |
| SHOP-027    | Active filter tags                    | ✅     |
| SHOP-028    | Clear all filters action              | ✅     |

## Architecture

### Backend

#### Controllers

**ProductController** (`app/Http/Controllers/Api/V1/ProductController.php`)

Handles all product-related API endpoints:

- `index()` - List products with filtering, sorting, and pagination
- `featured()` - Get featured/promoted products
- `show()` - Get single product by slug
- `related()` - Get related products for a given product
- `search()` - Quick search with autocomplete

**CategoryController** (`app/Http/Controllers/Api/V1/CategoryController.php`)

Handles category-related API endpoints:

- `index()` - List all active categories with product counts
- `show()` - Get single category details
- `products()` - Get products for a specific category

#### API Resources

| Resource                | Purpose                         |
| ----------------------- | ------------------------------- |
| `ProductResource`       | Transform product model for API |
| `ProductCollection`     | Collection with pagination meta |
| `CategoryResource`      | Transform category model        |
| `CategoryCollection`    | Collection with product counts  |
| `ProductImageResource`  | Transform product images        |
| `NutritionInfoResource` | Transform nutrition data        |

### Frontend

#### Pinia Store

**Products Store** (`stores/products.js`)

State:

```javascript
{
  products: [],
  featuredProducts: [],
  relatedProducts: [],
  currentProduct: null,
  categories: [],
  searchResults: [],
  filters: {
    category: null,
    minPrice: null,
    maxPrice: null,
    inStock: false,
    sortBy: 'name',
    sortOrder: 'asc'
  },
  pagination: { page: 1, perPage: 12, total: 0, lastPage: 1 },
  isLoading: false,
  isSearching: false,
  error: null
}
```

Actions:

- `fetchProducts()` - Get products with current filters
- `fetchFeaturedProducts()` - Get featured products
- `fetchProduct(slug)` - Get single product
- `fetchRelatedProducts(slug)` - Get related products
- `fetchCategories()` - Get all categories
- `quickSearch(query)` - Search with debounce
- `setFilter(key, value)` - Update filter and refetch
- `clearFilters()` - Reset all filters
- `setPage(page)` - Pagination control

#### Vue Components

| Component              | Location           | Purpose                            |
| ---------------------- | ------------------ | ---------------------------------- |
| `ProductCard.vue`      | `components/shop/` | Product grid card with add to cart |
| `CategorySidebar.vue`  | `components/shop/` | Category navigation with counts    |
| `ProductFilters.vue`   | `components/shop/` | Price, stock, sorting filters      |
| `SearchBar.vue`        | `components/shop/` | Search with autocomplete           |
| `ProductQuickView.vue` | `components/shop/` | Modal product preview              |

#### Pages

| Page              | Route             | Purpose                   |
| ----------------- | ----------------- | ------------------------- |
| `ShopPage.vue`    | `/shop`           | Main catalog with filters |
| `ProductPage.vue` | `/products/:slug` | Product detail view       |

## API Endpoints

### Products

```
GET /api/v1/products
  Query params:
    - category: string (slug)
    - min_price: number
    - max_price: number
    - in_stock: boolean
    - sort_by: 'price' | 'name' | 'created_at'
    - sort_order: 'asc' | 'desc'
    - per_page: number (default: 12)
    - page: number

GET /api/v1/products/featured
  Query params:
    - limit: number (default: 8)

GET /api/v1/products/search
  Query params:
    - q: string (search query)
    - limit: number (default: 10)

GET /api/v1/products/{slug}

GET /api/v1/products/{slug}/related
  Query params:
    - limit: number (default: 4)
```

### Categories

```
GET /api/v1/categories

GET /api/v1/categories/{slug}

GET /api/v1/categories/{slug}/products
  Query params:
    - Same as products index
```

## Component Usage

### ProductCard

```vue
<ProductCard :product="product" @quick-view="openQuickView" />
```

### SearchBar

```vue
<SearchBar v-model="searchQuery" @search="handleSearch" @select="goToProduct" />
```

### ProductFilters

```vue
<ProductFilters
  v-model:filters="currentFilters"
  @update:filters="fetchProducts"
/>
```

### ProductQuickView

```vue
<ProductQuickView
  :product="selectedProduct"
  :is-open="isQuickViewOpen"
  @close="isQuickViewOpen = false"
/>
```

## Data Flow

1. **Initial Load**: ShopPage mounts → fetches categories and products
2. **Filtering**: User selects filter → store updates → API called → products refresh
3. **Search**: User types → debounced API call → suggestions shown
4. **Quick View**: User clicks → modal opens with product data
5. **Add to Cart**: Button clicked → cart store addItem → toast notification

## Mobile Responsiveness

- **< 768px**: Single column grid, slide-out filter drawer
- **768px - 1024px**: 2-column grid, collapsible sidebar
- **> 1024px**: 3-4 column grid, permanent sidebar

## Performance Optimizations

1. **Debounced Search**: 300ms debounce on search input
2. **Lazy Loading**: Images use `loading="lazy"` attribute
3. **Pagination**: 12 products per page default
4. **Cached Categories**: Categories fetched once and cached

## Testing

### Backend Tests (42 tests, 100% pass rate)

The backend tests are located in `backend/tests/Feature/Api/V1/` and cover:

**ProductControllerTest (28 tests)**:

- Product listing with various filters (category, price range, stock)
- Combined filtering scenarios
- Sorting by price, name, date in both directions
- Pagination with configurable limits
- Featured products endpoint
- Single product retrieval by slug
- Related products functionality
- Search with query parameter
- Response field validation
- Sale price calculation
- Soft delete handling

**CategoryControllerTest (14 tests)**:

- Category listing with product counts (active only)
- Single category retrieval
- Category products with filtering
- Category products with sorting
- Pagination on category products
- Empty category handling
- Active-only product counts

Run backend tests:

```bash
cd backend
php vendor/bin/phpunit tests/Feature/Api/V1/ProductControllerTest.php tests/Feature/Api/V1/CategoryControllerTest.php --no-coverage
```

### Frontend Tests (285 tests, 100% pass rate)

The frontend tests are located in `frontend/src/tests/shop/` and cover:

**productsStore.spec.js (41 tests)**:

- Initial state validation
- Getters (featuredProducts, hasProducts, hasMore, activeCategories)
- fetchProducts with filters and pagination
- fetchFeaturedProducts with limit parameter
- fetchProduct by slug with error handling
- fetchRelatedProducts
- quickSearch with debouncing
- fetchCategories and fetchCategory
- setFilters and resetFilters actions

**ProductCard.spec.js (32 tests)**:

- Component rendering and product details
- Badge display (featured, sale, out of stock)
- Stock status indicators
- Price display with discount calculation
- Quantity controls (+/- buttons)
- Add to cart functionality
- Quick view trigger
- Product links
- Unit handling

**CategorySidebar.spec.js (17 tests)**:

- Sidebar rendering
- Product counts display
- Category selection and highlighting
- "All Products" option
- Empty state handling
- Icon display
- Accessibility features

**ProductFilters.spec.js (30 tests)**:

- Price range inputs (min/max)
- "In Stock Only" checkbox
- Sort dropdown with all options
- Apply and Reset buttons
- Props reactivity
- Computed sort value
- Filter state management

**SearchBar.spec.js (26 tests)**:

- Search input rendering
- Debounced search behavior
- Autocomplete dropdown
- Loading state
- Keyboard navigation (arrow keys, escape, enter)
- Product selection
- Focus/blur behavior
- Clear button

**ProductQuickView.spec.js (45 tests)**:

- Modal rendering and visibility
- Close button and backdrop click
- Product image gallery navigation
- Price display with discounts
- Stock status
- Quantity controls
- Add to cart functionality
- Featured and sale badges
- Nutrition info display
- In-cart indicator
- View full details link

**ShopPage.spec.js (39 tests)**:

- Page rendering with all components
- Product loading and grid display
- Results count
- View mode toggle
- Active filter chips
- Pagination controls
- Mobile filter button
- Quick view modal integration
- URL query sync
- Filter updates

**ProductPage.spec.js (55 tests)**:

- Page loading states
- Error state handling
- Breadcrumb navigation
- Product details display
- Price with discounts
- Image gallery navigation
- Stock status
- Quantity controls
- Add to cart with feedback
- In-cart indicator
- Tabs (description/nutrition)
- Related products section
- Product meta info

Run frontend tests:

```bash
cd frontend
npx vitest run src/tests/shop
```

### Test Coverage Summary

| Area               | Tests   | Status |
| ------------------ | ------- | ------ |
| Backend Products   | 28      | ✅     |
| Backend Categories | 14      | ✅     |
| Products Store     | 41      | ✅     |
| ProductCard        | 32      | ✅     |
| CategorySidebar    | 17      | ✅     |
| ProductFilters     | 30      | ✅     |
| SearchBar          | 26      | ✅     |
| ProductQuickView   | 45      | ✅     |
| ShopPage           | 39      | ✅     |
| ProductPage        | 55      | ✅     |
| **Total**          | **327** | ✅     |

## Related Documentation

- [Cart Module](../cart/README.md)
- [Authentication](../auth/README.md)
- [Foundation](../foundation/README.md)
