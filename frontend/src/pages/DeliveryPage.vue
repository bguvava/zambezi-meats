<script setup>
/**
 * DeliveryPage.vue
 * Delivery information page with zones, times, and policies
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Delivery Information</h1>
        <p class="text-lg text-gray-600">Everything you need to know about our delivery service</p>
      </div>

      <div class="space-y-8">
        <!-- Delivery Areas -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">Delivery Areas</h2>
          </div>
          <div class="prose prose-gray max-w-none">
            <p class="text-gray-600 mb-4">We currently deliver to the following areas in New South Wales:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
              <li><strong>Engadine</strong> - Same day delivery available</li>
              <li><strong>Menangle Park</strong> - Same day delivery available</li>
              <li><strong>City of Campbelltown</strong> - Next day delivery</li>
              <li><strong>Greater Sydney</strong> - 1-2 business days</li>
            </ul>
            <p class="text-gray-600 mt-4">Don't see your area? <RouterLink to="/contact" class="text-primary-600 hover:text-primary-700">Contact us</RouterLink> to check availability.</p>
          </div>
        </section>

        <!-- Delivery Times -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">Delivery Times</h2>
          </div>
          <div class="prose prose-gray max-w-none">
            <p class="text-gray-600 mb-4">Our delivery hours are:</p>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
              <div class="flex justify-between items-center text-gray-700">
                <span class="font-medium">Monday - Sunday</span>
                <span>7:00 AM - 6:00 PM AEST</span>
              </div>
            </div>
            <p class="text-gray-600">Orders placed before 2:00 PM may be eligible for same-day delivery in select areas.</p>
          </div>
        </section>

        <!-- Delivery Fees -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">Delivery Fees</h2>
          </div>
          <div class="prose prose-gray max-w-none">
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Orders under $100</span>
                <span class="font-semibold text-gray-800">$9.95</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Orders $100 - $199</span>
                <span class="font-semibold text-gray-800">$5.95</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">Orders $200+</span>
                <span class="font-semibold text-green-600">FREE</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Cold Chain Guarantee -->
        <section class="bg-primary-50 rounded-lg p-8 border border-primary-100">
          <div class="flex items-center mb-4">
            <svg class="w-8 h-8 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <h2 class="text-xl font-semibold text-gray-800">Cold Chain Guarantee</h2>
          </div>
          <p class="text-gray-600">
            All our meat products are delivered in temperature-controlled packaging to ensure freshness and safety. 
            Our insulated boxes with ice packs maintain the cold chain from our facility to your door.
          </p>
        </section>

        <!-- Special Instructions -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Delivery Instructions</h2>
          <div class="prose prose-gray max-w-none">
            <ul class="list-disc list-inside text-gray-600 space-y-2">
              <li>Someone must be present to receive the delivery</li>
              <li>Please ensure your contact number is correct for delivery updates</li>
              <li>Add any gate codes or special instructions during checkout</li>
              <li>Products should be refrigerated immediately upon receipt</li>
            </ul>
          </div>
        </section>
      </div>

      <!-- Back Link -->
      <div class="mt-12 text-center">
        <RouterLink 
          to="/" 
          class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Home
        </RouterLink>
      </div>
    </div>
  </div>
</template>
