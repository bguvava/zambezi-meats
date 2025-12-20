<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative transform overflow-hidden rounded-xl bg-white px-6 pb-6 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
              style="border: 3px solid #CF0D0F;">
              <div>
                <div class="flex items-center justify-between mb-6">
                  <DialogTitle as="h3" class="text-2xl font-bold" style="color: #CF0D0F;">
                    User Details
                  </DialogTitle>
                  <button type="button" class="rounded-lg p-2 hover:bg-gray-100 transition-colors" @click="close">
                    <XMarkIcon class="h-6 w-6" style="color: #4D4B4C;" />
                  </button>
                </div>

                <div v-if="user" class="space-y-6">
                  <!-- User Avatar and Name -->
                  <div class="flex items-center space-x-4 pb-6 border-b-2" style="border-color: #EFEFEF;">
                    <UserAvatar :name="user.name" size="xl" />
                    <div>
                      <h4 class="text-xl font-bold" style="color: #4D4B4C;">{{ user.name }}</h4>
                      <p class="text-sm" style="color: #6F6F6F;">{{ user.email }}</p>
                    </div>
                  </div>

                  <!-- User Information Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Phone</label>
                      <p class="text-sm font-medium" style="color: #4D4B4C;">{{ user.phone || 'Not provided' }}</p>
                    </div>

                    <!-- Role -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Role</label>
                      <UserRoleBadge :role="user.role" />
                    </div>

                    <!-- Status -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Status</label>
                      <UserStatusBadge :status="user.status" />
                    </div>

                    <!-- Currency Preference -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Currency</label>
                      <p class="text-sm font-medium" style="color: #4D4B4C;">{{ user.currency_preference?.toUpperCase()
                        || 'AUD' }}</p>
                    </div>

                    <!-- Email Verified -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Email
                        Verified</label>
                      <div class="flex items-center space-x-2">
                        <CheckCircleIcon v-if="user.email_verified_at" class="h-5 w-5" style="color: #10B981;" />
                        <XCircleIcon v-else class="h-5 w-5" style="color: #EF4444;" />
                        <span class="text-sm font-medium" style="color: #4D4B4C;">
                          {{ user.email_verified_at ? 'Yes' : 'No' }}
                        </span>
                      </div>
                    </div>

                    <!-- Member Since -->
                    <div class="space-y-1">
                      <label class="text-xs font-bold uppercase tracking-wider" style="color: #6F6F6F;">Member
                        Since</label>
                      <p class="text-sm font-medium" style="color: #4D4B4C;">{{ formatDate(user.created_at) }}</p>
                    </div>
                  </div>

                  <!-- Additional Information -->
                  <div class="pt-6 border-t-2 space-y-4" style="border-color: #EFEFEF;">
                    <!-- Last Login -->
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium" style="color: #6F6F6F;">Last Login:</span>
                      <span class="text-sm font-semibold" style="color: #4D4B4C;">
                        {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
                      </span>
                    </div>

                    <!-- Last Updated -->
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium" style="color: #6F6F6F;">Last Updated:</span>
                      <span class="text-sm font-semibold" style="color: #4D4B4C;">
                        {{ formatDate(user.updated_at) }}
                      </span>
                    </div>

                    <!-- User ID -->
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium" style="color: #6F6F6F;">User ID:</span>
                      <span class="text-sm font-mono font-semibold" style="color: #4D4B4C;">
                        #{{ user.id }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end">
                  <button type="button"
                    class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200"
                    style="background-color: #EFEFEF; color: #4D4B4C;" @click="close">
                    <XMarkIcon class="h-5 w-5 mr-2" />
                    Close
                  </button>
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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import UserAvatar from '@/components/user/UserAvatar.vue'
import UserRoleBadge from '@/components/user/UserRoleBadge.vue'
import UserStatusBadge from '@/components/user/UserStatusBadge.vue'

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

function close() {
  emit('close')
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
