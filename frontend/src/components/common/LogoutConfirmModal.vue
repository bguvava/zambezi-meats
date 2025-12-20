<script setup>
/**
 * LogoutConfirmModal.vue
 * Modal dialog to confirm logout action
 */
import { X, LogOut } from 'lucide-vue-next'

defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50"
          @click="emit('cancel')"
        ></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
          <!-- Close Button -->
          <button
            @click="emit('cancel')"
            class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <X class="w-5 h-5" />
          </button>

          <!-- Icon -->
          <div class="flex justify-center mb-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
              <LogOut class="w-8 h-8 text-red-600" />
            </div>
          </div>

          <!-- Title -->
          <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">
            Confirm Logout
          </h3>

          <!-- Message -->
          <p class="text-gray-600 text-center mb-6">
            Are you sure you want to logout? You will need to sign in again to access your account.
          </p>

          <!-- Actions -->
          <div class="flex space-x-3">
            <button
              @click="emit('cancel')"
              class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="emit('confirm')"
              class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
