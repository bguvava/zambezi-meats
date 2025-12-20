<script setup>
/**
 * Promo Code Input Component
 *
 * Input field for applying promo/discount codes.
 *
 * @requirement CHK-015 Create promo code input
 */
import { computed } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import {
  TagIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const checkoutStore = useCheckoutStore()

const isLoading = computed(() => checkoutStore.isValidatingPromo)
const hasPromo = computed(() => checkoutStore.promoValid)

const handleApply = () => {
  if (checkoutStore.promoCode.trim()) {
    checkoutStore.validatePromoCode()
  }
}

const handleClear = () => {
  checkoutStore.clearPromoCode()
}
</script>

<template>
  <div>
    <h3 class="mb-4 flex items-center font-medium text-gray-900">
      <TagIcon class="mr-2 h-5 w-5 text-gray-400" />
      Promo Code
    </h3>

    <div class="flex gap-3">
      <div class="relative flex-1">
        <input
          v-model="checkoutStore.promoCode"
          type="text"
          placeholder="Enter promo code"
          :disabled="hasPromo"
          class="block w-full rounded-lg border border-gray-300 px-4 py-3 uppercase focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:bg-gray-100"
          @keyup.enter="handleApply"
        />

        <!-- Clear button when promo is applied -->
        <button
          v-if="hasPromo"
          type="button"
          class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
          @click="handleClear"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <button
        v-if="!hasPromo"
        type="button"
        :disabled="!checkoutStore.promoCode.trim() || isLoading"
        class="rounded-lg border border-emerald-500 bg-white px-6 py-3 font-semibold text-emerald-500 transition-colors hover:bg-emerald-50 disabled:cursor-not-allowed disabled:border-gray-300 disabled:text-gray-400"
        @click="handleApply"
      >
        <span v-if="isLoading">
          <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></span>
        </span>
        <span v-else>Apply</span>
      </button>
    </div>

    <!-- Promo status message -->
    <div v-if="checkoutStore.promoMessage" class="mt-3 flex items-start">
      <CheckCircleIcon v-if="hasPromo" class="mr-2 h-5 w-5 flex-shrink-0 text-green-500" />
      <XCircleIcon v-else class="mr-2 h-5 w-5 flex-shrink-0 text-red-500" />
      <span
        class="text-sm"
        :class="{
          'text-green-600': hasPromo,
          'text-red-600': !hasPromo
        }"
      >
        {{ checkoutStore.promoMessage }}
      </span>
    </div>
  </div>
</template>
