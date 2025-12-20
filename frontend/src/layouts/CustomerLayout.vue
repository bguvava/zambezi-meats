<script setup>
/**
 * Customer Layout
 *
 * Dashboard layout for authenticated customers with sidebar navigation.
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
  ShoppingBag,
  Package,
  User,
  MapPin,
  Heart,
  Bell,
  MessageCircle,
  LogOut,
  Menu,
  X,
  ChevronLeft,
  Calendar,
  ChevronDown,
  ChevronRight,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const showLogoutModal = ref(false)

const navigation = [
  { name: 'Dashboard', href: '/customer', icon: Home },
  { name: 'My Orders', href: '/customer/orders', icon: Package },
  { name: 'My Profile', href: '/customer/profile', icon: User },
  { name: 'My Addresses', href: '/customer/addresses', icon: MapPin },
  { name: 'Wishlist', href: '/customer/wishlist', icon: Heart },
  { name: 'Notifications', href: '/customer/notifications', icon: Bell },
  { name: 'Support', href: '/customer/support', icon: MessageCircle },
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
        <RouterLink to="/" class="flex items-center space-x-2">
          <img src="/images/logo.png" alt="Zambezi Meats" class="w-8 h-8 rounded-full object-contain" />
          <span class="font-bold text-secondary-900">Zambezi Meats</span>
        </RouterLink>
        <div class="w-10"></div>
      </div>
    </header>

    <!-- Sidebar Overlay -->
    <div v-if="isSidebarOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <aside :class="[
      'fixed top-0 left-0 z-50 h-full w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 ease-in-out',
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]">
      <!-- Sidebar Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200">
        <RouterLink to="/" class="flex items-center space-x-2">
          <img src="/images/logo.png" alt="Zambezi Meats" class="w-8 h-8 rounded-full object-contain" />
          <span class="font-bold text-secondary-900">Zambezi Meats</span>
        </RouterLink>
        <button @click="toggleSidebar" class="lg:hidden p-2 text-gray-600 hover:text-gray-900">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Back to Shop -->
      <div class="p-4 border-b border-gray-200">
        <RouterLink to="/shop"
          class="flex items-center space-x-2 text-primary-700 hover:text-primary-800 text-sm font-medium">
          <ChevronLeft class="w-4 h-4" />
          <span>Back to Shop</span>
        </RouterLink>
      </div>

      <!-- User Info -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
            <User class="w-5 h-5 text-primary-700" />
          </div>
          <div>
            <p class="font-medium text-secondary-900">{{ authStore.userName }}</p>
            <p class="text-sm text-gray-500">Customer</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1">
        <RouterLink v-for="item in navigation" :key="item.name" :to="item.href"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
          active-class="bg-primary-50 text-primary-700 font-medium" @click="isSidebarOpen = false">
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.name }}</span>
        </RouterLink>
      </nav>

      <!-- Logout -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
        <button @click="promptLogout"
          class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
          <LogOut class="w-5 h-5" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 lg:pt-0 min-h-screen">
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
