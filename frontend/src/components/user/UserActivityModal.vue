<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl sm:p-6">
              <div>
                <div class="flex items-center justify-between mb-4">
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                    Activity History - {{ user?.name }}
                  </DialogTitle>
                  <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                    @click="close">
                    <span class="sr-only">Close</span>
                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                  </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-12">
                  <svg class="animate-spin h-8 w-8 text-red-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                </div>

                <!-- Activity List -->
                <div v-else-if="activities.length > 0" class="space-y-4">
                  <div v-for="activity in activities" :key="activity.id"
                    class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                          {{ activity.action }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                          {{ activity.description }}
                        </p>

                        <!-- Old and New Values -->
                        <div v-if="activity.old_value || activity.new_value" class="mt-2 space-y-1">
                          <div v-if="activity.old_value" class="text-xs text-gray-500">
                            <span class="font-medium">Old value:</span>
                            <span class="ml-1 font-mono bg-gray-100 px-1.5 py-0.5 rounded">
                              {{ formatValue(activity.old_value) }}
                            </span>
                          </div>
                          <div v-if="activity.new_value" class="text-xs text-gray-500">
                            <span class="font-medium">New value:</span>
                            <span class="ml-1 font-mono bg-green-50 text-green-700 px-1.5 py-0.5 rounded">
                              {{ formatValue(activity.new_value) }}
                            </span>
                          </div>
                        </div>

                        <!-- Metadata -->
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                          <span>IP: {{ activity.ip_address || 'N/A' }}</span>
                          <span>{{ formatDate(activity.created_at) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Pagination -->
                  <div v-if="totalPages > 1" class="flex items-center justify-between border-t border-gray-200 pt-4">
                    <button type="button" :disabled="currentPage === 1"
                      class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                      @click="loadPage(currentPage - 1)">
                      Previous
                    </button>
                    <span class="text-sm text-gray-700">
                      Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button type="button" :disabled="currentPage === totalPages"
                      class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                      @click="loadPage(currentPage + 1)">
                      Next
                    </button>
                  </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">No activity found</h3>
                  <p class="mt-1 text-sm text-gray-500">This user has no recorded activity yet.</p>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useUserStore } from '@/stores/user'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const userStore = useUserStore()
const toast = useToast()

const loading = ref(false)
const activities = ref([])
const currentPage = ref(1)
const totalPages = ref(1)

watch(() => props.open, async (newValue) => {
  if (newValue && props.user) {
    await loadActivity()
  }
})

function close() {
  emit('close')
}

async function loadActivity() {
  if (!props.user) return

  loading.value = true

  try {
    const response = await userStore.fetchActivity(props.user.id, currentPage.value)
    activities.value = response.data
    currentPage.value = response.current_page
    totalPages.value = response.last_page
  } catch (error) {
    toast.error('Failed to load activity history')
  } finally {
    loading.value = false
  }
}

async function loadPage(page) {
  currentPage.value = page
  await loadActivity()
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatValue(value) {
  if (value === null || value === undefined) return 'N/A'
  if (typeof value === 'object') return JSON.stringify(value)
  return String(value)
}
</script>
