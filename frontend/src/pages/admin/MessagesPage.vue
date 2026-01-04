<script setup>
/**
 * MessagesPage.vue
 * Admin/Staff dashboard page for managing contact messages and newsletter subscriptions
 */
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import axios from 'axios'

// Active tab
const activeTab = ref('messages')

// Messages data
const messages = ref([])
const messagesLoading = ref(false)
const messagesPage = ref(1)
const messagesTotal = ref(0)
const messagesFilter = ref('all')
const messageStats = ref({
  new: 0,
  read: 0,
  replied: 0,
  archived: 0,
  total: 0
})

// Subscriptions data
const subscriptions = ref([])
const subscriptionsLoading = ref(false)
const subscriptionsPage = ref(1)
const subscriptionsTotal = ref(0)
const subscriptionsFilter = ref('all')
const subscriptionStats = ref({
  total: 0,
  active: 0,
  unsubscribed: 0
})

// Tickets data
const tickets = ref([])
const ticketsLoading = ref(false)
const ticketsPage = ref(1)
const ticketsTotal = ref(0)
const ticketsFilter = ref('all')
const ticketStats = ref({
  total: 0,
  open: 0,
  in_progress: 0,
  resolved: 0,
  closed: 0
})

// Selected message/ticket for viewing
const selectedMessage = ref(null)
const selectedTicket = ref(null)
const showMessageModal = ref(false)
const showTicketModal = ref(false)
const ticketReplyMessage = ref('')

// Fetch messages
const fetchMessages = async () => {
  messagesLoading.value = true
  try {
    const params = {
      page: messagesPage.value,
      per_page: 20
    }
    if (messagesFilter.value !== 'all') {
      params.status = messagesFilter.value
    }

    const response = await axios.get('/api/v1/admin/messages', { params })
    if (response.data.success) {
      messages.value = response.data.data.data
      messagesTotal.value = response.data.data.total
    }
  } catch (error) {
    console.error('Error fetching messages:', error)
    toast.error('Failed to load messages')
  } finally {
    messagesLoading.value = false
  }
}

// Fetch message stats
const fetchMessageStats = async () => {
  try {
    const response = await axios.get('/api/v1/admin/messages-stats')
    if (response.data.success) {
      messageStats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching message stats:', error)
  }
}

// Fetch subscriptions
const fetchSubscriptions = async () => {
  subscriptionsLoading.value = true
  try {
    const params = {
      page: subscriptionsPage.value,
      per_page: 20
    }
    if (subscriptionsFilter.value !== 'all') {
      params.status = subscriptionsFilter.value
    }

    const response = await axios.get('/api/v1/admin/subscriptions', { params })
    if (response.data.success) {
      subscriptions.value = response.data.data.data
      subscriptionsTotal.value = response.data.data.total
    }
  } catch (error) {
    console.error('Error fetching subscriptions:', error)
    toast.error('Failed to load subscriptions')
  } finally {
    subscriptionsLoading.value = false
  }
}

// Fetch subscription stats
const fetchSubscriptionStats = async () => {
  try {
    const response = await axios.get('/api/v1/admin/subscriptions-stats')
    if (response.data.success) {
      subscriptionStats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching subscription stats:', error)
  }
}

// View message details
const viewMessage = async (message) => {
  try {
    const response = await axios.get(`/api/v1/admin/messages/${message.id}`)
    if (response.data.success) {
      selectedMessage.value = response.data.data
      showMessageModal.value = true
      
      // Refresh list if status changed to read
      if (message.status === 'new') {
        await fetchMessages()
        await fetchMessageStats()
      }
    }
  } catch (error) {
    console.error('Error viewing message:', error)
    toast.error('Failed to load message details')
  }
}

// Update message status
const updateMessageStatus = async (messageId, status) => {
  try {
    const response = await axios.put(`/api/v1/admin/messages/${messageId}`, { status })
    if (response.data.success) {
      toast.success('Message status updated')
      await fetchMessages()
      await fetchMessageStats()
      
      // Update selected message if viewing
      if (selectedMessage.value && selectedMessage.value.id === messageId) {
        selectedMessage.value.status = status
      }
    }
  } catch (error) {
    console.error('Error updating message:', error)
    toast.error('Failed to update message status')
  }
}

// Delete message
const deleteMessage = async (messageId) => {
  if (!confirm('Are you sure you want to delete this message?')) return

  try {
    const response = await axios.delete(`/api/v1/admin/messages/${messageId}`)
    if (response.data.success) {
      toast.success('Message deleted')
      await fetchMessages()
      await fetchMessageStats()
      
      if (selectedMessage.value && selectedMessage.value.id === messageId) {
        showMessageModal.value = false
        selectedMessage.value = null
      }
    }
  } catch (error) {
    console.error('Error deleting message:', error)
    toast.error('Failed to delete message')
  }
}

// Delete subscription
const deleteSubscription = async (subscriptionId) => {
  if (!confirm('Are you sure you want to delete this subscription?')) return

  try {
    const response = await axios.delete(`/api/v1/admin/subscriptions/${subscriptionId}`)
    if (response.data.success) {
      toast.success('Subscription deleted')
      await fetchSubscriptions()
      await fetchSubscriptionStats()
    }
  } catch (error) {
    console.error('Error deleting subscription:', error)
    toast.error('Failed to delete subscription')
  }
}

// Fetch tickets
const fetchTickets = async () => {
  ticketsLoading.value = true
  try {
    const params = {
      page: ticketsPage.value,
      per_page: 20
    }
    if (ticketsFilter.value !== 'all') {
      params.status = ticketsFilter.value
    }

    const response = await axios.get('/api/v1/admin/tickets', { params })
    if (response.data.success) {
      tickets.value = response.data.data
      ticketsTotal.value = response.data.pagination.total
    }
  } catch (error) {
    console.error('Error fetching tickets:', error)
    toast.error('Failed to load tickets')
  } finally {
    ticketsLoading.value = false
  }
}

// Fetch ticket stats
const fetchTicketStats = async () => {
  try {
    const response = await axios.get('/api/v1/admin/tickets-stats')
    if (response.data.success) {
      ticketStats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching ticket stats:', error)
  }
}

// View ticket details
const viewTicket = async (ticket) => {
  try {
    const response = await axios.get(`/api/v1/admin/tickets/${ticket.id}`)
    if (response.data.success) {
      selectedTicket.value = response.data.data
      showTicketModal.value = true
      ticketReplyMessage.value = ''
    }
  } catch (error) {
    console.error('Error viewing ticket:', error)
    toast.error('Failed to load ticket details')
  }
}

// Update ticket status
const updateTicketStatus = async (ticketId, status) => {
  try {
    const response = await axios.put(`/api/v1/admin/tickets/${ticketId}/status`, { status })
    if (response.data.success) {
      toast.success('Ticket status updated')
      await fetchTickets()
      await fetchTicketStats()
      
      if (selectedTicket.value && selectedTicket.value.id === ticketId) {
        selectedTicket.value.status = status
      }
    }
  } catch (error) {
    console.error('Error updating ticket:', error)
    toast.error('Failed to update ticket status')
  }
}

// Reply to ticket
const replyToTicket = async () => {
  if (!ticketReplyMessage.value.trim()) {
    toast.error('Please enter a reply message')
    return
  }

  try {
    const response = await axios.post(`/api/v1/admin/tickets/${selectedTicket.value.id}/reply`, {
      message: ticketReplyMessage.value
    })
    if (response.data.success) {
      toast.success('Reply sent successfully')
      ticketReplyMessage.value = ''
      // Refresh ticket details
      await viewTicket({ id: selectedTicket.value.id })
      await fetchTickets()
    }
  } catch (error) {
    console.error('Error replying to ticket:', error)
    toast.error('Failed to send reply')
  }
}

// Delete ticket
const deleteTicket = async (ticketId) => {
  if (!confirm('Are you sure you want to delete this ticket?')) return

  try {
    const response = await axios.delete(`/api/v1/admin/tickets/${ticketId}`)
    if (response.data.success) {
      toast.success('Ticket deleted')
      await fetchTickets()
      await fetchTicketStats()
      
      if (selectedTicket.value && selectedTicket.value.id === ticketId) {
        showTicketModal.value = false
        selectedTicket.value = null
      }
    }
  } catch (error) {
    console.error('Error deleting ticket:', error)
    toast.error('Failed to delete ticket')
  }
}

// Tab change handler
const changeTab = (tab) => {
  activeTab.value = tab
  if (tab === 'messages' && messages.value.length === 0) {
    fetchMessages()
    fetchMessageStats()
  } else if (tab === 'subscriptions' && subscriptions.value.length === 0) {
    fetchSubscriptions()
    fetchSubscriptionStats()
  } else if (tab === 'tickets' && tickets.value.length === 0) {
    fetchTickets()
    fetchTicketStats()
  }
}

// Computed properties
const messagesPagination = computed(() => {
  const perPage = 20
  const totalPages = Math.ceil(messagesTotal.value / perPage)
  return {
    currentPage: messagesPage.value,
    totalPages,
    showing: {
      from: (messagesPage.value - 1) * perPage + 1,
      to: Math.min(messagesPage.value * perPage, messagesTotal.value),
      total: messagesTotal.value
    }
  }
})

const subscriptionsPagination = computed(() => {
  const perPage = 20
  const totalPages = Math.ceil(subscriptionsTotal.value / perPage)
  return {
    currentPage: subscriptionsPage.value,
    totalPages,
    showing: {
      from: (subscriptionsPage.value - 1) * perPage + 1,
      to: Math.min(subscriptionsPage.value * perPage, subscriptionsTotal.value),
      total: subscriptionsTotal.value
    }
  }
})

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Status badge color
const getStatusColor = (status) => {
  const colors = {
    new: 'bg-blue-100 text-blue-800',
    read: 'bg-gray-100 text-gray-800',
    replied: 'bg-green-100 text-green-800',
    archived: 'bg-yellow-100 text-yellow-800',
    active: 'bg-green-100 text-green-800',
    unsubscribed: 'bg-red-100 text-red-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

// Filter change handlers
const changeMessagesFilter = (filter) => {
  messagesFilter.value = filter
  messagesPage.value = 1
  fetchMessages()
}

const changeSubscriptionsFilter = (filter) => {
  subscriptionsFilter.value = filter
  subscriptionsPage.value = 1
  fetchSubscriptions()
}

// Pagination handlers
const changeMessagesPage = (page) => {
  messagesPage.value = page
  fetchMessages()
}

const changeSubscriptionsPage = (page) => {
  subscriptionsPage.value = page
  fetchSubscriptions()
}

// Tickets filter/pagination
const changeTicketsFilter = (filter) => {
  ticketsFilter.value = filter
  ticketsPage.value = 1
  fetchTickets()
}

const changeTicketsPage = (page) => {
  ticketsPage.value = page
  fetchTickets()
}

// Compute pagination for tickets
const ticketsPagination = computed(() => {
  const perPage = 20
  const totalPages = Math.ceil(ticketsTotal.value / perPage)
  return {
    currentPage: ticketsPage.value,
    totalPages,
    showing: {
      from: (ticketsPage.value - 1) * perPage + 1,
      to: Math.min(ticketsPage.value * perPage, ticketsTotal.value),
      total: ticketsTotal.value
    }
  }
})

// Initialize
onMounted(() => {
  fetchMessages()
  fetchMessageStats()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
        <p class="text-gray-600 mt-2">Manage contact messages and newsletter subscriptions</p>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8">
          <button
            @click="changeTab('messages')"
            :class="[
              activeTab === 'messages'
                ? 'border-[#CF0D0F] text-[#CF0D0F]'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Contact Messages
            <span v-if="messageStats.new > 0" class="ml-2 bg-[#CF0D0F] text-white text-xs px-2 py-0.5 rounded-full">
              {{ messageStats.new }}
            </span>
          </button>
          <button
            @click="changeTab('subscriptions')"
            :class="[
              activeTab === 'subscriptions'
                ? 'border-[#CF0D0F] text-[#CF0D0F]'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Newsletter Subscriptions
          </button>
        </nav>
      </div>

      <!-- Contact Messages Tab -->
      <div v-if="activeTab === 'messages'">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
          <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ messageStats.total }}</div>
          </div>
          <div class="bg-white rounded-lg border border-blue-200 p-4">
            <div class="text-sm text-blue-600">New</div>
            <div class="text-2xl font-bold text-blue-900 mt-1">{{ messageStats.new }}</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Read</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ messageStats.read }}</div>
          </div>
          <div class="bg-white rounded-lg border border-green-200 p-4">
            <div class="text-sm text-green-600">Replied</div>
            <div class="text-2xl font-bold text-green-900 mt-1">{{ messageStats.replied }}</div>
          </div>
          <div class="bg-white rounded-lg border border-yellow-200 p-4">
            <div class="text-sm text-yellow-600">Archived</div>
            <div class="text-2xl font-bold text-yellow-900 mt-1">{{ messageStats.archived }}</div>
          </div>
        </div>

        <!-- Filter -->
        <div class="flex justify-between items-center mb-4">
          <div class="flex space-x-2">
            <button
              @click="changeMessagesFilter('all')"
              :class="[
                messagesFilter === 'all' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
              ]"
            >
              All
            </button>
            <button
              @click="changeMessagesFilter('new')"
              :class="[
                messagesFilter === 'new' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
              ]"
            >
              New
            </button>
            <button
              @click="changeMessagesFilter('read')"
              :class="[
                messagesFilter === 'read' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
              ]"
            >
              Read
            </button>
            <button
              @click="changeMessagesFilter('replied')"
              :class="[
                messagesFilter === 'replied' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
              ]"
            >
              Replied
            </button>
          </div>
        </div>

        <!-- Messages Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div v-if="messagesLoading" class="p-8 text-center text-gray-500">
            Loading messages...
          </div>
          <div v-else-if="messages.length === 0" class="p-8 text-center text-gray-500">
            No messages found
          </div>
          <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="message in messages" :key="message.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(message.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ message.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ message.email }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ message.subject }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(message.status)]">
                    {{ message.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button
                    @click="viewMessage(message)"
                    class="text-[#CF0D0F] hover:text-[#F6211F]"
                  >
                    View
                  </button>
                  <button
                    @click="deleteMessage(message.id)"
                    class="text-red-600 hover:text-red-800"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="messagesPagination.totalPages > 1" class="bg-white px-4 py-3 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ messagesPagination.showing.from }} to {{ messagesPagination.showing.to }} of {{ messagesPagination.showing.total }} results
              </div>
              <div class="flex space-x-2">
                <button
                  @click="changeMessagesPage(messagesPage - 1)"
                  :disabled="messagesPage === 1"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  @click="changeMessagesPage(messagesPage + 1)"
                  :disabled="messagesPage === messagesPagination.totalPages"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Newsletter Subscriptions Tab -->
      <div v-if="activeTab === 'subscriptions'">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ subscriptionStats.total }}</div>
          </div>
          <div class="bg-white rounded-lg border border-green-200 p-4">
            <div class="text-sm text-green-600">Active</div>
            <div class="text-2xl font-bold text-green-900 mt-1">{{ subscriptionStats.active }}</div>
          </div>
          <div class="bg-white rounded-lg border border-red-200 p-4">
            <div class="text-sm text-red-600">Unsubscribed</div>
            <div class="text-2xl font-bold text-red-900 mt-1">{{ subscriptionStats.unsubscribed }}</div>
          </div>
        </div>

        <!-- Filter -->
        <div class="flex space-x-2 mb-4">
          <button
            @click="changeSubscriptionsFilter('all')"
            :class="[
              subscriptionsFilter === 'all' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            All
          </button>
          <button
            @click="changeSubscriptionsFilter('active')"
            :class="[
              subscriptionsFilter === 'active' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            Active
          </button>
          <button
            @click="changeSubscriptionsFilter('unsubscribed')"
            :class="[
              subscriptionsFilter === 'unsubscribed' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            Unsubscribed
          </button>
        </div>

        <!-- Subscriptions Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div v-if="subscriptionsLoading" class="p-8 text-center text-gray-500">
            Loading subscriptions...
          </div>
          <div v-else-if="subscriptions.length === 0" class="p-8 text-center text-gray-500">
            No subscriptions found
          </div>
          <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="subscription in subscriptions" :key="subscription.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(subscription.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ subscription.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ subscription.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(subscription.status)]">
                    {{ subscription.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="deleteSubscription(subscription.id)"
                    class="text-red-600 hover:text-red-800"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="subscriptionsPagination.totalPages > 1" class="bg-white px-4 py-3 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ subscriptionsPagination.showing.from }} to {{ subscriptionsPagination.showing.to }} of {{ subscriptionsPagination.showing.total }} results
              </div>
              <div class="flex space-x-2">
                <button
                  @click="changeSubscriptionsPage(subscriptionsPage - 1)"
                  :disabled="subscriptionsPage === 1"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  @click="changeSubscriptionsPage(subscriptionsPage + 1)"
                  :disabled="subscriptionsPage === subscriptionsPagination.totalPages"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Detail Modal -->
    <div v-if="showMessageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-start">
            <h2 class="text-xl font-semibold text-gray-900">Message Details</h2>
            <button @click="showMessageModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div v-if="selectedMessage" class="p-6 space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-500">From</label>
            <div class="mt-1 text-gray-900">{{ selectedMessage.name }}</div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-500">Email</label>
            <div class="mt-1">
              <a :href="`mailto:${selectedMessage.email}`" class="text-[#CF0D0F] hover:text-[#F6211F]">
                {{ selectedMessage.email }}
              </a>
            </div>
          </div>

          <div v-if="selectedMessage.phone">
            <label class="text-sm font-medium text-gray-500">Phone</label>
            <div class="mt-1">
              <a :href="`tel:${selectedMessage.phone}`" class="text-[#CF0D0F] hover:text-[#F6211F]">
                {{ selectedMessage.phone }}
              </a>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-500">Subject</label>
            <div class="mt-1 text-gray-900">{{ selectedMessage.subject }}</div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-500">Message</label>
            <div class="mt-1 text-gray-900 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg">{{ selectedMessage.message }}</div>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
            <div>
              <label class="text-sm font-medium text-gray-500">Status</label>
              <div class="mt-1">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(selectedMessage.status)]">
                  {{ selectedMessage.status }}
                </span>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Received</label>
              <div class="mt-1 text-sm text-gray-900">{{ formatDate(selectedMessage.created_at) }}</div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-200 flex space-x-3">
            <select
              @change="updateMessageStatus(selectedMessage.id, $event.target.value)"
              v-model="selectedMessage.status"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            >
              <option value="new">New</option>
              <option value="read">Read</option>
              <option value="replied">Replied</option>
              <option value="archived">Archived</option>
            </select>
            <button
              @click="deleteMessage(selectedMessage.id)"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
