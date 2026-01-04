<script setup>
/**
 * Zambezi Meats - Root Application Component
 *
 * Main app wrapper with toast notifications, cart panel, session management, and router view.
 *
 * @requirement PROJ-INIT-002 Create Vue.js 3 + Vite frontend project
 * @requirement CART-010 Slide-out cart panel for quick cart access
 * @requirement AUTH-004 5-minute session timeout with 30-second warning
 */
import { computed, onMounted, onUnmounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { Toaster } from 'vue-sonner'
import CartPanel from '@/components/shop/CartPanel.vue'
import ScrollIndicator from '@/components/common/ScrollIndicator.vue'
import ScrollToTop from '@/components/common/ScrollToTop.vue'
import WhatsAppButton from '@/components/common/WhatsAppButton.vue'
import SessionWarningModal from '@/components/auth/SessionWarningModal.vue'
import LockScreen from '@/components/auth/LockScreen.vue'
import { useAuth } from '@/composables/useAuth'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()
const { isAuthenticated, setupActivityListeners } = useAuth()

// Session activity tracking cleanup function
let cleanupActivity = null

onMounted(() => {
  // Setup activity listeners for session management when authenticated
  if (isAuthenticated.value) {
    cleanupActivity = setupActivityListeners()
  }
})

onUnmounted(() => {
  if (cleanupActivity) {
    cleanupActivity()
  }
})

// Handle lock screen unlock
const handleUnlock = async () => {
  // Session is unlocked, no additional action needed
  // The unlock was handled in the LockScreen component
}

// Handle logout from lock screen
const handleLogoutFromLock = async () => {
  await authStore.logout(true, 'Logged out from lock screen')
}

// Hide floating buttons on dashboard routes
const isDashboardRoute = computed(() => {
  return route.path.startsWith('/admin') ||
    route.path.startsWith('/staff') ||
    route.path.startsWith('/customer')
})
</script>

<template>
  <!-- Scroll Progress Indicator (Red bar at top) -->
  <ScrollIndicator />

  <!-- Toast Notifications -->
  <Toaster
    position="top-right"
    :toastOptions="{
      duration: 4000,
      className: 'font-sans',
    }"
    richColors
    closeButton
  />

  <!-- Main Router View -->
  <RouterView />

  <!-- Global Cart Panel (Slide-out) -->
  <CartPanel />

  <!-- Session Warning Modal (appears when session is about to expire) -->
  <SessionWarningModal v-if="isAuthenticated" />

  <!-- Lock Screen (appears after 5 minutes of inactivity) -->
  <LockScreen 
    v-if="isAuthenticated"
    :show="authStore.sessionLocked"
    @unlock="handleUnlock"
    @logout="handleLogoutFromLock"
  />

  <!-- Scroll to Top Button (Bottom Right) - Hidden on dashboards -->
  <ScrollToTop v-if="!isDashboardRoute" />

  <!-- WhatsApp Floating Button (Bottom Left) - Hidden on dashboards -->
  <WhatsAppButton v-if="!isDashboardRoute" />
</template>

<style>
/* Global app styles - inherited from style.css */
</style>
