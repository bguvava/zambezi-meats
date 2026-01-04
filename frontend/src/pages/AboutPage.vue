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
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
          <!-- Left Column -->
          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">Premium Quality Guaranteed</h3>
                <p class="text-gray-600 text-sm">Every cut is hand-selected from the finest Australian farms. We only stock what we'd serve our own families.</p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">Expert Butchers</h3>
                <p class="text-gray-600 text-sm">Our team brings over 30 years of combined experience. We know exactly how to prepare each cut for maximum flavor and tenderness.</p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">Always Fresh, Never Frozen</h3>
                <p class="text-gray-600 text-sm">Your meat goes from farm to table within days. We deliver fresh daily, maintaining the cold chain every step of the way.</p>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">100% Australian Sourced</h3>
                <p class="text-gray-600 text-sm">We partner exclusively with trusted local farms. Supporting Australian agriculture while ensuring the highest quality standards.</p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">Fast & Reliable Delivery</h3>
                <p class="text-gray-600 text-sm">Free delivery on orders over $100. Same-day delivery available for orders placed before 2pm within our service area.</p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-2">100% Satisfaction Guarantee</h3>
                <p class="text-gray-600 text-sm">Not happy with your order? We offer a full refund or replacement, no questions asked. Your satisfaction is our priority.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Benefits -->
        <div class="bg-primary-50 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-4 text-center">More Reasons to Choose Us</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
              <div class="text-2xl font-bold text-primary-600 mb-1">30+</div>
              <div class="text-sm text-gray-600">Years Experience</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-primary-600 mb-1">100%</div>
              <div class="text-sm text-gray-600">Australian Sourced</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-primary-600 mb-1">50km</div>
              <div class="text-sm text-gray-600">Delivery Radius</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-primary-600 mb-1">24/7</div>
              <div class="text-sm text-gray-600">Online Ordering</div>
            </div>
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
