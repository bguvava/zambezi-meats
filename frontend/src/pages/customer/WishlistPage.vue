<script setup>
/**
 * WishlistPage.vue
 * Customer wishlist page showing saved products
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
          <span class="text-gray-900">Wishlist</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
        <p class="text-gray-600">Products you've saved for later</p>
      </div>

      <!-- Empty State -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center mb-8">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Your Wishlist is Empty</h2>
        <p class="text-gray-600 mb-6">
          Start adding products you love to your wishlist by clicking the heart icon.
        </p>
        <RouterLink 
          to="/shop" 
          class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
        >
          Browse Products
        </RouterLink>
      </div>

      <!-- Sample Wishlist Items (preview) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 opacity-50">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="relative">
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
              <span class="text-4xl">🥩</span>
            </div>
            <button disabled class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow cursor-not-allowed">
              <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>
          </div>
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Sample Product</h3>
            <p class="text-primary-700 font-semibold">$XX.XX / kg</p>
            <button disabled class="w-full mt-3 py-2 bg-gray-100 text-gray-400 text-sm rounded-lg cursor-not-allowed">
              Add to Cart
            </button>
          </div>
          <p class="text-xs text-gray-400 text-center pb-2">Preview only</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="relative">
            <div class="aspect-square bg-gray-100 flex items-center justify-center">
              <span class="text-4xl">🍗</span>
            </div>
            <button disabled class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow cursor-not-allowed">
              <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>
          </div>
          <div class="p-4">
            <h3 class="font-medium text-gray-800">Another Product</h3>
            <p class="text-primary-700 font-semibold">$XX.XX / kg</p>
            <button disabled class="w-full mt-3 py-2 bg-gray-100 text-gray-400 text-sm rounded-lg cursor-not-allowed">
              Add to Cart
            </button>
          </div>
          <p class="text-xs text-gray-400 text-center pb-2">Preview only</p>
        </div>
      </div>
    </div>
  </div>
</template>
