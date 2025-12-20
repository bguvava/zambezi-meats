<script setup>
/**
 * AddressesPage.vue
 * Customer delivery addresses management page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-primary-700">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Addresses</span>
        </nav>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Addresses</h1>
            <p class="text-gray-600">Manage your delivery addresses</p>
          </div>
          <button 
            disabled
            class="px-4 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
          >
            + Add Address
          </button>
        </div>
      </div>

      <!-- Addresses Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Empty State -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">No Addresses Saved</h2>
          <p class="text-gray-600 mb-6">
            Add a delivery address to make checkout faster.
          </p>
          <button 
            disabled
            class="inline-flex items-center px-6 py-3 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
          >
            Add Your First Address
          </button>
        </div>

        <!-- Sample Address Cards (preview) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 opacity-50">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
              <span class="inline-block px-2 py-1 bg-primary-100 text-primary-800 text-xs rounded mr-2">Default</span>
              <span class="font-medium text-gray-800">Home</span>
            </div>
            <div class="flex space-x-2">
              <button disabled class="text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </button>
              <button disabled class="text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <p class="text-gray-600">
            John Doe<br />
            123 Main Street<br />
            Harare, Zimbabwe<br />
            +263 XXX XXX XXX
          </p>
          <p class="text-xs text-gray-400 mt-2">Preview only</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 opacity-50">
          <div class="flex items-start justify-between mb-4">
            <span class="font-medium text-gray-800">Office</span>
            <div class="flex space-x-2">
              <button disabled class="text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </button>
              <button disabled class="text-gray-400 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <p class="text-gray-600">
            John Doe<br />
            456 Office Park<br />
            Harare, Zimbabwe<br />
            +263 XXX XXX XXX
          </p>
          <p class="text-xs text-gray-400 mt-2">Preview only</p>
        </div>
      </div>
    </div>
  </div>
</template>
