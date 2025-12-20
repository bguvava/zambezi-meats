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
              class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <div>
                <div class="flex items-center justify-between mb-4">
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                    Edit User
                  </DialogTitle>
                  <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                    @click="close">
                    <span class="sr-only">Close</span>
                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                  </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                  <!-- Name -->
                  <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-700">
                      Name <span class="text-red-600">*</span>
                    </label>
                    <input id="edit-name" v-model="form.name" type="text" required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.name }" />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                      {{ errors.name[0] }}
                    </p>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="edit-email" class="block text-sm font-medium text-gray-700">
                      Email <span class="text-red-600">*</span>
                    </label>
                    <input id="edit-email" v-model="form.email" type="email" required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.email }" />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                      {{ errors.email[0] }}
                    </p>
                  </div>

                  <!-- Role -->
                  <div>
                    <label for="edit-role" class="block text-sm font-medium text-gray-700">
                      Role <span class="text-red-600">*</span>
                    </label>
                    <select id="edit-role" v-model="form.role" required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.role }">
                      <option value="">Select role</option>
                      <option value="customer">Customer</option>
                      <option value="staff">Staff</option>
                      <option value="admin">Admin</option>
                    </select>
                    <p v-if="errors.role" class="mt-1 text-sm text-red-600">
                      {{ errors.role[0] }}
                    </p>
                  </div>

                  <!-- Actions -->
                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="submit" :disabled="loading"
                      class="inline-flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2"
                      style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);">
                      <span v-if="!loading" class="flex items-center">
                        <CheckIcon class="-ml-1 mr-2 h-5 w-5" />
                        Update User
                      </span>
                      <span v-else class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                          fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                          </circle>
                          <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                          </path>
                        </svg>
                        Updating...
                      </span>
                    </button>
                    <button type="button"
                      class="mt-3 inline-flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200 sm:col-start-1 sm:mt-0"
                      style="background-color: #EFEFEF; color: #4D4B4C;" @click="close">
                      <XMarkIcon class="-ml-1 mr-2 h-5 w-5" />
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon, CheckIcon } from '@heroicons/vue/24/outline'
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

const emit = defineEmits(['close', 'updated'])

const userStore = useUserStore()
const toast = useToast()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  email: '',
  role: ''
})

watch(() => props.user, (newUser) => {
  if (newUser) {
    form.name = newUser.name
    form.email = newUser.email
    form.role = newUser.role
    errors.value = {}
  }
}, { immediate: true })

watch(() => props.open, (newValue) => {
  if (newValue && props.user) {
    form.name = props.user.name
    form.email = props.user.email
    form.role = props.user.role
    errors.value = {}
  }
})

function close() {
  emit('close')
}

async function handleSubmit() {
  if (!props.user) return

  loading.value = true
  errors.value = {}

  try {
    const response = await userStore.updateUser(props.user.id, {
      name: form.name,
      email: form.email,
      role: form.role
    })

    toast.success('User updated successfully')
    emit('updated', response.data)
    close()
  } catch (error) {
    if (error.response?.data?.error?.errors) {
      errors.value = error.response.data.error.errors
    } else {
      toast.error(error.response?.data?.message || 'Failed to update user')
    }
  } finally {
    loading.value = false
  }
}
</script>
