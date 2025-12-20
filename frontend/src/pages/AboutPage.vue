<script setup>
/**
 * AboutPage.vue
 * About page with Zambezi Meats company information and branding
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary-700 to-primary-900 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">About Zambezi Meats</h1>
        <p class="text-xl text-primary-100 max-w-2xl mx-auto">
          Premium quality meats, delivered fresh to your door
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Content Sections -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
        <!-- Our Story -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Story</h2>
          <p class="text-gray-600 mb-4">
            Zambezi Meats was founded with a passion for delivering premium quality 
            meats to families across the region. Our commitment to excellence starts 
            with sourcing the finest cuts from trusted local farmers.
          </p>
          <p class="text-gray-600">
            More details about our journey, heritage, and commitment to quality 
            will be featured here.
          </p>
        </div>

        <!-- Our Values -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Values</h2>
          <ul class="space-y-3 text-gray-600">
            <li class="flex items-start">
              <span class="text-primary-600 mr-2">✓</span>
              <span><strong>Quality:</strong> Only the finest cuts make it to your table</span>
            </li>
            <li class="flex items-start">
              <span class="text-primary-600 mr-2">✓</span>
              <span><strong>Freshness:</strong> Delivered fresh, never frozen</span>
            </li>
            <li class="flex items-start">
              <span class="text-primary-600 mr-2">✓</span>
              <span><strong>Trust:</strong> Transparent sourcing and practices</span>
            </li>
            <li class="flex items-start">
              <span class="text-primary-600 mr-2">✓</span>
              <span><strong>Service:</strong> Customer satisfaction is our priority</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Why Choose Us -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Why Choose Zambezi Meats?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="text-4xl mb-4">🥩</div>
            <h3 class="font-semibold text-gray-800 mb-2">Premium Cuts</h3>
            <p class="text-gray-600 text-sm">Hand-selected premium cuts from trusted suppliers</p>
          </div>
          <div class="text-center">
            <div class="text-4xl mb-4">🚚</div>
            <h3 class="font-semibold text-gray-800 mb-2">Fast Delivery</h3>
            <p class="text-gray-600 text-sm">Quick and reliable delivery to your doorstep</p>
          </div>
          <div class="text-center">
            <div class="text-4xl mb-4">💯</div>
            <h3 class="font-semibold text-gray-800 mb-2">Satisfaction Guaranteed</h3>
            <p class="text-gray-600 text-sm">100% satisfaction guarantee on all products</p>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Ready to try our products?</h3>
        <div class="flex flex-wrap justify-center gap-4">
          <RouterLink 
            to="/shop" 
            class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
          >
            Shop Now
          </RouterLink>
          <RouterLink 
            to="/contact" 
            class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
          >
            Contact Us
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
