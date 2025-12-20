<script setup>
/**
 * Payment Step Component
 *
 * Handles payment method selection and promo code entry.
 *
 * @requirement CHK-008 Create payment method selection
 * @requirement CHK-015 Create promo code input
 */
import { computed, ref } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import PaymentMethodSelector from '@/components/checkout/PaymentMethodSelector.vue'
import PromoCodeInput from '@/components/checkout/PromoCodeInput.vue'
import {
  CreditCardIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const checkoutStore = useCheckoutStore()

const canContinue = computed(() => checkoutStore.isPaymentValid)

const handleContinue = () => {
  if (canContinue.value) {
    checkoutStore.nextStep()
  }
}

const handleBack = () => {
  checkoutStore.previousStep()
}
</script>

<template>
  <div class="rounded-lg bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center">
      <CreditCardIcon class="mr-3 h-6 w-6 text-emerald-500" />
      <h2 class="text-xl font-semibold text-gray-900">Payment Method</h2>
    </div>

    <!-- Payment Method Selection -->
    <PaymentMethodSelector
      :methods="checkoutStore.paymentMethods"
      :selected="checkoutStore.paymentMethod"
      @select="checkoutStore.paymentMethod = $event"
    />

    <!-- Promo Code -->
    <div class="mt-8">
      <PromoCodeInput />
    </div>

    <!-- Order Notes -->
    <div class="mt-8">
      <h3 class="mb-4 font-medium text-gray-900">Order Notes (Optional)</h3>

      <div class="space-y-4">
        <!-- General notes -->
        <div>
          <label for="order_notes" class="block text-sm font-medium text-gray-700">
            Order Notes
          </label>
          <textarea
            id="order_notes"
            v-model="checkoutStore.orderNotes"
            rows="2"
            placeholder="Any special requests for your order..."
            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
          ></textarea>
        </div>

        <!-- Delivery instructions -->
        <div>
          <label for="delivery_instructions" class="block text-sm font-medium text-gray-700">
            Delivery Instructions
          </label>
          <textarea
            id="delivery_instructions"
            v-model="checkoutStore.deliveryInstructions"
            rows="2"
            placeholder="Ring doorbell, leave at door, etc..."
            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="mt-8 flex justify-between">
      <button
        type="button"
        class="flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50"
        @click="handleBack"
      >
        <ArrowLeftIcon class="mr-2 h-5 w-5" />
        Back
      </button>

      <button
        type="button"
        :disabled="!canContinue"
        class="rounded-lg bg-emerald-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-emerald-600 disabled:cursor-not-allowed disabled:bg-gray-300"
        @click="handleContinue"
      >
        Review Order
      </button>
    </div>
  </div>
</template>
