<script setup>
/**
 * ScrollIndicator Component
 * 
 * Red progress bar at top of page showing scroll position
 */
import { ref, onMounted, onUnmounted } from 'vue'

const scrollProgress = ref(0)

const updateScrollProgress = () => {
  const scrollTop = window.scrollY
  const docHeight = document.documentElement.scrollHeight - window.innerHeight
  scrollProgress.value = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0
}

onMounted(() => {
  window.addEventListener('scroll', updateScrollProgress, { passive: true })
  updateScrollProgress()
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateScrollProgress)
})
</script>

<template>
  <div class="fixed top-0 left-0 right-0 z-[100] h-1 bg-gray-200/30">
    <div 
      class="h-full bg-gradient-to-r from-primary-600 to-primary-500 transition-all duration-100"
      :style="{ width: `${scrollProgress}%` }"
    />
  </div>
</template>
