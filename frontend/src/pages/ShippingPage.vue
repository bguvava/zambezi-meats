<script setup>
/**
 * ShippingPage.vue
 * Shipping and Returns policy page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Shipping & Returns</h1>
        <p class="text-lg text-gray-600">Our shipping and returns policies explained</p>
      </div>

      <div class="space-y-8">
        <!-- Shipping Policy -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Shipping Policy</h2>
          <div class="prose prose-gray max-w-none space-y-4">
            <h3 class="text-lg font-medium text-gray-800">Processing Time</h3>
            <p class="text-gray-600">
              Orders are typically processed within 24 hours of being placed. Orders placed on weekends 
              or public holidays will be processed on the next business day.
            </p>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Delivery Timeframes</h3>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
              <li><strong>Engadine & Menangle Park:</strong> Same day delivery for orders before 2 PM</li>
              <li><strong>City of Campbelltown:</strong> Next business day delivery</li>
              <li><strong>Greater Sydney:</strong> 1-2 business days</li>
            </ul>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Shipping Costs</h3>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
              <li>Orders under $100: $9.95 delivery fee</li>
              <li>Orders $100 - $199: $5.95 delivery fee</li>
              <li>Orders $200 and above: <span class="text-green-600 font-medium">FREE delivery</span></li>
            </ul>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Delivery Requirements</h3>
            <p class="text-gray-600">
              Due to the perishable nature of our products, someone must be present to receive the delivery. 
              We'll notify you via SMS or email when your delivery is on its way.
            </p>
          </div>
        </section>

        <!-- Cold Chain -->
        <section class="bg-primary-50 rounded-lg p-8 border border-primary-100">
          <div class="flex items-start">
            <svg class="w-8 h-8 text-primary-600 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-2">Temperature-Controlled Shipping</h3>
              <p class="text-gray-600">
                All meat products are vacuum-sealed and shipped in insulated packaging with ice packs. 
                This ensures your order stays fresh and safe during transit.
              </p>
            </div>
          </div>
        </section>

        <!-- Returns Policy -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Returns & Refunds</h2>
          <div class="prose prose-gray max-w-none space-y-4">
            <h3 class="text-lg font-medium text-gray-800">Quality Guarantee</h3>
            <p class="text-gray-600">
              We stand behind the quality of our products. If you receive an item that doesn't meet 
              our standards, we'll make it right.
            </p>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Reporting Issues</h3>
            <p class="text-gray-600">
              If you're unsatisfied with your order, please contact us within 24 hours of delivery. 
              Please include:
            </p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
              <li>Your order number</li>
              <li>Photos of the product and packaging</li>
              <li>A description of the issue</li>
            </ul>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Refund Process</h3>
            <p class="text-gray-600">
              Once we've reviewed your claim, we'll process a refund or replacement within 3-5 business days. 
              Refunds will be credited to your original payment method.
            </p>

            <h3 class="text-lg font-medium text-gray-800 mt-6">Non-Returnable Items</h3>
            <p class="text-gray-600">
              Due to food safety regulations, we cannot accept physical returns of meat products. 
              Please dispose of any unsatisfactory items safely.
            </p>
          </div>
        </section>

        <!-- Order Cancellation -->
        <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Order Cancellation</h2>
          <div class="prose prose-gray max-w-none">
            <p class="text-gray-600">
              Orders can be cancelled within 2 hours of being placed for a full refund. After this window, 
              please contact us as soon as possible. We'll do our best to accommodate cancellation requests, 
              but once an order is packed, cancellation may not be possible.
            </p>
          </div>
        </section>

        <!-- Contact -->
        <section class="bg-gray-100 rounded-lg p-6">
          <p class="text-gray-600 text-center">
            Have questions about shipping or returns? 
            <RouterLink to="/contact" class="text-primary-600 hover:text-primary-700 font-medium">Contact our support team</RouterLink>
          </p>
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
