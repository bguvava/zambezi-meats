<template>
  <TransitionGroup tag="div" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none" name="toast"
    enter-active-class="transition ease-out duration-300" enter-from-class="transform translate-x-full opacity-0"
    enter-to-class="transform translate-x-0 opacity-100" leave-active-class="transition ease-in duration-200"
    leave-from-class="transform translate-x-0 opacity-100" leave-to-class="transform translate-x-full opacity-0">
    <div v-for="toast in toasts" :key="toast.id" :class="[
      'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg pointer-events-auto max-w-md',
      toastClasses[toast.type] || toastClasses.info
    ]">
      <component :is="toastIcons[toast.type]" class="h-5 w-5 flex-shrink-0" />
      <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
      <button type="button" class="flex-shrink-0 hover:opacity-70 transition-opacity" @click="remove(toast.id)">
        <XMarkIcon class="h-5 w-5" />
        <span class="sr-only">Close</span>
      </button>
    </div>
  </TransitionGroup>
</template>

<script setup>
import { useToast } from '@/composables/useToast'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/solid'

const { toasts, remove } = useToast()

const toastClasses = {
  success: 'bg-green-50 text-green-800 border border-green-200',
  error: 'bg-red-50 text-red-800 border border-red-200',
  warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
  info: 'bg-blue-50 text-blue-800 border border-blue-200'
}

const toastIcons = {
  success: CheckCircleIcon,
  error: ExclamationCircleIcon,
  warning: ExclamationTriangleIcon,
  info: InformationCircleIcon
}
</script>
