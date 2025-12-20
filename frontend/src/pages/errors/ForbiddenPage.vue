<script setup>
/**
 * 403 Forbidden Page
 */
import { RouterLink } from 'vue-router'
import { Home, Shield } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="text-center">
      <div class="w-24 h-24 mx-auto mb-6 bg-red-100 rounded-full flex items-center justify-center">
        <Shield class="w-12 h-12 text-red-600" />
      </div>
      <h1 class="text-4xl font-bold text-secondary-900 mb-2">Access Denied</h1>
      <p class="text-gray-600 mb-8 max-w-md">
        You don't have permission to access this page. Please contact an administrator if you believe this is an error.
      </p>
      <RouterLink
        to="/"
        class="inline-flex items-center justify-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
      >
        <Home class="w-5 h-5 mr-2" />
        Go Home
      </RouterLink>
    </div>
  </div>
</template>
