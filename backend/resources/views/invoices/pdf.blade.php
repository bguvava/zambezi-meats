<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #B8353D;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #B8353D;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 14px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-paid {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-overdue {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-cancelled {
            background-color: #F3F4F6;
            color: #374151;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 8px;
        }

        .info-column + .info-column {
            background-color: #fff;
        }

        .info-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-text {
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
            display: inline-block;
            width: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #B8353D;
            color: white;
        }

        thead th {
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th.text-right {
            text-align: right;
        }

        tbody td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }

        tbody td.text-right {
            text-align: right;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .totals-section {
            margin-top: 20px;
            text-align: right;
        }

        .totals-table {
            display: inline-block;
            min-width: 300px;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .total-label {
            display: table-cell;
            font-size: 12px;
            color: #666;
            text-align: left;
            padding-right: 40px;
        }

        .total-value {
            display: table-cell;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        .grand-total {
            background-color: #B8353D;
            color: white;
            padding: 12px 0;
            border-radius: 4px;
            margin-top: 10px;
        }

        .grand-total .total-label,
        .grand-total .total-value {
            font-size: 16px;
            font-weight: bold;
            color: white;
            padding: 0 15px;
        }

        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 8px;
            border-left: 4px solid #B8353D;
        }

        .notes-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .notes-text {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .footer-highlight {
            color: #B8353D;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo">Zambezi Meats</div>
                <div class="company-info">
                    Premium Quality Meat Supplier<br>
                    123 Business Street<br>
                    Sydney, NSW 2000, Australia<br>
                    Phone: (02) 1234 5678<br>
                    Email: info@zambeziimeats.com.au
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Invoice & Customer Info -->
        <div class="info-section">
            <div class="info-column">
                <div class="info-title">Bill To:</div>
                <div class="info-text"><strong>{{ $invoice->order->user->name }}</strong></div>
                <div class="info-text">{{ $invoice->order->user->email }}</div>
                <div class="info-text">{{ $invoice->order->user->phone ?? 'N/A' }}</div>
                @if($invoice->order->address)
                <div class="info-text" style="margin-top: 10px;">
                    {{ $invoice->order->address->address_line1 }}<br>
                    @if($invoice->order->address->address_line2)
                    {{ $invoice->order->address->address_line2 }}<br>
                    @endif
                    {{ $invoice->order->address->suburb }}, {{ $invoice->order->address->state }} {{ $invoice->order->address->postcode }}
                </div>
                @endif
            </div>
            <div class="info-column">
                <div class="info-text">
                    <span class="info-label">Invoice Date:</span> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}
                </div>
                <div class="info-text">
                    <span class="info-label">Due Date:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                </div>
                @if($invoice->paid_at)
                <div class="info-text">
                    <span class="info-label">Paid Date:</span> {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d/m/Y') }}
                </div>
                @endif
                <div class="info-text">
                    <span class="info-label">Order Number:</span> #{{ $invoice->order->order_number }}
                </div>
                <div class="info-text">
                    <span class="info-label">Payment Method:</span> {{ strtoupper($invoice->order->payment_method ?? 'N/A') }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th class="text-right" style="width: 15%;">Quantity</th>
                    <th class="text-right" style="width: 17.5%;">Unit Price</th>
                    <th class="text-right" style="width: 17.5%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->delivery_fee > 0)
                <div class="total-row">
                    <span class="total-label">Delivery Fee:</span>
                    <span class="total-value">{{ $invoice->currency }} {{ number_format($invoice->delivery_fee, 2) }}</span>
                </div>
                @endif
                @if($invoice->discount > 0)
                <div class="total-row">
                    <span class="total-label">Discount:</span>
                    <span class="total-value">-{{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</span>
                </div>
                @endif
                <div class="grand-total">
                    <span class="total-label">Total Amount:</span>
                    <span class="total-value">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-title">Notes:</div>
            <div class="notes-text">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This invoice was generated on {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            <p>For questions about this invoice, please contact <span class="footer-highlight">accounts@zambeziimeats.com.au</span></p>
        </div>
    </div>
</body>
</html>
