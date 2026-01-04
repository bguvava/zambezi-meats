<script setup>
/**
 * Hero Section Component
 *
 * Main landing page hero with animated background and CTA buttons.
 *
 * @requirement LAND-001 Hero section with animated background
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'

// Animation state
const isVisible = ref(false)

onMounted(() => {
  // Trigger entrance animation
  setTimeout(() => {
    isVisible.value = true
  }, 100)
})
</script>

<template>
  <section class="relative min-h-[90vh] flex items-center overflow-hidden">
    <!-- Background Image -->
    <div 
      class="absolute inset-0 bg-cover bg-center bg-no-repeat"
      style="background-image: url('/images/hero.jpg');"
    >
      <!-- Dark overlay for text readability -->
      <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-black/40"></div>
    </div>

    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <!-- Floating meat shapes -->
      <div class="absolute top-20 left-10 w-32 h-32 bg-primary-600/10 rounded-full blur-xl animate-float-slow"></div>
      <div class="absolute top-40 right-20 w-48 h-48 bg-primary-600/5 rounded-full blur-2xl animate-float-medium"></div>
      <div class="absolute bottom-20 left-1/4 w-64 h-64 bg-accent-100/5 rounded-full blur-3xl animate-float-fast"></div>
      <div class="absolute bottom-40 right-1/3 w-40 h-40 bg-primary-600/10 rounded-full blur-xl animate-float-slow"></div>
    </div>

    <!-- Content -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
      <div
        class="max-w-3xl transition-all duration-1000 ease-out"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <!-- Company Logo -->
        <div
          class="mb-8 transition-all duration-700 delay-50"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          <img 
            src="/images/official_logo_landscape_white.png" 
            alt="Zambezi Meats" 
            class="h-20 sm:h-28 lg:h-36 w-auto drop-shadow-2xl filter brightness-110 contrast-110"
          />
        </div>

        <!-- Badge -->
        <div
          class="inline-flex items-center gap-2 bg-primary-600/20 backdrop-blur-sm border border-primary-500/30 rounded-full px-4 py-2 mb-6 transition-all duration-700 delay-100"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          <span class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></span>
          <span class="text-primary-400 text-sm font-medium">Premium Australian Meats</span>
        </div>

        <!-- Heading -->
        <h1
          class="text-4xl sm:text-5xl lg:text-7xl font-bold text-white mb-6 leading-tight transition-all duration-700 delay-200"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          Quality Meats,
          <br />
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 via-primary-400 to-primary-300">
            Delivered Fresh
          </span>
        </h1>

        <!-- Subheading -->
        <p
          class="text-lg sm:text-xl text-gray-300 mb-8 max-w-2xl leading-relaxed transition-all duration-700 delay-300"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          From our family to yours. Experience the finest cuts of premium Australian meat,
          hand-selected by expert butchers and delivered fresh to your doorstep.
        </p>

        <!-- CTA Buttons -->
        <div
          class="flex flex-col sm:flex-row gap-4 transition-all duration-700 delay-400"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          <RouterLink
            to="/shop"
            class="group inline-flex items-center justify-center gap-3 px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all duration-300 shadow-lg shadow-primary-600/25 hover:shadow-xl hover:shadow-primary-600/30 hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span>Shop Now</span>
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </RouterLink>

          <RouterLink
            to="/about"
            class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-300 hover:-translate-y-0.5"
          >
            <span>Learn More</span>
          </RouterLink>
        </div>

        <!-- Trust Badges -->
        <div
          class="flex flex-wrap items-center gap-6 mt-12 pt-8 border-t border-white/10 transition-all duration-700 delay-500"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
        >
          <div class="flex items-center gap-2 text-gray-400">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">Free Delivery over $100</span>
          </div>
          <div class="flex items-center gap-2 text-gray-400">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">Same-Day Delivery</span>
          </div>
          <div class="flex items-center gap-2 text-gray-400">
            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">100% Quality Guaranteed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-white/50 animate-bounce">
      <span class="text-xs tracking-wider uppercase">Scroll</span>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>
  </section>
</template>

<style scoped>
@keyframes float-slow {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes float-medium {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-30px) rotate(-5deg); }
}

@keyframes float-fast {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(3deg); }
}

.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-medium {
  animation: float-medium 6s ease-in-out infinite;
}

.animate-float-fast {
  animation: float-fast 4s ease-in-out infinite;
}
</style>
