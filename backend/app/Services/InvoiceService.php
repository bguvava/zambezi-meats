<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;

/**
 * Invoice Service.
 *
 * Handles invoice generation and management.
 */
class InvoiceService
{
    /**
     * Generate invoice from order.
     */
    public function generateFromOrder(Order $order): Invoice
    {
        // Check if invoice already exists
        if ($order->invoice) {
            return $order->invoice;
        }

        $invoiceNumber = Invoice::generateInvoiceNumber();
        $issueDate = Carbon::now();
        $dueDate = $issueDate->copy()->addDays(30); // 30 days payment terms

        return Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'discount' => $order->discount,
            'total' => $order->total,
            'currency' => $order->currency,
            'status' => Invoice::STATUS_PENDING,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'notes' => $order->notes,
        ]);
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        $invoice->markAsPaid();
        return $invoice->fresh();
    }

    /**
     * Update invoice status based on due date.
     */
    public function updateOverdueStatus(Invoice $invoice): Invoice
    {
        if ($invoice->status === Invoice::STATUS_PENDING && $invoice->due_date->isPast()) {
            $invoice->update(['status' => Invoice::STATUS_OVERDUE]);
        }

        return $invoice->fresh();
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        return $invoice->fresh();
    }

    /**
     * Get invoice summary.
     */
    public function getSummary(Invoice $invoice): array
    {
        return [
            'invoice_number' => $invoice->invoice_number,
            'order_number' => $invoice->order->order_number,
            'customer_name' => $invoice->order->user->name,
            'customer_email' => $invoice->order->user->email,
            'issue_date' => $invoice->issue_date->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'subtotal' => $invoice->subtotal,
            'delivery_fee' => $invoice->delivery_fee,
            'discount' => $invoice->discount,
            'total' => $invoice->total,
            'currency' => $invoice->currency,
            'status' => $invoice->status,
            'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
            'is_overdue' => $invoice->isOverdue(),
        ];
    }
}
