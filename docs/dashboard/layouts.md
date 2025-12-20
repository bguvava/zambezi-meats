# Dashboard Layouts Documentation

## Overview

Complete documentation for the DashboardLayout component and layout patterns used in the Dashboard Module. The layout provides a responsive, role-based navigation system with sidebar, header, and search functionality.

## DashboardLayout Component

### Location

`frontend/src/layouts/DashboardLayout.vue`

### Purpose

Unified layout wrapper that integrates Sidebar, Header, and SearchModal components. Provides consistent navigation structure across all dashboard pages (Admin, Staff, Customer).

### Features

- ✅ Responsive sidebar (desktop/mobile)
- ✅ Fixed header with breadcrumbs
- ✅ Global search modal (⌘K shortcut)
- ✅ Role-based menu items
- ✅ Mobile overlay with backdrop
- ✅ localStorage state persistence
- ✅ Keyboard shortcuts

### Component Structure

```vue
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Desktop Sidebar -->
    <Sidebar />

    <!-- Mobile Sidebar Overlay -->
    <div v-if="showMobileMenu" class="overlay">
      <Sidebar />
    </div>

    <!-- Header -->
    <Header />

    <!-- Main Content -->
    <main class="main-content">
      <slot></slot>
    </main>

    <!-- Search Modal -->
    <SearchModal :show="showSearch" />
  </div>
</template>
```

## Usage

### Basic Implementation

```vue
<script setup>
import DashboardLayout from "@/layouts/DashboardLayout.vue";
</script>

<template>
  <DashboardLayout>
    <!-- Your page content here -->
    <h1>Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Widgets, charts, etc. -->
    </div>
  </DashboardLayout>
</template>
```

### With Role-Based Content

```vue
<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import DashboardLayout from "@/layouts/DashboardLayout.vue";

const authStore = useAuthStore();
const userRole = computed(() => authStore.user?.role || "customer");
</script>

<template>
  <DashboardLayout>
    <div v-if="userRole === 'admin'">
      <!-- Admin-specific content -->
    </div>
    <div v-else-if="userRole === 'staff'">
      <!-- Staff-specific content -->
    </div>
    <div v-else>
      <!-- Customer content -->
    </div>
  </DashboardLayout>
</template>
```

## Layout Configuration

### Role-Based Menu Items

#### Admin Menu

```javascript
const adminMenuItems = [
  {
    icon: LayoutDashboard,
    label: "Dashboard",
    to: "/admin/dashboard",
  },
  {
    icon: Users,
    label: "Users",
    to: "/admin/users",
  },
  {
    icon: Package,
    label: "Products",
    to: "/admin/products",
  },
  {
    icon: ShoppingCart,
    label: "Orders",
    to: "/admin/orders",
    badge: 5,
    badgeColor: "#CF0D0F",
  },
  {
    icon: Warehouse,
    label: "Inventory",
    to: "/admin/inventory",
  },
  {
    icon: BarChart3,
    label: "Reports",
    to: "/admin/reports",
  },
];
```

#### Staff Menu

```javascript
const staffMenuItems = [
  {
    icon: LayoutDashboard,
    label: "Dashboard",
    to: "/staff/dashboard",
  },
  {
    icon: ShoppingCart,
    label: "Orders",
    to: "/staff/orders",
    badge: 3,
  },
  {
    icon: Truck,
    label: "Deliveries",
    to: "/staff/deliveries",
    badge: 2,
  },
  {
    icon: Warehouse,
    label: "Inventory",
    to: "/staff/inventory",
  },
];
```

#### Customer Menu

```javascript
const customerMenuItems = [
  {
    icon: LayoutDashboard,
    label: "Dashboard",
    to: "/customer/dashboard",
  },
  {
    icon: ShoppingBag,
    label: "Shop",
    to: "/shop",
  },
  {
    icon: Package,
    label: "Orders",
    to: "/customer/orders",
  },
  {
    icon: Heart,
    label: "Wishlist",
    to: "/customer/wishlist",
  },
  {
    icon: User,
    label: "Profile",
    to: "/customer/profile",
  },
];
```

## State Management

### Sidebar State

```javascript
const sidebarCollapsed = ref(false);

// Load from localStorage on mount
onMounted(() => {
  const saved = localStorage.getItem("sidebar-collapsed");
  if (saved !== null) {
    sidebarCollapsed.value = saved === "true";
  }
});

// Toggle function
function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value;
}
```

### Mobile Menu State

```javascript
const showMobileMenu = ref(false);

// Toggle mobile menu
function toggleMobileMenu() {
  showMobileMenu.value = !showMobileMenu.value;
}

// Close mobile menu on route change
watch(
  () => route.path,
  () => {
    showMobileMenu.value = false;
  }
);
```

### Search Modal State

```javascript
const showSearch = ref(false);

// Keyboard shortcut (⌘K or Ctrl+K)
function handleKeydown(event) {
  if ((event.metaKey || event.ctrlKey) && event.key === "k") {
    event.preventDefault();
    showSearch.value = !showSearch.value;
  }
}

// Register keyboard listener
onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeydown);
});
```

## Responsive Behavior

### Breakpoints

| Breakpoint | Width    | Behavior                               |
| ---------- | -------- | -------------------------------------- |
| Mobile     | < 1024px | Sidebar hidden, hamburger menu shown   |
| Desktop    | ≥ 1024px | Sidebar visible, hamburger menu hidden |

### Mobile Layout

```css
/* Mobile (< lg) */
- Sidebar: Overlay with backdrop
- Header: Full width with hamburger
- Content: Full width padding
```

### Desktop Layout

```css
/* Desktop (≥ lg) */
- Sidebar: Fixed left (w-64 or w-16)
- Header: Fixed top with left margin
- Content: Left margin matches sidebar width
```

## Styling Classes

### Main Container

```html
<div class="min-h-screen bg-gray-50">
  <!-- Ensures full viewport height -->
  <!-- Light gray background -->
</div>
```

### Content Area

```html
<main
  class="pt-16 transition-all duration-300"
  :class="sidebarCollapsed ? 'ml-16' : 'ml-64'"
>
  <!-- Top padding for fixed header -->
  <!-- Dynamic left margin for sidebar -->
</main>
```

### Mobile Overlay

```html
<div class="fixed inset-0 z-50 lg:hidden">
  <!-- Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50"
    @click="showMobileMenu = false"
  ></div>

  <!-- Sidebar -->
  <Sidebar :role="userRole" />
</div>
```

## Integration Examples

### Admin Dashboard Page

```vue
<script setup>
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import StatCard from "@/components/StatCard.vue";
import LineChart from "@/components/charts/LineChart.vue";
import { DollarSign, ShoppingCart, Users, Clock } from "lucide-vue-next";

// Dashboard data and logic
</script>

<template>
  <DashboardLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          :icon="DollarSign"
          label="Total Revenue"
          :value="125000"
          isCurrency
          :change="12.5"
          comparisonText="vs last month"
        />
        <!-- More stat cards -->
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg border-2 border-gray-200">
          <h2 class="text-lg font-semibold mb-4">Revenue Overview</h2>
          <LineChart :labels="labels" :data="revenueData" />
        </div>
        <!-- More charts -->
      </div>
    </div>
  </DashboardLayout>
</template>
```

### Staff Dashboard Page

```vue
<script setup>
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Package, Truck, CheckCircle, Clock } from "lucide-vue-next";

// Staff dashboard logic
</script>

<template>
  <DashboardLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Staff Dashboard</h1>

      <!-- Queue Status Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          :icon="Clock"
          label="Pending"
          :value="8"
          iconBackground="linear-gradient(135deg, #FFA500 0%, #FF8C00 100%)"
        />
        <!-- More queue cards -->
      </div>
    </div>
  </DashboardLayout>
</template>
```

### Customer Dashboard Page

```vue
<script setup>
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Package, Truck, Heart, MessageSquare } from "lucide-vue-next";

// Customer dashboard logic
</script>

<template>
  <DashboardLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">My Dashboard</h1>

      <!-- Customer Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard :icon="Package" label="Total Orders" :value="42" />
        <!-- More cards -->
      </div>

      <!-- Quick Actions -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <router-link to="/shop" class="quick-action-card">
          Shop Now
        </router-link>
        <!-- More actions -->
      </div>
    </div>
  </DashboardLayout>
</template>
```

## Keyboard Shortcuts

| Shortcut    | Action                  |
| ----------- | ----------------------- |
| ⌘K / Ctrl+K | Open search modal       |
| Escape      | Close search modal      |
| ↑ / ↓       | Navigate search results |
| Enter       | Select search result    |

## Accessibility

### Focus Management

- Tab navigation through menu items
- Keyboard shortcuts with focus handling
- Escape key to close overlays

### ARIA Labels

```html
<button aria-label="Toggle sidebar">
  <ChevronLeft />
</button>

<nav aria-label="Main navigation">
  <!-- Menu items -->
</nav>

<div role="search" aria-label="Global search">
  <!-- Search modal -->
</div>
```

## Performance Optimization

### Lazy Loading

```javascript
// Lazy load dashboard components
const AdminDashboard = defineAsyncComponent(() =>
  import("@/pages/admin/DashboardPage.vue")
);
```

### Memoization

```javascript
// Memoize computed menu items
const menuItems = computed(() => {
  const role = userRole.value;
  return getMenuItemsForRole(role);
});
```

## Best Practices

### 1. Content Organization

- Use consistent padding: `p-6`
- Grid layouts for responsive design
- White cards with border-2 for sections

### 2. Navigation

- Use exact route matching for active states
- Include role-based guards in routes
- Handle breadcrumbs dynamically

### 3. State Management

- Use composables for shared state
- Persist UI preferences to localStorage
- Handle loading/error states

### 4. Responsive Design

- Mobile-first approach
- Test at all breakpoints
- Optimize touch targets (min 44x44px)

## Common Patterns

### Widget Container

```html
<div
  class="bg-white p-6 rounded-lg border-2 border-gray-200 hover:shadow-lg transition-shadow"
>
  <h2 class="text-lg font-semibold text-gray-900 mb-4">Widget Title</h2>
  <!-- Widget content -->
</div>
```

### Action Button

```html
<button
  class="px-4 py-2 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white rounded-lg hover:shadow-lg transition-all"
>
  Action
</button>
```

### Info Card

```html
<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
  <p class="text-blue-700">Information message</p>
</div>
```

## Troubleshooting

### Sidebar Not Showing

- Check user role is set correctly
- Verify route configuration
- Ensure auth store is initialized

### Mobile Menu Not Closing

- Check click-away directive is registered
- Verify event handlers are bound
- Review z-index stacking

### Search Not Working

- Verify keyboard event listener is registered
- Check modal state management
- Ensure search data is loaded

## Migration Guide

### From Old Layout

```vue
<!-- Old (without DashboardLayout) -->
<template>
  <div>
    <nav><!-- Navigation --></nav>
    <header><!-- Header --></header>
    <main><!-- Content --></main>
  </div>
</template>

<!-- New (with DashboardLayout) -->
<template>
  <DashboardLayout>
    <!-- Content only -->
  </DashboardLayout>
</template>
```

## Future Enhancements

- [ ] Add customizable sidebar width
- [ ] Implement theme switcher (light/dark)
- [ ] Add sidebar pinning feature
- [ ] Support nested menu items
- [ ] Add breadcrumb customization
- [ ] Implement layout preferences
- [ ] Add mobile gestures (swipe to open/close)

## Resources

- [Navigation Components](./navigation.md)
- [Testing Documentation](./testing.md)
- [Dashboard Pages](../foundation/README.md)

## Summary

The DashboardLayout component provides a robust, responsive layout system for all dashboard pages. It handles:

- Role-based navigation
- Mobile/desktop responsiveness
- State persistence
- Keyboard shortcuts
- Search functionality

All dashboard pages should use this layout for consistent user experience and navigation.
