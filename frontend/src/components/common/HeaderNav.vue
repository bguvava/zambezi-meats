<script setup>
/**
 * Header Navigation Component
 *
 * Main navigation header with mobile menu, cart, and user account.
 *
 * @requirement LAND-002 Header with navigation and cart
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import CurrencySwitcher from './CurrencySwitcher.vue'

const route = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()

const isMobileMenuOpen = ref(false)
const isScrolled = ref(false)
const isUserMenuOpen = ref(false)

const navLinks = [
  { name: 'Home', to: '/' },
  { name: 'Shop', to: '/shop' },
  { name: 'About', to: '/about' },
  { name: 'Blog', to: '/blog' },
  { name: 'Contact', to: '/contact' }
]

const categoryLinks = [
  { name: 'Beef', to: '/category/beef' },
  { name: 'Lamb', to: '/category/lamb' },
  { name: 'Poultry', to: '/category/poultry' },
  { name: 'Pork', to: '/category/pork' },
  { name: 'Specialty', to: '/category/specialty' }
]

const isLoggedIn = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const cartItemCount = computed(() => cartStore?.itemCount ?? 0)
const cartTotal = computed(() => cartStore?.formattedTotal ?? '$0.00')

// Get correct dashboard route based on user role
const dashboardRoute = computed(() => {
  if (authStore.isAdmin) return '/admin'
  if (authStore.isStaff) return '/staff'
  return '/customer'
})

// Get correct logo based on styling
const logoSrc = computed(() => {
  // On homepage before scrolling, use white landscape logo
  // After scrolling or on other pages, use dark landscape logo
  return useDarkStyling.value 
    ? '/images/logo-landscape.png' 
    : '/images/logo-landscape-white.png'
})

// Pages with dark hero sections that allow transparent header
const darkHeroPages = ['/']

// Determine if header should use solid background (non-home pages need solid bg)
const needsSolidHeader = computed(() => !darkHeroPages.includes(route.path))

// Header should use dark styling (solid bg) when scrolled OR on non-dark-hero pages
const useDarkStyling = computed(() => isScrolled.value || needsSolidHeader.value)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  if (isMobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
  document.body.style.overflow = ''
}

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const closeUserMenu = () => {
  isUserMenuOpen.value = false
}

const handleLogout = async () => {
  closeUserMenu()
  await authStore.logout()
}

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

// Close menus when clicking outside
const handleClickOutside = (event) => {
  if (isUserMenuOpen.value && !event.target.closest('.user-menu-container')) {
    closeUserMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" :class="[
    useDarkStyling
      ? 'bg-white shadow-md'
      : 'bg-transparent'
  ]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 md:h-20">
        <!-- Logo -->
        <RouterLink to="/" class="flex-shrink-0 flex items-center gap-2">
          <img :src="logoSrc" alt="Zambezi Meats"
            class="h-10 md:h-12 object-contain" />
        </RouterLink>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center gap-8">
          <RouterLink v-for="link in navLinks" :key="link.name" :to="link.to"
            class="text-sm font-medium transition-colors relative group" :class="[
              useDarkStyling ? 'text-gray-700 hover:text-primary-600' : 'text-white/90 hover:text-white',
              route.path === link.to ? (useDarkStyling ? 'text-primary-600' : 'text-white') : ''
            ]">
            {{ link.name }}
            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-primary-600 transition-all group-hover:w-full"
              :class="{ 'w-full': route.path === link.to }" />
          </RouterLink>
        </nav>

        <!-- Right Side Actions -->
        <div class="flex items-center gap-4">
          <!-- Currency Switcher -->
          <CurrencySwitcher :theme="useDarkStyling ? 'light' : 'dark'" />

          <!-- Search Button -->
          <button class="p-2 transition-colors"
            :class="useDarkStyling ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>

          <!-- Cart Button -->
          <button @click="cartStore.toggleCart()" class="p-2 relative transition-colors"
            :class="useDarkStyling ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'"
            aria-label="Open cart">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span v-if="cartItemCount > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs font-bold rounded-full flex items-center justify-center">
              {{ cartItemCount > 99 ? '99+' : cartItemCount }}
            </span>
          </button>

          <!-- User Menu (Desktop) -->
          <div v-if="isLoggedIn" class="hidden md:block relative user-menu-container">
            <button @click="toggleUserMenu" class="flex items-center gap-2 p-2 transition-colors"
              :class="useDarkStyling ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ user?.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- User Dropdown -->
            <Transition enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95">
              <div v-if="isUserMenuOpen" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl py-2">
                <div class="px-4 py-2 border-b">
                  <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                  <p class="text-xs text-gray-500">{{ user?.email }}</p>
                </div>
                <RouterLink :to="dashboardRoute" @click="closeUserMenu"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Dashboard
                </RouterLink>
                <RouterLink :to="`${dashboardRoute}/orders`" @click="closeUserMenu"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  My Orders
                </RouterLink>
                <RouterLink :to="`${dashboardRoute}/profile`" @click="closeUserMenu"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Profile Settings
                </RouterLink>
                <div class="border-t mt-2 pt-2">
                  <button @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    Sign Out
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Login/Register (Desktop) -->
          <div v-else class="hidden md:flex items-center gap-3">
            <RouterLink to="/login" class="text-sm font-medium transition-colors"
              :class="useDarkStyling ? 'text-gray-700 hover:text-primary-600' : 'text-white/90 hover:text-white'">
              Login
            </RouterLink>
            <RouterLink to="/register"
              class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
              Register
            </RouterLink>
          </div>

          <!-- Mobile Menu Button -->
          <button @click="toggleMobileMenu" class="lg:hidden p-2 transition-colors"
            :class="useDarkStyling ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
            <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-4"
      enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">
      <div v-if="isMobileMenuOpen" class="lg:hidden bg-white shadow-xl">
        <div class="max-w-7xl mx-auto px-4 py-6">
          <!-- Main Nav Links -->
          <nav class="space-y-1">
            <RouterLink v-for="link in navLinks" :key="link.name" :to="link.to" @click="closeMobileMenu"
              class="block px-4 py-3 text-gray-700 hover:bg-primary-50 hover:text-primary-600 rounded-lg transition-colors"
              :class="{ 'bg-primary-50 text-primary-600': route.path === link.to }">
              {{ link.name }}
            </RouterLink>
          </nav>

          <!-- Auth Actions -->
          <div class="mt-6 pt-6 border-t">
            <template v-if="isLoggedIn">
              <div class="flex items-center gap-3 px-4 mb-4">
                <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-medium">
                    {{ user?.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ user?.name }}</p>
                  <p class="text-sm text-gray-500">{{ user?.email }}</p>
                </div>
              </div>
              <div class="space-y-1">
                <RouterLink :to="dashboardRoute" @click="closeMobileMenu"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                  Dashboard
                </RouterLink>
                <RouterLink :to="`${dashboardRoute}/orders`" @click="closeMobileMenu"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                  My Orders
                </RouterLink>
                <button @click="handleLogout; closeMobileMenu()"
                  class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                  Sign Out
                </button>
              </div>
            </template>
            <template v-else>
              <div class="flex gap-3">
                <RouterLink to="/login" @click="closeMobileMenu"
                  class="flex-1 px-4 py-3 text-center border border-primary-600 text-primary-600 font-medium rounded-lg hover:bg-primary-50 transition-colors">
                  Login
                </RouterLink>
                <RouterLink to="/register" @click="closeMobileMenu"
                  class="flex-1 px-4 py-3 text-center bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                  Register
                </RouterLink>
              </div>
            </template>
          </div>
        </div>
      </div>
    </Transition>
  </header>

  <!-- Spacer for fixed header -->
  <div class="h-16 md:h-20" />
</template>
