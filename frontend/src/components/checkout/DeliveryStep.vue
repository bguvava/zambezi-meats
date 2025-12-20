<script setup>
/**
 * Delivery Step Component
 *
 * Handles delivery address selection and validation.
 *
 * @requirement CHK-003 Create delivery address form
 * @requirement CHK-005 Display saved addresses for logged-in users
 * @requirement CHK-006 Validate delivery zone
 */
import { computed, ref } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import AddressForm from '@/components/checkout/AddressForm.vue'
import SavedAddresses from '@/components/checkout/SavedAddresses.vue'
import {
  MapPinIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'

const checkoutStore = useCheckoutStore()

const useNewAddress = ref(!checkoutStore.savedAddresses.length)

const toggleAddressMode = () => {
  useNewAddress.value = !useNewAddress.value
  if (useNewAddress.value) {
    checkoutStore.clearSelectedAddress()
  }
}

const canContinue = computed(() => checkoutStore.isDeliveryValid)
const isValidating = computed(
  () => checkoutStore.isValidatingAddress || checkoutStore.isCalculatingFee
)

const handleContinue = () => {
  if (canContinue.value) {
    checkoutStore.nextStep()
  }
}
</script>

<template>
  <div class="rounded-lg bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center">
      <MapPinIcon class="mr-3 h-6 w-6 text-emerald-500" />
      <h2 class="text-xl font-semibold text-gray-900">Delivery Address</h2>
    </div>

    <!-- Saved Addresses (if available) -->
    <div v-if="checkoutStore.savedAddresses.length > 0" class="mb-6">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="font-medium text-gray-900">Your Saved Addresses</h3>
        <button
          type="button"
          class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
          @click="toggleAddressMode"
        >
          {{ useNewAddress ? 'Use Saved Address' : 'Add New Address' }}
        </button>
      </div>

      <SavedAddresses
        v-if="!useNewAddress"
        :addresses="checkoutStore.savedAddresses"
        :selected-id="checkoutStore.deliveryForm.addressId"
        @select="checkoutStore.selectAddress"
      />
    </div>

    <!-- New Address Form -->
    <AddressForm v-if="useNewAddress || checkoutStore.savedAddresses.length === 0" />

    <!-- Delivery Zone Status -->
    <div v-if="checkoutStore.deliversToArea !== null" class="mt-6 rounded-lg p-4" :class="{
      'bg-green-50': checkoutStore.deliversToArea,
      'bg-red-50': !checkoutStore.deliversToArea
    }">
      <div class="flex items-start">
        <CheckCircleIcon v-if="checkoutStore.deliversToArea" class="mr-3 h-5 w-5 flex-shrink-0 text-green-500" />
        <ExclamationCircleIcon v-else class="mr-3 h-5 w-5 flex-shrink-0 text-red-500" />
        <div>
          <p class="font-medium" :class="{
            'text-green-800': checkoutStore.deliversToArea,
            'text-red-800': !checkoutStore.deliversToArea
          }">
            {{ checkoutStore.deliveryMessage }}
          </p>

          <!-- Delivery fee info -->
          <div v-if="checkoutStore.deliversToArea && checkoutStore.deliveryZone" class="mt-2">
            <div class="flex items-center text-sm text-green-700">
              <TruckIcon class="mr-2 h-4 w-4" />
              <span>
                Delivery Fee: <strong>{{ checkoutStore.deliveryFeeFormatted }}</strong>
                <span v-if="checkoutStore.estimatedDays" class="ml-2">
                  ({{ checkoutStore.estimatedDays === 1 ? 'Next day' : `${checkoutStore.estimatedDays} days` }})
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading indicator -->
    <div v-if="isValidating" class="mt-4 flex items-center text-gray-500">
      <div class="mr-3 h-5 w-5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"></div>
      <span>Validating address...</span>
    </div>

    <!-- Continue Button -->
    <div class="mt-8 flex justify-end">
      <button
        type="button"
        :disabled="!canContinue || isValidating"
        class="rounded-lg bg-emerald-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-emerald-600 disabled:cursor-not-allowed disabled:bg-gray-300"
        @click="handleContinue"
      >
        Continue to Payment
      </button>
    </div>
  </div>
</template>
