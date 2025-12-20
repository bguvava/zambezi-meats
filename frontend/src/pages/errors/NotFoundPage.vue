<script setup>
/**
 * 404 Not Found Page
 */
import { RouterLink } from 'vue-router'
import { Home, ArrowLeft } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="text-center">
      <h1 class="text-9xl font-bold text-primary-700">404</h1>
      <h2 class="text-2xl font-semibold text-secondary-900 mt-4 mb-2">Page Not Found</h2>
      <p class="text-gray-600 mb-8 max-w-md">
        Sorry, the page you're looking for doesn't exist or has been moved.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink
          to="/"
          class="inline-flex items-center justify-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
        >
          <Home class="w-5 h-5 mr-2" />
          Go Home
        </RouterLink>
        <button
          @click="$router.back()"
          class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
        >
          <ArrowLeft class="w-5 h-5 mr-2" />
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>
