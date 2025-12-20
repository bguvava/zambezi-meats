<script setup>
/**
 * OrdersPage.vue
 * Customer order history page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-primary-700">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Orders</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-600">View and track your order history</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px]">
            <input 
              type="text" 
              placeholder="Search orders..." 
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
            />
          </div>
          <select disabled class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
            <option>All Status</option>
            <option>Pending</option>
            <option>Processing</option>
            <option>Delivered</option>
          </select>
          <select disabled class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
            <option>Last 30 days</option>
            <option>Last 3 months</option>
            <option>Last year</option>
          </select>
        </div>
      </div>

      <!-- Orders List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="text-center py-16">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">No Orders Yet</h2>
          <p class="text-gray-600 mb-6">
            You haven't placed any orders yet. Start shopping to see your orders here.
          </p>
          <RouterLink 
            to="/shop" 
            class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
          >
            Start Shopping
          </RouterLink>
        </div>

        <!-- Sample Order Row (preview) -->
        <div class="border-t border-gray-200 p-6 opacity-50">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-800">#ORD-XXXXXXXX</p>
              <p class="text-sm text-gray-500">Placed on January 1, 2025</p>
            </div>
            <div class="text-right">
              <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                Pending
              </span>
              <p class="text-sm text-gray-500 mt-1">3 items • $XXX.XX</p>
            </div>
          </div>
          <p class="text-xs text-gray-400 mt-2">Preview only</p>
        </div>
      </div>
    </div>
  </div>
</template>
