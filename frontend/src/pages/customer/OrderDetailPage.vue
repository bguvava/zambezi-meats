<script setup>
/**
 * OrderDetailPage.vue
 * Individual order detail page with tracking
 */
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const orderId = route.params.id
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-primary-700">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <RouterLink to="/customer/orders" class="text-gray-500 hover:text-primary-700">Orders</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">{{ orderId || 'Order' }}</span>
        </nav>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ orderId || 'XXXXXXXX' }}</h1>
            <p class="text-gray-600">Placed on January 1, 2025</p>
          </div>
          <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">
            Pending
          </span>
        </div>
      </div>

      <!-- Order Tracking -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Order Status</h2>
        
        <div class="flex items-center justify-between">
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-primary-700 rounded-full flex items-center justify-center text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-800 mt-2">Confirmed</p>
          </div>
          <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 mt-2">Preparing</p>
          </div>
          <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 mt-2">Out for Delivery</p>
          </div>
          <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 mt-2">Delivered</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h2>
            
            <div class="space-y-4 opacity-50">
              <div class="flex items-center space-x-4 pb-4 border-b border-gray-100">
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                  <span class="text-xl">🥩</span>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-800">Sample Product</p>
                  <p class="text-sm text-gray-500">1 kg × 2</p>
                </div>
                <p class="font-medium text-gray-800">$XX.XX</p>
              </div>
              <div class="flex items-center space-x-4 pb-4 border-b border-gray-100">
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                  <span class="text-xl">🍗</span>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-800">Another Product</p>
                  <p class="text-sm text-gray-500">500g × 1</p>
                </div>
                <p class="font-medium text-gray-800">$XX.XX</p>
              </div>
              <p class="text-xs text-gray-400">Preview only</p>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
            <div class="space-y-3">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>$XX.XX</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Delivery</span>
                <span>$X.XX</span>
              </div>
              <div class="border-t pt-3 flex justify-between font-semibold text-gray-900">
                <span>Total</span>
                <span>$XX.XX</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Delivery Address</h2>
            <p class="text-gray-600">
              123 Main Street<br />
              Harare, Zimbabwe<br />
              +263 XXX XXX XXX
            </p>
          </div>
        </div>
      </div>

      <!-- Back Link -->
      <div class="mt-8">
        <RouterLink 
          to="/customer/orders" 
          class="inline-flex items-center text-primary-700 hover:text-primary-800 font-medium"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Orders
        </RouterLink>
      </div>
    </div>
  </div>
</template>
