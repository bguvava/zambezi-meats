# User Management Components

Complete reference for all Vue.js components in the User Management Module.

## Component Overview

| Component         | Type         | File Path                                            |
| ----------------- | ------------ | ---------------------------------------------------- |
| UsersIndex        | View         | `frontend/src/views/admin/UsersIndex.vue`            |
| UserStatusBadge   | UI Component | `frontend/src/components/user/UserStatusBadge.vue`   |
| UserRoleBadge     | UI Component | `frontend/src/components/user/UserRoleBadge.vue`     |
| UserAvatar        | UI Component | `frontend/src/components/user/UserAvatar.vue`        |
| CreateUserModal   | Modal        | `frontend/src/components/user/CreateUserModal.vue`   |
| EditUserModal     | Modal        | `frontend/src/components/user/EditUserModal.vue`     |
| UserActivityModal | Modal        | `frontend/src/components/user/UserActivityModal.vue` |

---

## UsersIndex

Main view component for the user management page with full CRUD functionality.

### Location

```
frontend/src/views/admin/UsersIndex.vue
```

### Features

- User listing with pagination (15 per page)
- Real-time search (debounced 500ms)
- Role filter dropdown
- Status filter dropdown
- Create, edit, status change, password reset, view activity actions
- PDF export with current filters
- Responsive design with mobile support
- Loading states
- Empty state display

### Template Structure

```vue
<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with Add User button -->
    <!-- Filters (search, role, status) -->
    <!-- Actions (clear filters, export PDF) -->
    <!-- Data table with user rows -->
    <!-- Pagination controls -->
    <!-- Modals (create, edit, activity, status) -->
  </div>
</template>
```

### Key Methods

| Method                       | Description                               |
| ---------------------------- | ----------------------------------------- |
| `loadUsers()`                | Fetch users from API with current filters |
| `debouncedSearch()`          | Debounced search handler (500ms delay)    |
| `handleFilterChange()`       | Reload users when filters change          |
| `resetFilters()`             | Clear all filters and reload              |
| `changePage(page)`           | Navigate to specific page                 |
| `openCreateModal()`          | Show create user modal                    |
| `openEditModal(user)`        | Show edit user modal                      |
| `openActivityModal(user)`    | Show activity history modal               |
| `openStatusMenu(user)`       | Show status change menu                   |
| `handleStatusChange(status)` | Change user status                        |
| `handleResetPassword(user)`  | Reset user password                       |
| `exportToPDF()`              | Export users to PDF                       |
| `formatDate(dateString)`     | Format date for display                   |

### Dependencies

**Stores**:

- `useUserStore`: User state management

**Composables**:

- `useToast`: Toast notifications

**Components**:

- `UserAvatar`, `UserRoleBadge`, `UserStatusBadge`
- `CreateUserModal`, `EditUserModal`, `UserActivityModal`

**UI Libraries**:

- `@headlessui/vue`: Dialog, Menu components
- `@heroicons/vue/24/outline`: Icons

### Usage Example

```javascript
// In router/index.js
{
  path: '/admin/users',
  name: 'admin-users',
  component: () => import('@/views/admin/UsersIndex.vue'),
  meta: {
    requiresAuth: true,
    requiresAdmin: true,
    title: 'Users Management'
  }
}
```

### Events Emitted

None (root component).

### Computed Properties

| Property       | Type  | Description                           |
| -------------- | ----- | ------------------------------------- |
| `visiblePages` | Array | Page numbers to display in pagination |

---

## UserStatusBadge

Badge component displaying user status with color coding.

### Location

```
frontend/src/components/user/UserStatusBadge.vue
```

### Props

| Prop     | Type   | Required | Validator                         | Description |
| -------- | ------ | -------- | --------------------------------- | ----------- |
| `status` | String | Yes      | `active`, `suspended`, `inactive` | User status |

### Status Colors

| Status      | Background | Text       | Icon Color |
| ----------- | ---------- | ---------- | ---------- |
| `active`    | Green 100  | Green 800  | Green      |
| `suspended` | Yellow 100 | Yellow 800 | Yellow     |
| `inactive`  | Gray 100   | Gray 800   | Gray       |

### Template

```vue
<span
  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
>
  <svg class="..."><!-- Circle icon --></svg>
  {{ statusText }}
</span>
```

### Usage Example

```vue
<template>
  <UserStatusBadge :status="user.status" />
</template>

<script setup>
import UserStatusBadge from "@/components/user/UserStatusBadge.vue";

const user = {
  status: "active", // or 'suspended', 'inactive'
};
</script>
```

### Computed Properties

| Property        | Type   | Description                         |
| --------------- | ------ | ----------------------------------- |
| `statusClasses` | String | Tailwind classes for badge styling  |
| `statusText`    | String | Capitalized status text for display |

---

## UserRoleBadge

Badge component displaying user role with icon and color coding.

### Location

```
frontend/src/components/user/UserRoleBadge.vue
```

### Props

| Prop   | Type   | Required | Validator                    | Description |
| ------ | ------ | -------- | ---------------------------- | ----------- |
| `role` | String | Yes      | `admin`, `staff`, `customer` | User role   |

### Role Colors

| Role       | Background | Text       | Border     | Icon            |
| ---------- | ---------- | ---------- | ---------- | --------------- |
| `admin`    | Red 50     | Red 700    | Red 200    | ShieldCheckIcon |
| `staff`    | Blue 50    | Blue 700   | Blue 200   | UserGroupIcon   |
| `customer` | Purple 50  | Purple 700 | Purple 200 | UserIcon        |

### Template

```vue
<span
  class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium border"
>
  <component :is="roleIcon" class="..." />
  {{ roleText }}
</span>
```

### Usage Example

```vue
<template>
  <UserRoleBadge :role="user.role" />
</template>

<script setup>
import UserRoleBadge from "@/components/user/UserRoleBadge.vue";

const user = {
  role: "admin", // or 'staff', 'customer'
};
</script>
```

### Computed Properties

| Property      | Type      | Description                        |
| ------------- | --------- | ---------------------------------- |
| `roleClasses` | String    | Tailwind classes for badge styling |
| `roleText`    | String    | Capitalized role text for display  |
| `roleIcon`    | Component | Heroicon component for role        |

---

## UserAvatar

Avatar component with image support and initials fallback.

### Location

```
frontend/src/components/user/UserAvatar.vue
```

### Props

| Prop    | Type   | Required | Default | Validator                    | Description               |
| ------- | ------ | -------- | ------- | ---------------------------- | ------------------------- |
| `name`  | String | Yes      | -       | -                            | User's full name          |
| `src`   | String | No       | `null`  | -                            | Avatar image URL          |
| `size`  | String | No       | `'md'`  | `xs`, `sm`, `md`, `lg`, `xl` | Avatar size               |
| `color` | String | No       | `'red'` | -                            | Fallback background color |

### Size Classes

| Size | Dimensions   | Text Size |
| ---- | ------------ | --------- |
| `xs` | 6x6 (24px)   | xs        |
| `sm` | 8x8 (32px)   | sm        |
| `md` | 10x10 (40px) | base      |
| `lg` | 12x12 (48px) | lg        |
| `xl` | 16x16 (64px) | xl        |

### Color Options

Available colors: `red`, `blue`, `green`, `yellow`, `purple`, `gray`

### Template

```vue
<div class="inline-flex items-center justify-center rounded-full">
  <img v-if="src" :src="src" :alt="name" />
  <span v-else>{{ initials }}</span>
</div>
```

### Usage Example

```vue
<template>
  <!-- With image -->
  <UserAvatar name="John Doe" src="/avatars/john.jpg" size="lg" />

  <!-- Without image (initials) -->
  <UserAvatar name="Jane Smith" size="md" color="blue" />
</template>

<script setup>
import UserAvatar from "@/components/user/UserAvatar.vue";
</script>
```

### Methods

| Method               | Description                            |
| -------------------- | -------------------------------------- |
| `handleImageError()` | Handle image load error, show initials |

### Computed Properties

| Property       | Type   | Description                          |
| -------------- | ------ | ------------------------------------ |
| `initials`     | String | First letters of first and last name |
| `sizeClasses`  | String | Tailwind classes for avatar size     |
| `colorClasses` | String | Tailwind classes for fallback color  |

---

## CreateUserModal

Modal component for creating new users.

### Location

```
frontend/src/components/user/CreateUserModal.vue
```

### Props

| Prop   | Type    | Required | Default | Description      |
| ------ | ------- | -------- | ------- | ---------------- |
| `open` | Boolean | No       | `false` | Modal visibility |

### Events

| Event     | Payload  | Description                           |
| --------- | -------- | ------------------------------------- |
| `close`   | None     | Modal close requested                 |
| `created` | `Object` | User created successfully (user data) |

### Form Fields

| Field      | Type     | Validation       | Description                      |
| ---------- | -------- | ---------------- | -------------------------------- |
| `name`     | Text     | Required         | User's full name                 |
| `email`    | Email    | Required, unique | User's email address             |
| `password` | Password | Required, min:8  | User's password                  |
| `role`     | Select   | Required         | User role (admin/staff/customer) |

### Template Structure

```vue
<Dialog :show="open">
  <form @submit.prevent="handleSubmit">
    <!-- Name input -->
    <!-- Email input -->
    <!-- Password input (min 8 chars) -->
    <!-- Role select dropdown -->
    <!-- Submit and Cancel buttons -->
  </form>
</Dialog>
```

### Usage Example

```vue
<template>
  <button @click="showModal = true">Create User</button>

  <CreateUserModal
    :open="showModal"
    @close="showModal = false"
    @created="handleUserCreated"
  />
</template>

<script setup>
import { ref } from "vue";
import CreateUserModal from "@/components/user/CreateUserModal.vue";

const showModal = ref(false);

function handleUserCreated(user) {
  console.log("User created:", user);
  showModal.value = false;
  // Refresh user list
}
</script>
```

### Methods

| Method           | Description                      |
| ---------------- | -------------------------------- |
| `resetForm()`    | Clear all form fields            |
| `handleSubmit()` | Submit form, create user via API |

### State

| Property  | Type    | Description                             |
| --------- | ------- | --------------------------------------- |
| `loading` | Boolean | Form submission in progress             |
| `errors`  | Object  | Validation errors from API              |
| `form`    | Object  | Form data (name, email, password, role) |

---

## EditUserModal

Modal component for editing existing users.

### Location

```
frontend/src/components/user/EditUserModal.vue
```

### Props

| Prop   | Type    | Required | Default | Description         |
| ------ | ------- | -------- | ------- | ------------------- |
| `open` | Boolean | No       | `false` | Modal visibility    |
| `user` | Object  | No       | `null`  | User object to edit |

### Events

| Event     | Payload  | Description                           |
| --------- | -------- | ------------------------------------- |
| `close`   | None     | Modal close requested                 |
| `updated` | `Object` | User updated successfully (user data) |

### Form Fields

| Field   | Type   | Validation                     | Description      |
| ------- | ------ | ------------------------------ | ---------------- |
| `name`  | Text   | Required                       | User's full name |
| `email` | Email  | Required, unique (except self) | User's email     |
| `role`  | Select | Required                       | User role        |

**Note**: Password field is **not** included. Use Reset Password action instead.

### Template Structure

```vue
<Dialog :show="open">
  <form @submit.prevent="handleSubmit">
    <!-- Name input (pre-filled) -->
    <!-- Email input (pre-filled) -->
    <!-- Role select (pre-selected) -->
    <!-- Update and Cancel buttons -->
  </form>
</Dialog>
```

### Usage Example

```vue
<template>
  <button @click="openEdit(selectedUser)">Edit User</button>

  <EditUserModal
    :open="showEditModal"
    :user="selectedUser"
    @close="showEditModal = false"
    @updated="handleUserUpdated"
  />
</template>

<script setup>
import { ref } from "vue";
import EditUserModal from "@/components/user/EditUserModal.vue";

const showEditModal = ref(false);
const selectedUser = ref(null);

function openEdit(user) {
  selectedUser.value = user;
  showEditModal.value = true;
}

function handleUserUpdated(user) {
  console.log("User updated:", user);
  showEditModal.value = false;
  // Refresh user list
}
</script>
```

### Methods

| Method           | Description                      |
| ---------------- | -------------------------------- |
| `handleSubmit()` | Submit form, update user via API |

### Watchers

- **`user`**: Updates form when user prop changes
- **`open`**: Resets form when modal opens

### State

| Property  | Type    | Description                   |
| --------- | ------- | ----------------------------- |
| `loading` | Boolean | Form submission in progress   |
| `errors`  | Object  | Validation errors from API    |
| `form`    | Object  | Form data (name, email, role) |

---

## UserActivityModal

Modal component displaying user activity history with pagination.

### Location

```
frontend/src/components/user/UserActivityModal.vue
```

### Props

| Prop   | Type    | Required | Default | Description                    |
| ------ | ------- | -------- | ------- | ------------------------------ |
| `open` | Boolean | No       | `false` | Modal visibility               |
| `user` | Object  | No       | `null`  | User whose activity to display |

### Events

| Event   | Payload | Description           |
| ------- | ------- | --------------------- |
| `close` | None    | Modal close requested |

### Features

- Paginated activity logs (15 per page)
- Old/new value comparison display
- Formatted relative timestamps
- IP address display
- Loading state
- Empty state

### Template Structure

```vue
<Dialog :show="open">
  <div>
    <h3>Activity History - {{ user?.name }}</h3>
    
    <!-- Loading spinner -->
    <div v-if="loading">...</div>
    
    <!-- Activity list -->
    <div v-else-if="activities.length">
      <div v-for="activity in activities">
        <!-- Action, description, old/new values, IP, timestamp -->
      </div>
      <!-- Pagination controls -->
    </div>
    
    <!-- Empty state -->
    <div v-else>No activity found</div>
  </div>
</Dialog>
```

### Usage Example

```vue
<template>
  <button @click="openActivity(selectedUser)">View Activity</button>

  <UserActivityModal
    :open="showActivityModal"
    :user="selectedUser"
    @close="showActivityModal = false"
  />
</template>

<script setup>
import { ref } from "vue";
import UserActivityModal from "@/components/user/UserActivityModal.vue";

const showActivityModal = ref(false);
const selectedUser = ref(null);

function openActivity(user) {
  selectedUser.value = user;
  showActivityModal.value = true;
}
</script>
```

### Methods

| Method                   | Description                       |
| ------------------------ | --------------------------------- |
| `loadActivity()`         | Fetch activity logs from API      |
| `loadPage(page)`         | Load specific page of activity    |
| `formatDate(dateString)` | Format timestamp as relative time |
| `formatValue(value)`     | Format old/new values for display |

### Watchers

- **`open`**: Load activity when modal opens

### State

| Property      | Type    | Description                |
| ------------- | ------- | -------------------------- |
| `loading`     | Boolean | Activity fetch in progress |
| `activities`  | Array   | Activity log entries       |
| `currentPage` | Number  | Current page number        |
| `totalPages`  | Number  | Total number of pages      |

### Activity Log Entry Structure

```javascript
{
  id: 123,
  action: 'user.status_changed',
  description: 'User status changed from active to suspended',
  old_value: 'active',
  new_value: 'suspended',
  ip_address: '192.168.1.100',
  created_at: '2025-01-15T15:00:00.000000Z'
}
```

---

## Common Patterns

### Error Handling

All components handle API errors consistently:

```javascript
try {
  await someAction();
  toast.success("Success message");
} catch (error) {
  if (error.response?.data?.error?.errors) {
    // Validation errors
    errors.value = error.response.data.error.errors;
  } else {
    // General error
    toast.error(error.response?.data?.message || "Generic error");
  }
}
```

### Loading States

All components with async operations use loading states:

```vue
<button :disabled="loading">
  <span v-if="!loading">Submit</span>
  <span v-else class="flex items-center">
    <svg class="animate-spin ..."><!-- Spinner --></svg>
    Loading...
  </span>
</button>
```

### Form Validation

Forms display validation errors from API:

```vue
<input v-model="form.field" :class="{ 'border-red-300': errors.field }" />
<p v-if="errors.field" class="text-red-600">
  {{ errors.field[0] }}
</p>
```

---

## Styling

All components use Tailwind CSS with Zambezi Meats branding:

### Color Theme

- **Primary**: `#8B0000` (red-800)
- **Hover**: `#7F0000` (red-700)
- **Focus Ring**: red-800

### Typography

- **Headings**: font-semibold
- **Body**: font-medium/normal
- **Labels**: text-sm font-medium text-gray-700

### Spacing

- **Form fields**: mt-1, space-y-4
- **Buttons**: px-3 py-2 (sm), px-4 py-2 (base)
- **Modals**: p-4 sm:p-6

---

## Accessibility

Components follow accessibility best practices:

- **ARIA labels**: All interactive elements have labels
- **Keyboard navigation**: Full keyboard support
- **Focus indicators**: Visible focus rings
- **Screen reader support**: sr-only labels where needed
- **Semantic HTML**: Proper heading hierarchy

---

## Testing

### Component Testing Example

```javascript
import { mount } from "@vue/test-utils";
import UserStatusBadge from "@/components/user/UserStatusBadge.vue";

describe("UserStatusBadge", () => {
  it("renders active status correctly", () => {
    const wrapper = mount(UserStatusBadge, {
      props: { status: "active" },
    });

    expect(wrapper.text()).toContain("Active");
    expect(wrapper.classes()).toContain("bg-green-100");
  });

  it("validates status prop", () => {
    const validator = UserStatusBadge.props.status.validator;
    expect(validator("active")).toBe(true);
    expect(validator("invalid")).toBe(false);
  });
});
```

---

## Best Practices

1. **Always validate props**: Use validators for enum-like props
2. **Handle loading states**: Show spinners during async operations
3. **Display errors clearly**: Show validation errors near inputs
4. **Emit events**: Use events for parent communication
5. **Use composables**: Extract reusable logic (useToast, useAuth)
6. **Type safety**: Consider TypeScript for larger projects
7. **Accessibility**: Always include ARIA labels and keyboard support
8. **Responsive design**: Test on mobile/tablet/desktop
9. **Error boundaries**: Handle errors gracefully
10. **Performance**: Use debouncing for expensive operations

---

## Dependencies

### Required Packages

```json
{
  "vue": "^3.3.0",
  "pinia": "^2.1.0",
  "@headlessui/vue": "^1.7.0",
  "@heroicons/vue": "^2.0.0",
  "tailwindcss": "^3.3.0"
}
```

### Install

```bash
npm install @headlessui/vue @heroicons/vue
```

---

## Changelog

### Version 1.0.0 (January 2025)

- Initial release with 7 components
- Full CRUD functionality
- Activity logging display
- Responsive design
- Accessibility support

---

**Last Updated**: January 15, 2025  
**Component Library Version**: 1.0.0  
**Maintainer**: Zambezi Meats Development Team
