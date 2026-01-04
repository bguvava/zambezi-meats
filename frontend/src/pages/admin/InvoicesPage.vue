<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
        <p class="mt-1 text-sm text-gray-500">
          Manage invoices and payment tracking
        </p>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ stats.total_invoices }}
            </p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <FileText class="h-6 w-6 text-blue-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
              {{ stats.paid_invoices }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              {{ formatCurrency(stats.paid_amount) }}
            </p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <CheckCircle class="h-6 w-6 text-green-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">
              {{ stats.pending_invoices }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              {{ formatCurrency(stats.pending_amount) }}
            </p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-full">
            <Clock class="h-6 w-6 text-yellow-600" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Overdue</p>
            <p class="text-2xl font-bold text-red-600 mt-1">
              {{ stats.overdue_invoices }}
            </p>
            <p class="text-sm text-gray-500 mt-1">Total Amount</p>
          </div>
          <div class="p-3 bg-red-100 rounded-full">
            <AlertCircle class="h-6 w-6 text-red-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input v-model="filters.search" type="text" placeholder="Search by invoice number or customer..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @input="debouncedSearch" />
          </div>
        </div>

        <!-- Status Filter -->
        <div>
          <select v-model="filters.status"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="loadInvoices">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <!-- Per Page -->
        <div>
          <select v-model.number="filters.per_page"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="loadInvoices">
            <option :value="15">15 per page</option>
            <option :value="25">25 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Loading invoices...</p>
      </div>

      <div v-else-if="error" class="p-8 text-center text-red-600">
        <AlertCircle class="h-12 w-12 mx-auto mb-2" />
        <p>{{ error }}</p>
        <button @click="loadInvoices" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Retry
        </button>
      </div>

      <div v-else-if="invoices.length === 0" class="p-8 text-center text-gray-500">
        <FileText class="h-12 w-12 mx-auto mb-2 text-gray-400" />
        <p>No invoices found</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Invoice
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Order
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Issue Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Due Date
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ invoice.invoice_number }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ invoice.customer_name }}</div>
                <div class="text-xs text-gray-500">{{ invoice.customer_email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                #{{ invoice.order_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(invoice.total) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(invoice.status)">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.issue_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="viewInvoice(invoice.id)" class="text-blue-600 hover:text-blue-900 mr-3"
                  title="View details">
                  <Eye class="h-5 w-5" />
                </button>
                <button v-if="invoice.status !== 'paid'" @click="updateStatus(invoice.id, 'paid')"
                  class="text-green-600 hover:text-green-900" title="Mark as paid">
                  <CheckCircle class="h-5 w-5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1"
          class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
            {{ pagination.total }} invoices
          </div>
          <div class="flex space-x-2">
            <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
              Previous
            </button>
            <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
              'px-3 py-1 border rounded-lg',
              page === pagination.current_page
                ? 'bg-blue-600 text-white border-blue-600'
                : 'border-gray-300 hover:bg-gray-50'
            ]">
              {{ page }}
            </button>
            <button @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { adminDashboard } from '@/services/dashboard';
import { FileText, Search, Eye, CheckCircle, Clock, AlertCircle } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const invoices = ref([]);
const stats = ref(null);
const loading = ref(false);
const error = ref(null);

const filters = reactive({
  search: '',
  status: '',
  per_page: 15,
  page: 1,
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const pages = [];

  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }

  return pages;
});

// Debounce timer for search
let searchTimer = null;
const debouncedSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    filters.page = 1;
    loadInvoices();
  }, 500);
};

const loadInvoices = async () => {
  loading.value = true;
  error.value = null;

  try {
    const params = {
      page: filters.page,
      per_page: filters.per_page,
    };

    if (filters.search) params.search = filters.search;
    if (filters.status) params.status = filters.status;

    const response = await adminDashboard.getInvoices(params);

    if (response.success) {
      invoices.value = response.data;
      pagination.value = response.pagination;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoices';
    console.error('Error loading invoices:', err);
  } finally {
    loading.value = false;
  }
};

const loadStats = async () => {
  try {
    const response = await adminDashboard.getInvoiceStats();
    if (response.success) {
      stats.value = response.data;
    }
  } catch (err) {
    console.error('Error loading stats:', err);
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.page = page;
    loadInvoices();
  }
};

const viewInvoice = (id) => {
  router.push({ name: 'admin-invoice-detail', params: { id } });
};

const updateStatus = async (id, status) => {
  if (!confirm(`Mark this invoice as ${status}?`)) return;

  try {
    const response = await adminDashboard.updateInvoiceStatus(id, status);
    if (response.success) {
      // Reload invoices and stats
      await Promise.all([loadInvoices(), loadStats()]);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update invoice status');
  }
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD',
  }).format(amount);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusClass = (status) => {
  const classes = {
    paid: 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800',
    pending: 'px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800',
    overdue: 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800',
    cancelled: 'px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800',
    draft: 'px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800',
  };
  return classes[status] || classes.draft;
};

onMounted(() => {
  loadInvoices();
  loadStats();
});
</script>
