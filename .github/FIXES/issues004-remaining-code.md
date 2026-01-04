# Remaining Code Implementation - Issues004

This file contains all the remaining code snippets that need to be manually added to complete the implementation.

---

## 1. STAFF-003: Fix Invoice PDF Download

### File: `frontend/src/pages/staff/InvoiceDetailPage.vue`

Find the Download PDF button and replace it with this implementation:

```vue
<script setup>
// ... existing imports ...
import { toast } from "vue-sonner";
import axios from "axios";

// ... existing code ...

// Add this function
async function downloadPDF() {
  try {
    const response = await axios.get(
      `/api/v1/staff/invoices/${route.params.id}/pdf`,
      {
        responseType: "blob",
      }
    );

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute(
      "download",
      `invoice-${invoice.value.invoice_number}.pdf`
    );
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    toast.success("PDF downloaded successfully");
  } catch (error) {
    console.error("Error downloading PDF:", error);
    toast.error("Failed to download PDF");
  }
}
</script>

<template>
  <!-- ... existing template ... -->

  <!-- Replace the Download PDF button with: -->
  <button
    @click="downloadPDF"
    class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
  >
    <svg
      class="w-4 h-4 mr-2"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
      />
    </svg>
    Download PDF
  </button>
</template>
```

---

## 2. MSG-001: Complete Tickets Tab UI

### File: `frontend/src/pages/admin/MessagesPage.vue`

Add this to the template section, right after the Newsletter Subscriptions section closing `</div>`:

```vue
      <!-- Support Tickets Tab -->
      <div v-if="activeTab === 'tickets'">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
          <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ ticketStats.total }}</div>
          </div>
          <div class="bg-white rounded-lg border border-blue-200 p-4">
            <div class="text-sm text-blue-600">Open</div>
            <div class="text-2xl font-bold text-blue-900 mt-1">{{ ticketStats.open }}</div>
          </div>
          <div class="bg-white rounded-lg border border-yellow-200 p-4">
            <div class="text-sm text-yellow-600">In Progress</div>
            <div class="text-2xl font-bold text-yellow-900 mt-1">{{ ticketStats.in_progress }}</div>
          </div>
          <div class="bg-white rounded-lg border border-green-200 p-4">
            <div class="text-sm text-green-600">Resolved</div>
            <div class="text-2xl font-bold text-green-900 mt-1">{{ ticketStats.resolved }}</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-sm text-gray-600">Closed</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ ticketStats.closed }}</div>
          </div>
        </div>

        <!-- Filter -->
        <div class="flex space-x-2 mb-4">
          <button
            @click="changeTicketsFilter('all')"
            :class="[
              ticketsFilter === 'all' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            All
          </button>
          <button
            @click="changeTicketsFilter('open')"
            :class="[
              ticketsFilter === 'open' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            Open
          </button>
          <button
            @click="changeTicketsFilter('in_progress')"
            :class="[
              ticketsFilter === 'in_progress' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            In Progress
          </button>
          <button
            @click="changeTicketsFilter('resolved')"
            :class="[
              ticketsFilter === 'resolved' ? 'bg-[#CF0D0F] text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
              'px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium'
            ]"
          >
            Resolved
          </button>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div v-if="ticketsLoading" class="p-8 text-center text-gray-500">
            Loading tickets...
          </div>
          <div v-else-if="tickets.length === 0" class="p-8 text-center text-gray-500">
            No tickets found
          </div>
          <table v-else class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(ticket.created_at) }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="font-medium text-gray-900">{{ ticket.user.name }}</div>
                  <div class="text-gray-500 text-xs">{{ ticket.user.email }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ ticket.subject }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    ticket.priority === 'urgent' ? 'bg-red-100 text-red-800' :
                    ticket.priority === 'high' ? 'bg-orange-100 text-orange-800' :
                    ticket.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  ]">
                    {{ ticket.priority }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(ticket.status)]">
                    {{ ticket.status.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button
                    @click="viewTicket(ticket)"
                    class="text-[#CF0D0F] hover:text-[#F6211F]"
                  >
                    View
                  </button>
                  <button
                    @click="deleteTicket(ticket.id)"
                    class="text-red-600 hover:text-red-800"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="ticketsPagination.totalPages > 1" class="bg-white px-4 py-3 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ ticketsPagination.showing.from }} to {{ ticketsPagination.showing.to }} of {{ ticketsPagination.showing.total }} results
              </div>
              <div class="flex space-x-2">
                <button
                  @click="changeTicketsPage(ticketsPage - 1)"
                  :disabled="ticketsPage === 1"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  @click="changeTicketsPage(ticketsPage + 1)"
                  :disabled="ticketsPage === ticketsPagination.totalPages"
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

    <!-- Ticket Detail Modal -->
    <div v-if="showTicketModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Ticket Details</h2>
              <p class="text-sm text-gray-500 mt-1">ID: #{{ selectedTicket.id }}</p>
            </div>
            <button @click="showTicketModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div v-if="selectedTicket" class="p-6 space-y-4">
          <!-- Customer Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Customer</label>
              <div class="mt-1 text-gray-900">{{ selectedTicket.user.name }}</div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Email</label>
              <div class="mt-1">
                <a :href="`mailto:${selectedTicket.user.email}`" class="text-[#CF0D0F] hover:text-[#F6211F]">
                  {{ selectedTicket.user.email }}
                </a>
              </div>
            </div>
          </div>

          <div v-if="selectedTicket.order_number">
            <label class="text-sm font-medium text-gray-500">Related Order</label>
            <div class="mt-1">
              <a :href="`/admin/orders/${selectedTicket.order_id}`" class="text-[#CF0D0F] hover:text-[#F6211F]">
                Order #{{ selectedTicket.order_number }}
              </a>
            </div>
          </div>

          <!-- Ticket Info -->
          <div>
            <label class="text-sm font-medium text-gray-500">Subject</label>
            <div class="mt-1 text-gray-900 font-medium">{{ selectedTicket.subject }}</div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-500">Original Message</label>
            <div class="mt-1 text-gray-900 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg">{{ selectedTicket.message }}</div>
          </div>

          <!-- Priority & Status -->
          <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
            <div>
              <label class="text-sm font-medium text-gray-500">Priority</label>
              <div class="mt-1">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  selectedTicket.priority === 'urgent' ? 'bg-red-100 text-red-800' :
                  selectedTicket.priority === 'high' ? 'bg-orange-100 text-orange-800' :
                  selectedTicket.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ selectedTicket.priority }}
                </span>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Status</label>
              <div class="mt-1">
                <select
                  @change="updateTicketStatus(selectedTicket.id, $event.target.value)"
                  v-model="selectedTicket.status"
                  class="px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] text-sm"
                >
                  <option value="open">Open</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Created</label>
              <div class="mt-1 text-sm text-gray-900">{{ formatDate(selectedTicket.created_at) }}</div>
            </div>
          </div>

          <!-- Replies -->
          <div v-if="selectedTicket.replies && selectedTicket.replies.length > 0" class="pt-4 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Conversation History</h3>
            <div class="space-y-3 max-h-64 overflow-y-auto">
              <div v-for="reply in selectedTicket.replies" :key="reply.id"
                :class="[
                  'p-4 rounded-lg',
                  reply.is_staff ? 'bg-blue-50 ml-8' : 'bg-gray-50 mr-8'
                ]">
                <div class="flex justify-between items-start mb-2">
                  <div class="flex items-center space-x-2">
                    <span :class="[
                      'text-sm font-medium',
                      reply.is_staff ? 'text-blue-900' : 'text-gray-900'
                    ]">
                      {{ reply.user.name }}
                    </span>
                    <span v-if="reply.is_staff" class="px-2 py-0.5 bg-blue-200 text-blue-800 text-xs rounded-full">
                      Staff
                    </span>
                  </div>
                  <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ reply.message }}</p>
              </div>
            </div>
          </div>

          <!-- Reply Form -->
          <div class="pt-4 border-t border-gray-200">
            <label class="text-sm font-medium text-gray-700 block mb-2">Add Reply</label>
            <textarea
              v-model="ticketReplyMessage"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
              placeholder="Type your reply here..."
            ></textarea>
            <div class="flex justify-end gap-3 mt-3">
              <button
                @click="deleteTicket(selectedTicket.id)"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
              >
                Delete Ticket
              </button>
              <button
                @click="replyToTicket"
                :disabled="!ticketReplyMessage.trim()"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Send Reply
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
```

Also update the tabs section to include the Tickets tab button. Find the tabs section and add this button after the Newsletter Subscriptions button:

```vue
<button
  @click="changeTab('tickets')"
  :class="[
    activeTab === 'tickets'
      ? 'border-[#CF0D0F] text-[#CF0D0F]'
      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
    'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
  ]"
>
            Support Tickets
            <span v-if="ticketStats.open > 0" class="ml-2 bg-[#CF0D0F] text-white text-xs px-2 py-0.5 rounded-full">
              {{ ticketStats.open }}
            </span>
          </button>
```

---

## 3. SETTINGS-002: Streamline Settings Groups

### File: `frontend/src/pages/admin/SettingsPage.vue`

Replace the `settingsGroups` array (around line 25):

```javascript
// Settings groups configuration
const settingsGroups = [
  { id: "store", name: "Store Information", icon: Building },
  { id: "payment", name: "Payments Configuration", icon: CreditCard },
  { id: "currency", name: "Currency Configuration", icon: DollarSign },
  { id: "security", name: "Security Configuration", icon: Shield },
];
```

---

## 4. REPORTS-001: Streamline Reports

### File: `frontend/src/pages/admin/ReportsPage.vue`

Replace the entire `reportTypes` array (around line 70) with just a comment:

```javascript
// Report types removed - focus on dashboard metrics only
// Keep only: Revenue Trend, Top Products, Top Customers
```

Then in the template, remove the entire reports section that has all the report type buttons. Keep only:

1. Date range selector
2. Revenue Trend Chart
3. Top Products section
4. Top Customers section

Remove this entire section if it exists:

```vue
<!-- Remove this entire section -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
  <div v-for="report in reportTypes" :key="report.type" ...>
    <!-- All report type cards -->
  </div>
</div>
```

---

## 5. REPORTS-002: PDF Export with Logo

### File: `backend/app/Http/Controllers/Api/V1/ReportController.php`

Find the `export()` method and update it:

```php
public function export(Request $request, string $type)
{
    $this->authorizeAdmin($request);

    $dateRange = $this->getDateRange($request);

    // Get report data based on type
    $reportData = match($type) {
        'revenue' => $this->getRevenueDataForPdf($dateRange),
        'top_products' => $this->getTopProductsDataForPdf($dateRange),
        'top_customers' => $this->getTopCustomersDataForPdf($dateRange),
        default => throw new \InvalidArgumentException("Invalid report type: {$type}")
    };

    $data = [
        'title' => 'Zambezi Meats - ' . ucwords(str_replace('_', ' ', $type)) . ' Report',
        'generated_at' => now()->format('d M Y, H:i'),
        'date_range' => [
            'start' => $dateRange['start']->format('d M Y'),
            'end' => $dateRange['end']->format('d M Y'),
        ],
        'logo' => public_path('images/official_logo.png'),
        'report_type' => $type,
        'report_data' => $reportData,
    ];

    $pdf = Pdf::loadView('pdf.reports', $data);
    $pdf->setPaper('a4', 'portrait');

    return $pdf->download("zambezi-meats-report-{$type}-" . now()->format('Y-m-d') . ".pdf");
}

// Add helper methods for PDF data
private function getRevenueDataForPdf(array $dateRange): array
{
    // ... implementation
    return [
        'total_revenue' => $totalRevenue,
        'daily_breakdown' => $dailyBreakdown,
    ];
}

private function getTopProductsDataForPdf(array $dateRange): array
{
    $topProducts = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
        ->where('status', Order::STATUS_DELIVERED)
        ->with('items.product')
        ->get()
        ->flatMap(fn($order) => $order->items)
        ->groupBy('product_id')
        ->map(function ($items) {
            return [
                'product_name' => $items->first()->product->name,
                'quantity_sold' => $items->sum('quantity'),
                'revenue' => $items->sum('total_price'),
            ];
        })
        ->sortByDesc('revenue')
        ->take(10)
        ->values()
        ->toArray();

    return ['top_products' => $topProducts];
}

private function getTopCustomersDataForPdf(array $dateRange): array
{
    // ... similar implementation
    return ['top_customers' => $topCustomers];
}
```

### Create: `backend/resources/views/pdf/reports.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #CF0D0F;
        }

        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 15px;
        }

        h1 {
            color: #CF0D0F;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .meta {
            color: #666;
            font-size: 11px;
            margin-top: 10px;
        }

        .content {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background-color: #CF0D0F;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .summary-box {
            background-color: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #CF0D0F;
        }

        .summary-box h3 {
            color: #CF0D0F;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists($logo))
            <img src="{{ $logo }}" alt="Zambezi Meats" class="logo">
        @endif
        <h1>{{ $title }}</h1>
        <div class="meta">
            <p>Generated: {{ $generated_at }}</p>
            <p>Report Period: {{ $date_range['start'] }} - {{ $date_range['end'] }}</p>
        </div>
    </div>

    <div class="content">
        @if($report_type === 'revenue')
            <div class="summary-box">
                <h3>Revenue Summary</h3>
                <p><strong>Total Revenue:</strong> ${{ number_format($report_data['total_revenue'], 2) }}</p>
            </div>

            <h2>Daily Breakdown</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Revenue</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report_data['daily_breakdown'] as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td>${{ number_format($day['revenue'], 2) }}</td>
                            <td>{{ $day['orders'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($report_type === 'top_products')
            <h2>Top 10 Products</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report_data['top_products'] as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product['product_name'] }}</td>
                            <td>{{ $product['quantity_sold'] }}</td>
                            <td>${{ number_format($product['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($report_type === 'top_customers')
            <h2>Top 10 Customers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Customer</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report_data['top_customers'] as $index => $customer)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $customer['name'] }}</td>
                            <td>{{ $customer['orders'] }}</td>
                            <td>${{ number_format($customer['total_spent'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Zambezi Meats - Premium Quality Meats</p>
        <p>6/1053 Old Princes Highway, Engadine, NSW 2233, Australia</p>
        <p>This report is confidential and intended for internal use only.</p>
    </div>
</body>
</html>
```

### Copy Logo File

```bash
# Copy the logo to public directory
cp .github/official_logo.png backend/public/images/official_logo.png
```

---

## TESTING COMMANDS

```bash
# Backend tests
cd backend
php artisan test --filter TicketTest  # If you create ticket tests

# Frontend tests
cd frontend
npm run test

# Manual API testing
# Test ticket endpoints
curl http://localhost/api/v1/admin/tickets \
  -H "Authorization: Bearer {admin-token}"

# Test settings save
curl -X PUT http://localhost/api/v1/admin/settings/group/store \
  -H "Authorization: Bearer {admin-token}" \
  -H "Content-Type: application/json" \
  -d '{"store_name":"Test Store"}'

# Check database
SELECT * FROM settings WHERE `key` = 'store_name';
SELECT * FROM support_tickets ORDER BY created_at DESC LIMIT 10;
```

---

**END OF REMAINING CODE**
