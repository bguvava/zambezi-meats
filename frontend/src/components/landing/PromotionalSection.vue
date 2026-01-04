<script setup>
/**
 * Promotional Section
 *
 * Eye-catching promotional banner with timeless messaging
 *
 * @requirement TASK-3 Promotional section on homepage
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="py-16 bg-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
      <div class="absolute top-0 left-0 w-96 h-96 bg-primary-600 rounded-full filter blur-3xl transform -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-primary-600 rounded-full filter blur-3xl transform translate-x-1/2 translate-y-1/2"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Promo - Free Delivery -->
        <div class="lg:col-span-2 bg-gradient-to-br from-[#CF0D0F] to-[#F6211F] rounded-2xl p-8 md:p-12 text-white shadow-xl transform transition-all hover:scale-[1.02] duration-300">
          <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1">
              <div class="inline-flex items-center px-3 py-1 bg-white/20 rounded-full text-sm font-semibold mb-4">
                🎉 Limited Time Offer
              </div>
              <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Free Delivery on Orders $100+
              </h2>
              <p class="text-white/90 text-lg mb-6">
                Premium quality meats delivered fresh to your doorstep. No delivery fees, no minimum order time limits.
              </p>
              <RouterLink
                to="/shop"
                class="inline-flex items-center gap-2 px-8 py-3 bg-white text-[#CF0D0F] font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:translate-x-1"
              >
                Shop Now
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </RouterLink>
            </div>
            <div class="flex-shrink-0">
              <div class="w-32 h-32 md:w-40 md:h-40 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center">
                <svg class="w-20 h-20 md:w-24 md:h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Side Promos -->
        <div class="space-y-6">
          <!-- Family Owned -->
          <div class="bg-[#EFEFEF] rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-[#CF0D0F]/10 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-[#4D4B4C] mb-1">Family Owned</h3>
                <p class="text-sm text-[#6F6F6F]">
                  Locally owned and operated, serving the community with pride
                </p>
              </div>
            </div>
          </div>

          <!-- Premium Cuts -->
          <div class="bg-[#EFEFEF] rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-[#CF0D0F]/10 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-[#4D4B4C] mb-1">Premium Cuts</h3>
                <p class="text-sm text-[#6F6F6F]">
                  Hand-selected by expert butchers, delivered fresh daily
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
