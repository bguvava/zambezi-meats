<script setup>
/**
 * DashboardLayout.vue
 * Main layout with sidebar, header, and mobile menu
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Sidebar from '@/components/navigation/Sidebar.vue'
import MenuItem from '@/components/navigation/MenuItem.vue'
import Header from '@/components/navigation/Header.vue'
import SearchModal from '@/components/navigation/SearchModal.vue'
import {
  LayoutDashboard,
  Users,
  Package,
  ShoppingCart,
  Warehouse,
  BarChart3,
  TruckIcon,
  ClipboardList,
  User,
  Heart,
  ShoppingBag
} from 'lucide-vue-next'

const authStore = useAuthStore()

const sidebarCollapsed = ref(false)
const showMobileMenu = ref(false)
const showSearch = ref(false)

// Get user role
const userRole = computed(() => {
  return authStore.user?.role || 'customer'
})

// Menu items based on role
const menuItems = computed(() => {
  const items = {
    admin: [
      { icon: LayoutDashboard, label: 'Dashboard', to: '/admin/dashboard', badge: null },
      { icon: Users, label: 'Users', to: '/admin/users', badge: null },
      { icon: Package, label: 'Products', to: '/admin/products', badge: null },
      { icon: ShoppingCart, label: 'Orders', to: '/admin/orders', badge: 5 },
      { icon: Warehouse, label: 'Inventory', to: '/admin/inventory', badge: null },
      { icon: BarChart3, label: 'Reports', to: '/admin/reports', badge: null }
    ],
    staff: [
      { icon: LayoutDashboard, label: 'Dashboard', to: '/staff/dashboard', badge: null },
      { icon: ClipboardList, label: 'Orders', to: '/staff/orders', badge: 3 },
      { icon: TruckIcon, label: 'Deliveries', to: '/staff/deliveries', badge: 2 },
      { icon: Warehouse, label: 'Inventory', to: '/staff/inventory', badge: null }
    ],
    customer: [
      { icon: LayoutDashboard, label: 'Dashboard', to: '/customer/dashboard', badge: null },
      { icon: ShoppingBag, label: 'Shop', to: '/shop', badge: null },
      { icon: ShoppingCart, label: 'Orders', to: '/customer/orders', badge: null },
      { icon: Heart, label: 'Wishlist', to: '/customer/wishlist', badge: null },
      { icon: User, label: 'Profile', to: '/customer/profile', badge: null }
    ]
  }

  return items[userRole.value] || items.customer
})

// Load sidebar state from localStorage
onMounted(() => {
  const saved = localStorage.getItem('sidebar-collapsed')
  if (saved !== null) {
    sidebarCollapsed.value = saved === 'true'
  }

  // Add keyboard shortcut for search (Cmd+K or Ctrl+K)
  window.addEventListener('keydown', handleKeyboardShortcuts)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyboardShortcuts)
})

// Keyboard shortcuts
function handleKeyboardShortcuts(event) {
  // Cmd+K or Ctrl+K for search
  if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
    event.preventDefault()
    showSearch.value = !showSearch.value
  }
}

// Update sidebar state
function updateSidebarState(collapsed) {
  sidebarCollapsed.value = collapsed
}

// Close mobile menu when clicking backdrop
function closeMobileMenu() {
  showMobileMenu.value = false
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Desktop Sidebar -->
    <div class="hidden lg:block">
      <Sidebar :role="userRole" @update:collapsed="updateSidebarState">
        <template #menu="{ isCollapsed }">
          <MenuItem
            v-for="item in menuItems"
            :key="item.to"
            :icon="item.icon"
            :label="item.label"
            :to="item.to"
            :badge="item.badge"
            :is-collapsed="isCollapsed"
          />
        </template>
      </Sidebar>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <transition name="backdrop">
      <div
        v-if="showMobileMenu"
        class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
        @click="closeMobileMenu"
      ></div>
    </transition>

    <!-- Mobile Sidebar -->
    <transition name="slide">
      <div v-if="showMobileMenu" class="lg:hidden fixed left-0 top-0 bottom-0 w-64 bg-white z-50 shadow-xl">
        <Sidebar :role="userRole">
          <template #menu="{ isCollapsed }">
            <MenuItem
              v-for="item in menuItems"
              :key="item.to"
              :icon="item.icon"
              :label="item.label"
              :to="item.to"
              :badge="item.badge"
              :is-collapsed="false"
              @click="closeMobileMenu"
            />
          </template>
        </Sidebar>
      </div>
    </transition>

    <!-- Header -->
    <Header
      :sidebar-collapsed="sidebarCollapsed"
      :show-mobile-menu="showMobileMenu"
      @toggle-mobile-menu="showMobileMenu = !showMobileMenu"
      @toggle-search="showSearch = !showSearch"
    />

    <!-- Main Content -->
    <main
      class="pt-16 transition-all duration-300"
      :class="[
        sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'
      ]"
    >
      <slot></slot>
    </main>

    <!-- Search Modal -->
    <SearchModal :show="showSearch" @close="showSearch = false" />
  </div>
</template>

<style scoped>
.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
