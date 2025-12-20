<script setup>
/**
 * ProfilePage.vue
 * Customer profile management page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-primary-700">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Profile</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600">Manage your personal information and preferences</p>
      </div>

      <!-- Profile Form -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <!-- Avatar Section -->
        <div class="flex items-center space-x-6 mb-8 pb-8 border-b border-gray-200">
          <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center">
            <span class="text-4xl">👤</span>
          </div>
          <div>
            <h3 class="font-medium text-gray-800">Profile Photo</h3>
            <p class="text-sm text-gray-500 mb-2">JPG, PNG or GIF. Max 2MB</p>
            <button 
              disabled
              class="px-4 py-2 bg-gray-100 text-gray-400 text-sm rounded-lg cursor-not-allowed"
            >
              Change Photo
            </button>
          </div>
        </div>

        <!-- Form Fields -->
        <form class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
              <input 
                type="text" 
                disabled
                placeholder="John"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input 
                type="text" 
                disabled
                placeholder="Doe"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input 
              type="email" 
              disabled
              placeholder="john@example.com"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <input 
              type="tel" 
              disabled
              placeholder="+263 XXX XXX XXX"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
            />
          </div>

          <div class="pt-6 border-t border-gray-200">
            <h3 class="font-medium text-gray-800 mb-4">Change Password</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input 
                  type="password" 
                  disabled
                  placeholder="••••••••"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                  <input 
                    type="password" 
                    disabled
                    placeholder="••••••••"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                  <input 
                    type="password" 
                    disabled
                    placeholder="••••••••"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-6">
            <button 
              type="button"
              disabled
              class="px-6 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
