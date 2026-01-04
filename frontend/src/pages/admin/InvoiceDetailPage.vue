<template>
  <div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button @click="$router.back()" class="p-2 hover:bg-gray-100 rounded-lg transition">
          <ArrowLeft class="h-5 w-5 text-gray-600" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            {{ invoice?.invoice_number || 'Loading...' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500">Invoice Details</p>
        </div>
      </div>
      <div v-if="invoice" class="flex space-x-2">
        <button @click="downloadPDF"
          class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          <Download class="h-5 w-5 mr-2" />
          Download PDF
        </button>
        <button v-if="invoice.status !== 'paid'" @click="markAsPaid"
          class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
          <CheckCircle class="h-5 w-5 mr-2" />
          Mark as Paid
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading invoice...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
      <AlertCircle class="h-12 w-12 mx-auto mb-2 text-red-600" />
      <p class="text-red-600">{{ error }}</p>
      <button @click="loadInvoice" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Retry
      </button>
    </div>

    <!-- Invoice Content -->
    <div v-else-if="invoice" class="space-y-6">
      <!-- Status and Info Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <p class="text-sm font-medium text-gray-600">Status</p>
            <span :class="getStatusClass(invoice.status)" class="inline-block mt-1">
              {{ invoice.status }}
            </span>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Issue Date</p>
            <p class="text-sm text-gray-900 mt-1">{{ formatDate(invoice.issue_date) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Due Date</p>
            <p class="text-sm text-gray-900 mt-1">{{ formatDate(invoice.due_date) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Paid Date</p>
            <p class="text-sm text-gray-900 mt-1">
              {{ invoice.paid_at ? formatDate(invoice.paid_at) : 'Not paid' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Customer and Order Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
          <div class="space-y-3">
            <div>
              <p class="text-sm font-medium text-gray-600">Name</p>
              <p class="text-sm text-gray-900">{{ invoice.customer_name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Email</p>
              <p class="text-sm text-gray-900">{{ invoice.customer_email }}</p>
            </div>
            <div v-if="invoice.order?.address">
              <p class="text-sm font-medium text-gray-600">Delivery Address</p>
              <p class="text-sm text-gray-900">
                {{ invoice.order.address.address_line1 }}<br>
                <span v-if="invoice.order.address.address_line2">
                  {{ invoice.order.address.address_line2 }}<br>
                </span>
                {{ invoice.order.address.suburb }}, {{ invoice.order.address.state }} {{ invoice.order.address.postcode
                }}
              </p>
            </div>
          </div>
        </div>

        <!-- Order Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
          <div class="space-y-3">
            <div>
              <p class="text-sm font-medium text-gray-600">Order Number</p>
              <p class="text-sm text-gray-900">#{{ invoice.order_number }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Order Date</p>
              <p class="text-sm text-gray-900">{{ formatDate(invoice.order?.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Payment Method</p>
              <p class="text-sm text-gray-900">{{ invoice.order?.payment_method || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Order Status</p>
              <p class="text-sm text-gray-900">{{ invoice.order?.status || 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Items</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Product
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Quantity
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Unit Price
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in invoice.order?.items" :key="item.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.product_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                  {{ formatCurrency(item.price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                  {{ formatCurrency(item.quantity * item.price) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals -->
        <div class="mt-6 border-t border-gray-200 pt-4">
          <div class="flex justify-end">
            <div class="w-full max-w-xs space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-900 font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
              </div>
              <div v-if="invoice.delivery_fee > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Delivery Fee</span>
                <span class="text-gray-900 font-medium">{{ formatCurrency(invoice.delivery_fee) }}</span>
              </div>
              <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Discount</span>
                <span class="text-gray-900 font-medium">-{{ formatCurrency(invoice.discount) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                <span class="text-gray-900">Total</span>
                <span class="text-gray-900">{{ formatCurrency(invoice.total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="invoice.notes" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes</h3>
        <p class="text-sm text-gray-700">{{ invoice.notes }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminDashboard } from '@/services/dashboard';
import { ArrowLeft, CheckCircle, AlertCircle, Download } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();

const invoice = ref(null);
const loading = ref(false);
const error = ref(null);

const loadInvoice = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await adminDashboard.getInvoice(route.params.id);
    if (response.success) {
      invoice.value = response.data;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load invoice';
    console.error('Error loading invoice:', err);
  } finally {
    loading.value = false;
  }
};

const markAsPaid = async () => {
  if (!confirm('Mark this invoice as paid?')) return;

  try {
    const response = await adminDashboard.updateInvoiceStatus(invoice.value.id, 'paid');
    if (response.success) {
      invoice.value = response.data;
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update invoice status');
  }
};

const downloadPDF = () => {
  const url = `${import.meta.env.VITE_API_BASE_URL}/admin/invoices/${invoice.value.id}/pdf`;
  window.open(url, '_blank');
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
    month: 'long',
    day: 'numeric',
  });
};

const getStatusClass = (status) => {
  const classes = {
    paid: 'px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800',
    pending: 'px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800',
    overdue: 'px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800',
    cancelled: 'px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800',
    draft: 'px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800',
  };
  return classes[status] || classes.draft;
};

onMounted(() => {
  loadInvoice();
});
</script>
