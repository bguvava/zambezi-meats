<script setup>
/**
 * SupportTicketsPage.vue
 * Customer support tickets page for viewing and creating tickets
 *
 * @requirement CUST-016 Create support contact page
 * @requirement CUST-017 Implement support ticket submission
 * @requirement CUST-018 View support ticket history
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useTicketsStore } from '@/stores/tickets'
import { useNotificationStore } from '@/stores/notificationStore'
import {
  MessageCircle,
  Plus,
  Send,
  X,
  AlertTriangle,
  RefreshCw,
  Loader2,
  ChevronLeft,
  Clock,
  User,
  Headphones
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const ticketsStore = useTicketsStore()
const toast = useNotificationStore()

const isLoading = ref(true)
const error = ref(null)
const activeFilter = ref('all') // 'all', 'open', 'resolved', 'closed'
const showNewTicketModal = ref(false)
const selectedTicket = ref(null)

// New Ticket Form
const newTicketForm = ref({
  subject: '',
  message: '',
  priority: 'medium'
})
const formErrors = ref({})

// Reply Form
const replyMessage = ref('')

// Computed
const filteredTickets = computed(() => {
  switch (activeFilter.value) {
    case 'open':
      return ticketsStore.openTickets
    case 'resolved':
      return ticketsStore.resolvedTickets
    case 'closed':
      return ticketsStore.closedTickets
    default:
      return ticketsStore.tickets
  }
})

const filterCounts = computed(() => ({
  all: ticketsStore.ticketCount,
  open: ticketsStore.openTickets.length,
  resolved: ticketsStore.resolvedTickets.length,
  closed: ticketsStore.closedTickets.length
}))

// Methods
async function fetchTickets() {
  isLoading.value = true
  error.value = null

  try {
    await ticketsStore.fetchTickets()
  } catch (err) {
    error.value = 'Failed to load support tickets'
    console.error('Failed to fetch tickets:', err)
  } finally {
    isLoading.value = false
  }
}

async function viewTicket(ticket) {
  selectedTicket.value = ticket
  await ticketsStore.fetchTicket(ticket.id)
  selectedTicket.value = ticketsStore.currentTicket
}

function closeTicketDetail() {
  selectedTicket.value = null
  ticketsStore.clearCurrentTicket()
}

async function submitNewTicket() {
  formErrors.value = {}

  // Validate
  if (!newTicketForm.value.subject.trim()) {
    formErrors.value.subject = 'Subject is required'
  }
  if (!newTicketForm.value.message.trim()) {
    formErrors.value.message = 'Message is required'
  }

  if (Object.keys(formErrors.value).length > 0) return

  const result = await ticketsStore.createTicket(newTicketForm.value)

  if (result.success) {
    toast.success('Ticket created successfully')
    showNewTicketModal.value = false
    newTicketForm.value = { subject: '', message: '', priority: 'medium' }
    // View the new ticket
    await viewTicket(result.ticket)
  } else {
    toast.error(result.message)
    if (result.errors) {
      formErrors.value = result.errors
    }
  }
}

async function sendReply() {
  if (!replyMessage.value.trim() || !selectedTicket.value) return

  const result = await ticketsStore.replyToTicket(
    selectedTicket.value.id,
    replyMessage.value
  )

  if (result.success) {
    toast.success('Reply sent successfully')
    replyMessage.value = ''
    // Refresh the ticket
    await ticketsStore.fetchTicket(selectedTicket.value.id)
    selectedTicket.value = ticketsStore.currentTicket
  } else {
    toast.error(result.message)
  }
}

function formatDate(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getStatusClass(status) {
  const config = ticketsStore.getStatusConfig(status)
  return config.bgColor
}

function getPriorityClass(priority) {
  const config = ticketsStore.getPriorityConfig(priority)
  return config.bgColor
}

// Lifecycle
onMounted(() => {
  fetchTickets()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Ticket Detail View -->
      <template v-if="selectedTicket">
        <div class="mb-6">
          <button @click="closeTicketDetail"
            class="inline-flex items-center text-gray-600 hover:text-[#CF0D0F] transition-colors">
            <ChevronLeft class="w-5 h-5 mr-1" />
            Back to Tickets
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 mb-6">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ selectedTicket.subject }}</h1>
              <div class="flex items-center gap-3 mt-2">
                <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusClass(selectedTicket.status)]">
                  {{ ticketsStore.getStatusConfig(selectedTicket.status).label }}
                </span>
                <span :class="['px-3 py-1 rounded-full text-sm font-medium', getPriorityClass(selectedTicket.priority)]">
                  {{ ticketsStore.getPriorityConfig(selectedTicket.priority).label }} Priority
                </span>
              </div>
            </div>
            <div class="text-sm text-gray-500">
              <div class="flex items-center gap-2">
                <Clock class="w-4 h-4" />
                Created {{ formatDate(selectedTicket.created_at) }}
              </div>
            </div>
          </div>

          <!-- Original Message -->
          <div class="border-2 border-gray-200 rounded-lg p-4 mb-6 bg-gray-50">
            <div class="flex items-center gap-2 mb-2">
              <User class="w-5 h-5 text-gray-500" />
              <span class="font-medium text-gray-700">You</span>
            </div>
            <p class="text-gray-600 whitespace-pre-wrap">{{ selectedTicket.message }}</p>
          </div>

          <!-- Replies -->
          <div v-if="selectedTicket.replies?.length > 0" class="space-y-4 mb-6">
            <h3 class="font-medium text-gray-700">Conversation</h3>

            <div v-for="reply in selectedTicket.replies" :key="reply.id" :class="[
              'border-2 rounded-lg p-4',
              reply.is_staff
                ? 'border-blue-200 bg-blue-50'
                : 'border-gray-200 bg-gray-50'
            ]">
              <div class="flex items-center gap-2 mb-2">
                <Headphones v-if="reply.is_staff" class="w-5 h-5 text-blue-500" />
                <User v-else class="w-5 h-5 text-gray-500" />
                <span class="font-medium" :class="reply.is_staff ? 'text-blue-700' : 'text-gray-700'">
                  {{ reply.is_staff ? 'Support Team' : 'You' }}
                </span>
                <span class="text-sm text-gray-500">
                  {{ formatDate(reply.created_at) }}
                </span>
              </div>
              <p class="text-gray-600 whitespace-pre-wrap">{{ reply.message }}</p>
            </div>
          </div>

          <!-- Reply Form -->
          <div v-if="selectedTicket.status !== 'closed'" class="border-t-2 border-gray-200 pt-6">
            <h3 class="font-medium text-gray-700 mb-4">Send a Reply</h3>
            <div class="space-y-4">
              <textarea v-model="replyMessage" rows="4" placeholder="Type your message here..."
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] resize-none"></textarea>
              <div class="flex justify-end">
                <button @click="sendReply" :disabled="!replyMessage.trim() || ticketsStore.isSaving"
                  class="inline-flex items-center px-6 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                  <Send v-if="!ticketsStore.isSaving" class="w-5 h-5 mr-2" />
                  <Loader2 v-else class="w-5 h-5 mr-2 animate-spin" />
                  Send Reply
                </button>
              </div>
            </div>
          </div>

          <div v-else class="border-t-2 border-gray-200 pt-6 text-center text-gray-500">
            This ticket is closed. If you need further assistance, please create a new ticket.
          </div>
        </div>
      </template>

      <!-- Tickets List View -->
      <template v-else>
        <!-- Page Header -->
        <div class="mb-8">
          <nav class="text-sm mb-4">
            <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
            <span class="text-gray-400 mx-2">/</span>
            <span class="text-gray-900">Support</span>
          </nav>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
              <p class="text-gray-600 mt-1">Get help with your orders and account</p>
            </div>

            <button @click="showNewTicketModal = true"
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
              <Plus class="w-5 h-5 mr-2" />
              New Ticket
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 mb-6">
          <div class="flex flex-wrap border-b border-gray-200">
            <button v-for="filter in ['all', 'open', 'resolved', 'closed']" :key="filter" @click="activeFilter = filter"
              :class="[
                'px-4 py-3 text-sm font-medium transition-colors capitalize',
                activeFilter === filter
                  ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F]'
                  : 'text-gray-500 hover:text-gray-700'
              ]">
              {{ filter }} ({{ filterCounts[filter] }})
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div
              class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
            </div>
            <p class="mt-4 text-gray-600">Loading tickets...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
          <div class="flex items-center">
            <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
            <div>
              <h3 class="text-red-800 font-medium">Error loading tickets</h3>
              <p class="text-red-600 text-sm mt-1">{{ error }}</p>
            </div>
          </div>
          <button @click="fetchTickets"
            class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <RefreshCw class="w-4 h-4 mr-2" />
            Retry
          </button>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredTickets.length === 0"
          class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-12 text-center">
          <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <MessageCircle class="w-10 h-10 text-blue-500" />
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">No Tickets Found</h2>
          <p class="text-gray-600 mb-6">
            {{ activeFilter === 'all'
              ? 'You haven\'t created any support tickets yet.'
              : `No ${activeFilter} tickets.`
            }}
          </p>
          <button @click="showNewTicketModal = true"
            class="inline-flex items-center px-6 py-3 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
            <Plus class="w-5 h-5 mr-2" />
            Create Your First Ticket
          </button>
        </div>

        <!-- Tickets List -->
        <div v-else class="space-y-4">
          <div v-for="ticket in filteredTickets" :key="ticket.id" @click="viewTicket(ticket)"
            class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-6 hover:border-[#CF0D0F] hover:shadow-md transition-all cursor-pointer">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="font-semibold text-gray-900">{{ ticket.subject }}</h3>
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getStatusClass(ticket.status)]">
                    {{ ticketsStore.getStatusConfig(ticket.status).label }}
                  </span>
                </div>
                <p class="text-gray-600 text-sm line-clamp-2">{{ ticket.message }}</p>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getPriorityClass(ticket.priority)]">
                  {{ ticketsStore.getPriorityConfig(ticket.priority).label }}
                </span>
                <span class="text-sm text-gray-500">{{ formatDate(ticket.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- New Ticket Modal -->
      <Teleport to="body">
        <Transition name="modal">
          <div v-if="showNewTicketModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showNewTicketModal = false"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
              <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Create New Ticket</h2>
                <button @click="showNewTicketModal = false" class="text-gray-400 hover:text-gray-600">
                  <X class="w-6 h-6" />
                </button>
              </div>

              <form @submit.prevent="submitNewTicket" class="p-6 space-y-6">
                <!-- Subject -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                  <input v-model="newTicketForm.subject" type="text" placeholder="Brief description of your issue"
                    :class="[
                      'w-full px-4 py-2 border-2 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]',
                      formErrors.subject ? 'border-red-500' : 'border-gray-300'
                    ]" />
                  <p v-if="formErrors.subject" class="mt-1 text-sm text-red-600">{{ formErrors.subject }}</p>
                </div>

                <!-- Priority -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                  <select v-model="newTicketForm.priority"
                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </select>
                </div>

                <!-- Message -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                  <textarea v-model="newTicketForm.message" rows="5"
                    placeholder="Please describe your issue in detail..." :class="[
                      'w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] resize-none',
                      formErrors.message ? 'border-red-500' : 'border-gray-300'
                    ]"></textarea>
                  <p v-if="formErrors.message" class="mt-1 text-sm text-red-600">{{ formErrors.message }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4">
                  <button type="button" @click="showNewTicketModal = false"
                    class="px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                  </button>
                  <button type="submit" :disabled="ticketsStore.isSaving"
                    class="inline-flex items-center px-6 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <Loader2 v-if="ticketsStore.isSaving" class="w-5 h-5 mr-2 animate-spin" />
                    Submit Ticket
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Transition>
      </Teleport>
    </div>
  </div>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
