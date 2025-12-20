<script setup>
/**
 * Address Form Component
 *
 * Form for entering delivery address details.
 *
 * @requirement CHK-003 Create delivery address form
 */
import { useCheckoutStore } from '@/stores/checkout'

const checkoutStore = useCheckoutStore()

const handlePostcodeChange = () => {
  // Validation is triggered by watcher in store
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="font-medium text-gray-900">Enter Delivery Address</h3>

    <!-- Street Address -->
    <div>
      <label for="street_address" class="block text-sm font-medium text-gray-700">
        Street Address <span class="text-red-500">*</span>
      </label>
      <input
        id="street_address"
        v-model="checkoutStore.deliveryForm.streetAddress"
        type="text"
        placeholder="123 Main Street"
        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        required
      />
    </div>

    <!-- Apartment/Unit (optional) -->
    <div>
      <label for="apartment" class="block text-sm font-medium text-gray-700">
        Apartment / Unit (optional)
      </label>
      <input
        id="apartment"
        v-model="checkoutStore.deliveryForm.apartment"
        type="text"
        placeholder="Apt 4B"
        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
      />
    </div>

    <!-- Suburb and State row -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label for="suburb" class="block text-sm font-medium text-gray-700">
          Suburb <span class="text-red-500">*</span>
        </label>
        <input
          id="suburb"
          v-model="checkoutStore.deliveryForm.suburb"
          type="text"
          placeholder="Engadine"
          class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
          required
        />
      </div>

      <div>
        <label for="state" class="block text-sm font-medium text-gray-700">
          State <span class="text-red-500">*</span>
        </label>
        <select
          id="state"
          v-model="checkoutStore.deliveryForm.state"
          class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
          required
        >
          <option v-for="state in checkoutStore.australianStates" :key="state.code" :value="state.code">
            {{ state.code }} - {{ state.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Postcode -->
    <div class="w-1/2">
      <label for="postcode" class="block text-sm font-medium text-gray-700">
        Postcode <span class="text-red-500">*</span>
      </label>
      <input
        id="postcode"
        v-model="checkoutStore.deliveryForm.postcode"
        type="text"
        inputmode="numeric"
        pattern="\d{4}"
        maxlength="4"
        placeholder="2233"
        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        required
        @blur="handlePostcodeChange"
      />
      <p class="mt-1 text-xs text-gray-500">Enter 4-digit Australian postcode</p>
    </div>

    <!-- Address Label (for saving) -->
    <div>
      <label for="address_label" class="block text-sm font-medium text-gray-700">
        Address Label (optional)
      </label>
      <input
        id="address_label"
        v-model="checkoutStore.deliveryForm.label"
        type="text"
        placeholder="Home, Work, etc."
        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
      />
    </div>

    <!-- Save Address Checkbox -->
    <div class="flex items-center">
      <input
        id="save_address"
        v-model="checkoutStore.deliveryForm.saveAddress"
        type="checkbox"
        class="h-4 w-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
      />
      <label for="save_address" class="ml-2 text-sm text-gray-700">
        Save this address for future orders
      </label>
    </div>
  </div>
</template>
