# Navigation System Documentation

**Phase 6: Dashboard Module - Navigation Components**  
**Date**: December 19, 2024  
**Status**: ✅ COMPLETED

---

## Overview

The navigation system provides a comprehensive, role-based interface with:

- **Collapsible Sidebar**: Desktop navigation with localStorage persistence
- **Header**: Global actions, search, notifications, user menu
- **SearchModal**: Global search with keyboard shortcuts
- **DashboardLayout**: Integrated layout component
- **Mobile Responsive**: Overlay menu for mobile devices

---

## Components

### 1. Sidebar.vue

**Location**: `frontend/src/components/navigation/Sidebar.vue`

**Purpose**: Main navigation sidebar with collapsible functionality

**Features**:

- Width: `w-64` (expanded) → `w-16` (collapsed)
- Logo section with "ZM" branding
- Menu items via slot
- Collapse button at bottom
- localStorage persistence (key: `sidebar-collapsed`)
- Smooth transitions (300ms)
- Red hover states (#CF0D0F)

**Props**:

```javascript
{
  role: {
    type: String,
    required: true,
    validator: (value) => ['admin', 'staff', 'customer'].includes(value)
  }
}
```

**Slots**:

```html
<template #menu="{ isCollapsed }">
  <!-- MenuItem components go here -->
</template>
```

**Usage**:

```vue
<Sidebar role="admin">
  <template #menu="{ isCollapsed }">
    <MenuItem
      :icon="LayoutDashboard"
      label="Dashboard"
      to="/admin/dashboard"
      :is-collapsed="isCollapsed"
    />
  </template>
</Sidebar>
```

**State Management**:

- Collapse state stored in localStorage
- Emits `update:collapsed` event when toggled

---

### 2. MenuItem.vue

**Location**: `frontend/src/components/navigation/MenuItem.vue`

**Purpose**: Individual navigation menu item with active state

**Features**:

- Icon from lucide-vue-next
- Label (hidden when collapsed)
- Badge support (hidden when collapsed)
- Active route highlighting
- Red gradient background when active
- Hover effects

**Props**:

```javascript
{
  icon: Object,              // Lucide icon component
  label: String,             // Menu item label
  to: String,                // Vue Router path
  badge: [Number, String],   // Optional badge (e.g., notification count)
  badgeColor: String,        // Badge background color
  roles: Array,              // Allowed roles (future use)
  isCollapsed: Boolean       // Sidebar collapse state
}
```

**Active State**:

- Checks current route with `useRoute()`
- Matches exact path or starts with path
- Applies red gradient background and white text

**Styling**:

- Active: `bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white border-l-4`
- Inactive: `text-gray-700 hover:bg-gray-100 hover:text-[#CF0D0F]`
- Smooth transitions on all states

---

### 3. Header.vue

**Location**: `frontend/src/components/navigation/Header.vue`

**Purpose**: Top header with global actions and user menu

**Features**:

- Fixed position with dynamic left margin
- Hamburger menu button (mobile only)
- Breadcrumb navigation
- Search button (triggers SearchModal)
- Notifications dropdown with badge
- Theme toggle (light/dark)
- User dropdown menu

**Props**:

```javascript
{
  sidebarCollapsed: Boolean,  // Sidebar state for positioning
  showMobileMenu: Boolean     // Mobile menu state
}
```

**Events**:

```javascript
emit("toggle-mobile-menu"); // Toggle mobile sidebar
emit("toggle-search"); // Open search modal
```

**Dropdowns**:

1. **Notifications**:

   - Badge shows unread count
   - Mock notifications (replace with real data)
   - "View all notifications" link

2. **User Menu**:
   - User name and role
   - Profile link
   - Settings link
   - Logout button

**Positioning**:

- Left margin adjusts based on sidebar state:
  - Collapsed: `left: 4rem` (w-16)
  - Expanded: `left: 16rem` (w-64)

---

### 4. SearchModal.vue

**Location**: `frontend/src/components/navigation/SearchModal.vue`

**Purpose**: Global search with keyboard navigation

**Features**:

- Keyboard shortcut: `⌘K` or `Ctrl+K`
- Search products, orders, customers
- Recent searches (localStorage)
- Keyboard navigation (arrows, enter, escape)
- Mock results (replace with API calls)

**Props**:

```javascript
{
  show: Boolean; // Modal visibility
}
```

**Events**:

```javascript
emit("close"); // Close modal
```

**Keyboard Controls**:

- `Escape`: Close modal
- `ArrowDown`: Navigate down results
- `ArrowUp`: Navigate up results
- `Enter`: Select highlighted result
- `⌘K` / `Ctrl+K`: Toggle modal

**localStorage**:

- Key: `recent-searches`
- Stores last 5 searches
- Clear button to remove all

**Search Results**:

```javascript
{
  type: 'product' | 'order' | 'customer',
  title: String,
  subtitle: String,
  path: String  // Router path
}
```

---

### 5. DashboardLayout.vue

**Location**: `frontend/src/layouts/DashboardLayout.vue`

**Purpose**: Complete dashboard layout with all navigation components

**Features**:

- Desktop sidebar (hidden on mobile)
- Mobile sidebar overlay
- Header with dynamic positioning
- Main content area with dynamic margin
- SearchModal integration
- Keyboard shortcuts

**Role-Based Menus**:

**Admin Menu**:

- Dashboard
- Users
- Products
- Orders (badge: 5)
- Inventory
- Reports

**Staff Menu**:

- Dashboard
- Orders (badge: 3)
- Deliveries (badge: 2)
- Inventory

**Customer Menu**:

- Dashboard
- Shop
- Orders
- Wishlist
- Profile

**Usage**:

```vue
<DashboardLayout>
  <YourPageContent />
</DashboardLayout>
```

**Mobile Behavior**:

- Hamburger button in header
- Sidebar slides in from left
- Backdrop overlay (click to close)
- Smooth animations

---

## Keyboard Shortcuts

| Shortcut        | Action                  |
| --------------- | ----------------------- |
| `⌘K` / `Ctrl+K` | Open search modal       |
| `Escape`        | Close modal/dropdown    |
| `↑` `↓`         | Navigate search results |
| `Enter`         | Select search result    |

---

## Color Scheme

**Primary Colors**:

- `#CF0D0F`: Primary red
- `#F6211F`: Secondary red
- `#EFEFEF`: Light gray
- `#6F6F6F`: Medium gray
- `#4D4B4C`: Dark gray

**Active States**:

- Gradient: `from-[#CF0D0F] to-[#F6211F]`
- Text: White
- Border: Red (`border-l-4 border-[#CF0D0F]`)

**Hover States**:

- Background: `hover:bg-gray-100`
- Text: `hover:text-[#CF0D0F]`
- Buttons: `hover:bg-[#CF0D0F] hover:text-white`

---

## localStorage Keys

| Key                 | Value                 | Purpose                |
| ------------------- | --------------------- | ---------------------- |
| `sidebar-collapsed` | `'true'` \| `'false'` | Sidebar collapse state |
| `recent-searches`   | `JSON` array          | Last 5 searches        |

---

## Transitions

**Sidebar**:

```css
transition: all 300ms ease-in-out;
```

**Dropdowns**:

```css
opacity: 0 → 1 (200ms)
transform: translateY(-10px) → translateY(0)
```

**Mobile Sidebar**:

```css
transform: translateX(-100%) → translateX(0) (300ms);
```

**Fade Elements** (labels, badges):

```css
opacity: 0 → 1 (200ms);
```

---

## Mobile Responsive Breakpoints

| Breakpoint        | Behavior                             |
| ----------------- | ------------------------------------ |
| `< 1024px` (< lg) | Hide desktop sidebar, show hamburger |
| `>= 1024px` (lg+) | Show desktop sidebar, hide hamburger |

---

## Integration Example

```vue
<template>
  <DashboardLayout>
    <div class="p-8">
      <h1>Your Dashboard Content</h1>
      <!-- Page content here -->
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from "@/layouts/DashboardLayout.vue";
</script>
```

---

## Click-Away Directive

**Location**: `frontend/src/directives/clickAway.js`

**Purpose**: Close dropdowns when clicking outside

**Registration**:

```javascript
// main.js
import { clickAway } from "./directives/clickAway";
app.directive("click-away", clickAway);
```

**Usage**:

```vue
<div v-if="showDropdown" v-click-away="closeDropdown">
  <!-- Dropdown content -->
</div>
```

---

## Future Enhancements

1. **Real API Integration**:

   - Replace mock search results with actual API calls
   - Real notifications from backend
   - User profile data

2. **Advanced Search**:

   - Filters (type, date range)
   - Search history with timestamps
   - Search suggestions

3. **Nested Menus**:

   - Expandable menu sections
   - Sub-menu support in MenuItem

4. **Customization**:

   - User-configurable menu order
   - Pinned items
   - Custom shortcuts

5. **Accessibility**:
   - ARIA labels
   - Screen reader support
   - Focus management

---

## Testing Checklist

- [ ] Sidebar collapse/expand works
- [ ] localStorage persistence
- [ ] Active route highlighting
- [ ] Mobile overlay opens/closes
- [ ] Hamburger button triggers mobile menu
- [ ] Search modal opens with ⌘K
- [ ] Keyboard navigation in search
- [ ] Dropdowns close on click-away
- [ ] Breadcrumbs update correctly
- [ ] Role-based menus display correctly
- [ ] Badge numbers display
- [ ] Theme toggle works
- [ ] User menu actions work

---

## Dependencies

```json
{
  "vue": "^3.x",
  "vue-router": "^4.x",
  "pinia": "^2.x",
  "lucide-vue-next": "^0.x"
}
```

---

**Last Updated**: December 19, 2024  
**Next**: Write comprehensive tests for all navigation components
