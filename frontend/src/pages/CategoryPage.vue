<script setup>
/**
 * CategoryPage.vue
 * Category listing page showing products in a specific category
 */
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const categorySlug = route.params.slug
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Breadcrumb -->
      <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <RouterLink to="/" class="text-gray-500 hover:text-primary-600">Home</RouterLink>
          </li>
          <li class="text-gray-400">/</li>
          <li>
            <RouterLink to="/shop" class="text-gray-500 hover:text-primary-600">Shop</RouterLink>
          </li>
          <li class="text-gray-400">/</li>
          <li class="text-gray-900 font-medium capitalize">{{ categorySlug || 'Category' }}</li>
        </ol>
      </nav>

      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4 capitalize">
          {{ categorySlug || 'Category' }}
        </h1>
        <p class="text-lg text-gray-600">Browse our selection of quality products</p>
      </div>

      <!-- Placeholder Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center">
          <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <h2 class="text-2xl font-semibold text-gray-800 mb-2">Category Products</h2>
          <p class="text-gray-600 mb-6">
            Browse products in this category. Features will include subcategory navigation,
            product filtering, and sorting options.
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <RouterLink 
              to="/shop" 
              class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors"
            >
              View All Products
            </RouterLink>
            <RouterLink 
              to="/" 
              class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
            >
              Back to Home
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Sample Categories -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Sample Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <RouterLink 
            to="/category/beef"
            class="bg-white rounded-lg p-4 border border-gray-200 text-center hover:border-primary-300 transition-colors"
          >
            <span class="text-2xl mb-2 block">🥩</span>
            <span class="font-medium text-gray-800">Beef</span>
          </RouterLink>
          <RouterLink 
            to="/category/pork"
            class="bg-white rounded-lg p-4 border border-gray-200 text-center hover:border-primary-300 transition-colors"
          >
            <span class="text-2xl mb-2 block">🐷</span>
            <span class="font-medium text-gray-800">Pork</span>
          </RouterLink>
          <RouterLink 
            to="/category/chicken"
            class="bg-white rounded-lg p-4 border border-gray-200 text-center hover:border-primary-300 transition-colors"
          >
            <span class="text-2xl mb-2 block">🍗</span>
            <span class="font-medium text-gray-800">Chicken</span>
          </RouterLink>
          <RouterLink 
            to="/category/specialty"
            class="bg-white rounded-lg p-4 border border-gray-200 text-center hover:border-primary-300 transition-colors"
          >
            <span class="text-2xl mb-2 block">⭐</span>
            <span class="font-medium text-gray-800">Specialty</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
