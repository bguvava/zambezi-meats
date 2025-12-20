/**
 * Tickets Store
 *
 * Manages customer support tickets state and operations.
 *
 * @requirement CUST-016 Support contact
 * @requirement CUST-017 Ticket submission
 * @requirement CUST-018 View ticket history
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useTicketsStore = defineStore("tickets", () => {
  // State
  const tickets = ref([]);
  const currentTicket = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const isSaving = ref(false);
  const saveError = ref(null);

  // Ticket statuses
  const TICKET_STATUSES = {
    open: {
      label: "Open",
      color: "green",
      bgColor: "bg-green-100 text-green-800",
    },
    in_progress: {
      label: "In Progress",
      color: "blue",
      bgColor: "bg-blue-100 text-blue-800",
    },
    resolved: {
      label: "Resolved",
      color: "purple",
      bgColor: "bg-purple-100 text-purple-800",
    },
    closed: {
      label: "Closed",
      color: "gray",
      bgColor: "bg-gray-100 text-gray-800",
    },
  };

  // Ticket priorities
  const TICKET_PRIORITIES = {
    low: { label: "Low", color: "gray", bgColor: "bg-gray-100 text-gray-600" },
    medium: {
      label: "Medium",
      color: "yellow",
      bgColor: "bg-yellow-100 text-yellow-800",
    },
    high: { label: "High", color: "red", bgColor: "bg-red-100 text-red-800" },
  };

  // Getters
  const openTickets = computed(() =>
    tickets.value.filter((ticket) => ticket.status === "open")
  );

  const inProgressTickets = computed(() =>
    tickets.value.filter((ticket) => ticket.status === "in_progress")
  );

  const resolvedTickets = computed(() =>
    tickets.value.filter((ticket) => ticket.status === "resolved")
  );

  const closedTickets = computed(() =>
    tickets.value.filter((ticket) => ticket.status === "closed")
  );

  const ticketCount = computed(() => tickets.value.length);

  const openTicketCount = computed(() => openTickets.value.length);

  const hasTickets = computed(() => tickets.value.length > 0);

  // Actions

  /**
   * Fetch all tickets
   * @param {Object} options - Query options
   */
  async function fetchTickets(options = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {};
      if (options.status) params.status = options.status;
      if (options.page) params.page = options.page;

      const response = await api.get("/customer/tickets", { params });

      if (response.data.success) {
        tickets.value = response.data.tickets || [];
      } else {
        tickets.value = response.data || [];
      }

      return { success: true, tickets: tickets.value };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch tickets";
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single ticket
   * @param {number} id - Ticket ID
   */
  async function fetchTicket(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/customer/tickets/${id}`);

      if (response.data.success) {
        currentTicket.value = response.data.ticket;
      } else {
        currentTicket.value = response.data;
      }

      return { success: true, ticket: currentTicket.value };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch ticket";
      return { success: false, message: error.value };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Create new ticket
   * @param {Object} data - Ticket data
   */
  async function createTicket(data) {
    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await api.post("/customer/tickets", {
        subject: data.subject,
        message: data.message,
        priority: data.priority || "medium",
        order_id: data.orderId || null,
      });

      if (response.data.success) {
        const newTicket = response.data.ticket;
        tickets.value.unshift(newTicket);
        currentTicket.value = newTicket;
      }

      return {
        success: true,
        message: "Ticket created successfully",
        ticket: response.data.ticket,
      };
    } catch (err) {
      saveError.value =
        err.response?.data?.message || "Failed to create ticket";
      return {
        success: false,
        message: saveError.value,
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isSaving.value = false;
    }
  }

  /**
   * Reply to ticket
   * @param {number} id - Ticket ID
   * @param {string} message - Reply message
   */
  async function replyToTicket(id, message) {
    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await api.post(`/customer/tickets/${id}/reply`, {
        message,
      });

      if (response.data.success && currentTicket.value?.id === id) {
        // Add reply to current ticket
        if (!currentTicket.value.replies) {
          currentTicket.value.replies = [];
        }
        currentTicket.value.replies.push(response.data.reply);

        // Update ticket status if it was reopened
        if (response.data.ticket) {
          currentTicket.value.status = response.data.ticket.status;
        }
      }

      return {
        success: true,
        message: "Reply sent successfully",
        reply: response.data.reply,
      };
    } catch (err) {
      saveError.value = err.response?.data?.message || "Failed to send reply";
      return { success: false, message: saveError.value };
    } finally {
      isSaving.value = false;
    }
  }

  /**
   * Get status config
   * @param {string} status - Status key
   */
  function getStatusConfig(status) {
    return (
      TICKET_STATUSES[status] || {
        label: status,
        color: "gray",
        bgColor: "bg-gray-100 text-gray-600",
      }
    );
  }

  /**
   * Get priority config
   * @param {string} priority - Priority key
   */
  function getPriorityConfig(priority) {
    return (
      TICKET_PRIORITIES[priority] || {
        label: priority,
        color: "gray",
        bgColor: "bg-gray-100 text-gray-600",
      }
    );
  }

  /**
   * Clear current ticket
   */
  function clearCurrentTicket() {
    currentTicket.value = null;
  }

  /**
   * Clear tickets state
   */
  function clearTickets() {
    tickets.value = [];
    currentTicket.value = null;
    error.value = null;
    saveError.value = null;
  }

  /**
   * Clear errors
   */
  function clearErrors() {
    error.value = null;
    saveError.value = null;
  }

  return {
    // State
    tickets,
    currentTicket,
    isLoading,
    error,
    isSaving,
    saveError,

    // Constants
    TICKET_STATUSES,
    TICKET_PRIORITIES,

    // Getters
    openTickets,
    inProgressTickets,
    resolvedTickets,
    closedTickets,
    ticketCount,
    openTicketCount,
    hasTickets,

    // Actions
    fetchTickets,
    fetchTicket,
    createTicket,
    replyToTicket,
    getStatusConfig,
    getPriorityConfig,
    clearCurrentTicket,
    clearTickets,
    clearErrors,
  };
});
