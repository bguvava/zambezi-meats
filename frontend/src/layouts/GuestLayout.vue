<script setup>
/**
 * Guest Layout
 *
 * Layout for public pages accessible without authentication.
 * Includes enhanced header with scroll effects and comprehensive footer.
 *
 * @requirement PROJ-INIT-012 Create base layout components
 * @requirement LAND-002 Header navigation component
 * @requirement LAND-010 Footer component
 */
import { RouterView } from 'vue-router'
import HeaderNav from '@/components/common/HeaderNav.vue'
import FooterSection from '@/components/common/FooterSection.vue'
import SessionWarningModal from '@/components/auth/SessionWarningModal.vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white">
    <!-- Enhanced Header Navigation -->
    <HeaderNav />

    <!-- Main Content -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Enhanced Footer -->
    <FooterSection />

    <!-- Session Warning Modal (for authenticated users) -->
    <SessionWarningModal v-if="authStore.isAuthenticated" />
  </div>
</template>
