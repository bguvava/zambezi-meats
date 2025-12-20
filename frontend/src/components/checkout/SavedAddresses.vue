<script setup>
/**
 * Saved Addresses Component
 *
 * Displays and allows selection of saved addresses.
 *
 * @requirement CHK-005 Display saved addresses for logged-in users
 */
import { CheckCircleIcon, HomeIcon, BriefcaseIcon, MapPinIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  addresses: {
    type: Array,
    required: true
  },
  selectedId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['select'])

const getAddressIcon = (label) => {
  const lowerLabel = label?.toLowerCase() || ''
  if (lowerLabel.includes('home')) return HomeIcon
  if (lowerLabel.includes('work') || lowerLabel.includes('office')) return BriefcaseIcon
  return MapPinIcon
}

const handleSelect = (addressId) => {
  emit('select', addressId)
}
</script>

<template>
  <div class="space-y-3">
    <label
      v-for="address in addresses"
      :key="address.id"
      class="relative flex cursor-pointer rounded-lg border p-4 transition-colors"
      :class="{
        'border-emerald-500 bg-emerald-50': selectedId === address.id,
        'border-gray-200 hover:border-gray-300 hover:bg-gray-50': selectedId !== address.id
      }"
    >
      <input
        type="radio"
        :checked="selectedId === address.id"
        class="sr-only"
        @change="handleSelect(address.id)"
      />

      <div class="flex w-full items-start">
        <!-- Icon -->
        <div
          class="mr-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
          :class="{
            'bg-emerald-100': selectedId === address.id,
            'bg-gray-100': selectedId !== address.id
          }"
        >
          <component
            :is="getAddressIcon(address.label)"
            class="h-5 w-5"
            :class="{
              'text-emerald-600': selectedId === address.id,
              'text-gray-500': selectedId !== address.id
            }"
          />
        </div>

        <!-- Address details -->
        <div class="flex-1">
          <div class="flex items-center justify-between">
            <span class="font-medium text-gray-900">{{ address.label }}</span>
            <span
              v-if="address.is_default"
              class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600"
            >
              Default
            </span>
          </div>
          <p class="mt-1 text-sm text-gray-600">
            {{ address.street_address }}
            <span v-if="address.apartment">, {{ address.apartment }}</span>
          </p>
          <p class="text-sm text-gray-600">
            {{ address.suburb }}, {{ address.state }} {{ address.postcode }}
          </p>
        </div>

        <!-- Checkmark -->
        <div
          v-if="selectedId === address.id"
          class="ml-4 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500"
        >
          <CheckCircleIcon class="h-4 w-4 text-white" />
        </div>
      </div>
    </label>
  </div>
</template>
