<script setup>
/**
 * SuccessPage.vue
 * Order confirmation/success page after successful checkout
 */
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const orderId = route.query.order || 'XXXXXXXX'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Success Icon -->
      <div class="text-center mb-8">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
        <p class="text-lg text-gray-600">Thank you for your order</p>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
        <div class="text-center border-b border-gray-200 pb-6 mb-6">
          <p class="text-sm text-gray-500 mb-1">Order Number</p>
          <p class="text-2xl font-bold text-gray-900">{{ orderId }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Order Summary -->
          <div>
            <h3 class="font-semibold text-gray-800 mb-4">Order Summary</h3>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center space-x-4 mb-4 opacity-50">
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                  <span class="text-xl">🥩</span>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-800">Sample Product</p>
                  <p class="text-sm text-gray-500">1 kg × 1</p>
                </div>
                <p class="font-medium text-gray-800">$XX.XX</p>
              </div>
              <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Subtotal</span>
                  <span>$XX.XX</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Delivery</span>
                  <span>$X.XX</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t">
                  <span>Total</span>
                  <span>$XX.XX</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Delivery Details -->
          <div>
            <h3 class="font-semibold text-gray-800 mb-4">Delivery Details</h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-4">
              <div>
                <p class="text-sm text-gray-500 mb-1">Delivery Address</p>
                <p class="text-gray-800">
                  123 Main Street<br />
                  Harare, Zimbabwe
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500 mb-1">Delivery Date</p>
                <p class="text-gray-800">To be scheduled</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 mb-1">Payment Method</p>
                <p class="text-gray-800">Cash on Delivery</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- What's Next -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
        <h3 class="font-semibold text-gray-800 mb-4">What's Next?</h3>
        <div class="space-y-4">
          <div class="flex items-start space-x-4">
            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="text-primary-600 font-semibold text-sm">1</span>
            </div>
            <div>
              <p class="font-medium text-gray-800">Order Confirmation Email</p>
              <p class="text-sm text-gray-600">You'll receive an email with your order details</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="text-primary-600 font-semibold text-sm">2</span>
            </div>
            <div>
              <p class="font-medium text-gray-800">Order Preparation</p>
              <p class="text-sm text-gray-600">We'll prepare your order with care</p>
            </div>
          </div>
          <div class="flex items-start space-x-4">
            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="text-primary-600 font-semibold text-sm">3</span>
            </div>
            <div>
              <p class="font-medium text-gray-800">Delivery</p>
              <p class="text-sm text-gray-600">Your order will be delivered at the scheduled time</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap justify-center gap-4">
        <RouterLink 
          to="/customer/orders" 
          class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
        >
          View My Orders
        </RouterLink>
        <RouterLink 
          to="/shop" 
          class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
        >
          Continue Shopping
        </RouterLink>
      </div>
    </div>
  </div>
</template>
