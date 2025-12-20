<script setup>
/**
 * Staff Layout
 *
 * Dashboard layout for staff members with order and delivery management.
 *
 * @requirement PROJ-INIT-012 Create base layout components
 */
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoutConfirmModal from '@/components/common/LogoutConfirmModal.vue'
import SessionWarningModal from '@/components/auth/SessionWarningModal.vue'
import DashboardFooter from '@/components/common/DashboardFooter.vue'
import {
  Home,
  Package,
  Truck,
  LogOut,
  Menu,
  X,
  ChevronLeft,
  User,
  Bell,
  Calendar,
  Layers,
  ChevronDown,
  Boxes,
  Trash2,
  Activity,
  ClipboardList,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const showLogoutModal = ref(false)

const navigation = [
  { name: 'Dashboard', href: '/staff', icon: Home },
  { name: 'Order Queue', href: '/staff/orders', icon: ClipboardList },
  { name: 'My Deliveries', href: '/staff/deliveries', icon: Truck },
  { name: 'Stock Check', href: '/staff/stock', icon: Boxes },
  { name: 'Waste Log', href: '/staff/waste', icon: Trash2 },
  { name: 'My Activity', href: '/staff/activity', icon: Activity },
]

function promptLogout() {
  showLogoutModal.value = true
}

async function handleLogout() {
  showLogoutModal.value = false
  await authStore.logout()
  // Redirect to homepage after logout
  router.push('/')
}

function cancelLogout() {
  showLogoutModal.value = false
}

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 h-16">
      <div class="flex items-center justify-between h-full px-4">
        <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-900">
          <Menu class="w-6 h-6" />
        </button>
        <span class="font-bold text-secondary-900">Staff Portal</span>
        <button class="p-2 text-gray-600 hover:text-gray-900 relative">
          <Bell class="w-6 h-6" />
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
      </div>
    </header>

    <!-- Sidebar Overlay -->
    <div v-if="isSidebarOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <aside :class="[
      'fixed top-0 left-0 z-50 h-full w-64 bg-secondary-900 text-white transform transition-transform duration-200 ease-in-out',
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]">
      <!-- Sidebar Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-secondary-800">
        <div class="flex items-center space-x-2">
          <img src="/images/logo.png" alt="Zambezi Meats" class="w-8 h-8 rounded-full object-contain" />
          <div>
            <span class="font-bold text-white text-sm">Zambezi Meats</span>
            <span class="block text-xs text-gray-400">Staff Portal</span>
          </div>
        </div>
        <button @click="toggleSidebar" class="lg:hidden p-2 text-gray-400 hover:text-white">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- User Info -->
      <div class="p-4 border-b border-secondary-800">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-primary-700/30 rounded-full flex items-center justify-center">
            <User class="w-5 h-5 text-primary-400" />
          </div>
          <div>
            <p class="font-medium text-white">{{ authStore.userName }}</p>
            <p class="text-sm text-gray-400">Staff Member</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1">
        <RouterLink v-for="item in navigation" :key="item.name" :to="item.href"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-secondary-800 hover:text-white transition-colors"
          active-class="bg-primary-700 text-white" @click="isSidebarOpen = false">
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.name }}</span>
        </RouterLink>
      </nav>

      <!-- Quick Actions -->
      <div class="p-4 border-t border-secondary-800">
        <RouterLink to="/shop" class="flex items-center space-x-2 text-gray-400 hover:text-white text-sm">
          <ChevronLeft class="w-4 h-4" />
          <span>View Store</span>
        </RouterLink>
      </div>

      <!-- Logout -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-secondary-800">
        <button @click="promptLogout"
          class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-lg text-gray-300 hover:bg-red-600/20 hover:text-red-400 transition-colors">
          <LogOut class="w-5 h-5" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 lg:pt-0 min-h-screen">
      <!-- Desktop Header -->
      <header class="hidden lg:flex h-16 items-center justify-between px-8 bg-white border-b border-gray-200">
        <h1 class="text-xl font-semibold text-secondary-900">Staff Dashboard</h1>
        <div class="flex items-center space-x-4">
          <button class="p-2 text-gray-600 hover:text-gray-900 relative">
            <Bell class="w-5 h-5" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
        </div>
      </header>

      <div class="p-4 sm:p-6 lg:p-8">
        <RouterView />
      </div>

      <!-- Dashboard Footer -->
      <DashboardFooter />
    </main>

    <!-- Session Warning Modal -->
    <SessionWarningModal />

    <!-- Logout Confirmation Modal -->
    <LogoutConfirmModal :is-open="showLogoutModal" @confirm="handleLogout" @cancel="cancelLogout" />
  </div>
</template>
