<script setup>
/**
 * CheckoutPage.vue
 * Multi-step checkout process page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Checkout</h1>
        <p class="text-lg text-gray-600">Complete your order</p>
      </div>

      <!-- Checkout Steps Preview -->
      <div class="mb-8">
        <div class="flex items-center justify-center">
          <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-700 text-white font-semibold">
              1
            </div>
            <span class="ml-2 text-sm font-medium text-gray-900">Delivery</span>
          </div>
          <div class="w-16 h-0.5 bg-gray-300 mx-4"></div>
          <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
              2
            </div>
            <span class="ml-2 text-sm font-medium text-gray-500">Schedule</span>
          </div>
          <div class="w-16 h-0.5 bg-gray-300 mx-4"></div>
          <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
              3
            </div>
            <span class="ml-2 text-sm font-medium text-gray-500">Payment</span>
          </div>
          <div class="w-16 h-0.5 bg-gray-300 mx-4"></div>
          <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold">
              4
            </div>
            <span class="ml-2 text-sm font-medium text-gray-500">Review</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Delivery Address</h2>
            
            <form class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                <input 
                  type="text" 
                  disabled
                  placeholder="123 Main Street"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                  <input 
                    type="text" 
                    disabled
                    placeholder="Harare"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                  <input 
                    type="text" 
                    disabled
                    placeholder="Harare"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                  <input 
                    type="tel" 
                    disabled
                    placeholder="+263 XXX XXX XXX"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                  />
                </div>
              </div>

              <div class="flex justify-between pt-6 border-t">
                <RouterLink 
                  to="/cart" 
                  class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Back to Cart
                </RouterLink>
                <button 
                  type="button"
                  disabled
                  class="px-6 py-2 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed"
                >
                  Continue (Coming Soon)
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
            
            <!-- Empty Cart Message -->
            <div class="text-center py-6 border-b border-gray-200 mb-4">
              <p class="text-gray-500">Your cart is empty</p>
            </div>

            <div class="space-y-3 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>$0.00</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Delivery</span>
                <span>--</span>
              </div>
              <div class="border-t pt-3 flex justify-between font-semibold text-gray-900">
                <span>Total</span>
                <span>$0.00</span>
              </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <h3 class="font-medium text-gray-800 mb-2">Features coming soon:</h3>
              <ul class="text-sm text-gray-600 space-y-1">
                <li>• Delivery time slot selection</li>
                <li>• Multiple payment methods</li>
                <li>• Order notes</li>
                <li>• Promo code support</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
