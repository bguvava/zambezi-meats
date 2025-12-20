<template>
  <footer class="bg-white border-t border-gray-200 py-4 px-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
      <p class="text-sm text-gray-600">
        © {{ currentYear }} Zambezi Meats. All rights reserved.
      </p>
      <p class="text-sm text-gray-600">
        Developed with <span class="text-red-600">❤️</span> by
        <a href="https://bguvava.com" target="_blank" rel="noopener noreferrer"
          class="text-primary-700 hover:text-primary-900 font-medium transition-colors">
          bguvava
        </a>
      </p>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>
