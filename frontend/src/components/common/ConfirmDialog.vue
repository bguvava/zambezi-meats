<script setup>
/**
 * ConfirmDialog Component
 *
 * Reusable confirmation dialog for destructive actions.
 * Displays a modal with title, message, and confirm/cancel buttons.
 */
import { computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  variant: {
    type: String,
    default: 'danger', // 'danger' or 'warning' or 'info'
    validator: (value) => ['danger', 'warning', 'info'].includes(value),
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const variantClasses = computed(() => {
  const variants = {
    danger: {
      button: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
      icon: 'bg-red-100 text-red-600',
    },
    warning: {
      button: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
      icon: 'bg-yellow-100 text-yellow-600',
    },
    info: {
      button: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
      icon: 'bg-blue-100 text-blue-600',
    },
  };
  return variants[props.variant];
});

function handleConfirm() {
  if (!props.loading) {
    emit('confirm');
  }
}

function handleCancel() {
  if (!props.loading) {
    emit('cancel');
  }
}

function handleClose() {
  if (!props.loading) {
    emit('close');
  }
}
</script>

<template>
  <!-- Backdrop -->
  <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
    enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100"
    leave-to-class="opacity-0">
    <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-50" @click="handleClose"></div>
  </Transition>

  <!-- Dialog -->
  <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200"
    leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="handleClose">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full" @click.stop>
        <!-- Header -->
        <div class="flex items-start justify-between p-6 pb-4">
          <div class="flex items-start space-x-3">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
              variantClasses.icon,
            ]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="variant === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                <path v-else-if="variant === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
              <p class="mt-1 text-sm text-gray-600">{{ message }}</p>
            </div>
          </div>
          <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" :disabled="loading"
            @click="handleClose">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end space-x-3 p-6 pt-4 bg-gray-50 rounded-b-lg">
          <button type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="loading" @click="handleCancel">
            {{ cancelText }}
          </button>
          <button type="button" :class="[
            'px-4 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
            variantClasses.button,
          ]" :disabled="loading" @click="handleConfirm">
            <span v-if="loading" class="flex items-center space-x-2">
              <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <span>Processing...</span>
            </span>
            <span v-else>{{ confirmText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
