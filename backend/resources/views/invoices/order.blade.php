<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif;
        }

        body {
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #CF0D0F;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #CF0D0F;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #CF0D0F;
            margin-bottom: 10px;
        }

        .invoice-meta {
            font-size: 11px;
            color: #666;
        }

        .invoice-meta strong {
            color: #333;
        }

        /* Billing/Shipping Info */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-box-title {
            font-size: 11px;
            font-weight: bold;
            color: #CF0D0F;
            text-transform: uppercase;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .info-content {
            font-size: 11px;
            line-height: 1.6;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #CF0D0F;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 10px 8px;
            text-align: left;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table th:nth-child(2),
        .items-table th:nth-child(3),
        .items-table td:nth-child(2),
        .items-table td:nth-child(3) {
            text-align: center;
        }

        .items-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .product-name {
            font-weight: 600;
            color: #333;
        }

        .product-sku {
            font-size: 9px;
            color: #999;
            margin-top: 2px;
        }

        /* Summary */
        .summary-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .summary-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .summary-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .summary-table tr:last-child {
            background: #CF0D0F;
            color: #fff;
        }

        .summary-table tr:last-child td {
            font-size: 14px;
            font-weight: bold;
            padding: 12px 10px;
        }

        .payment-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-size: 10px;
        }

        .payment-info-title {
            font-weight: bold;
            color: #CF0D0F;
            margin-bottom: 8px;
        }

        /* Footer */
        .footer {
            border-top: 2px solid #CF0D0F;
            padding-top: 20px;
            text-align: center;
        }

        .footer p {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .footer .thank-you {
            font-size: 14px;
            font-weight: bold;
            color: #CF0D0F;
            margin-bottom: 10px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-confirmed { background: #DBEAFE; color: #1E40AF; }
        .status-processing { background: #E9D5FF; color: #7C3AED; }
        .status-ready { background: #D1FAE5; color: #065F46; }
        .status-out_for_delivery { background: #E0E7FF; color: #3730A3; }
        .status-delivered { background: #D1FAE5; color: #065F46; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">Zambezi Meats</div>
                <div class="company-info">
                    Premium Quality Meats<br>
                    123 Meat Street, Sydney NSW 2000<br>
                    Phone: (02) 1234 5678<br>
                    Email: orders@zambezimeats.com.au<br>
                    ABN: 12 345 678 901
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Invoice #:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                    <strong>Due Date:</strong> {{ $order->created_at->format('d M Y') }}<br>
                    <strong>Status:</strong>
                    <span class="status-badge status-{{ $order->status }}">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Billing & Shipping Info -->
        <div class="info-section">
            <div class="info-box">
                <div class="info-box-title">Bill To</div>
                <div class="info-content">
                    <strong>{{ $order->user->name }}</strong><br>
                    {{ $order->user->email }}<br>
                    @if($order->user->phone)
                        Phone: {{ $order->user->phone }}
                    @endif
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-title">Ship To</div>
                <div class="info-content">
                    @if($order->address)
                        <strong>{{ $order->address->label ?? 'Delivery Address' }}</strong><br>
                        {{ $order->address->street }}<br>
                        @if($order->address->suburb)
                            {{ $order->address->suburb }},
                        @endif
                        {{ $order->address->city }} {{ $order->address->state }} {{ $order->address->postcode }}
                    @else
                        <em>No delivery address specified</em>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Product</th>
                    <th style="width: 15%">Qty</th>
                    <th style="width: 15%">Unit Price</th>
                    <th style="width: 20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">{{ $item->product_name }}</div>
                            @if($item->product && $item->product->sku)
                                <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-left">
                <div class="payment-info">
                    <div class="payment-info-title">Payment Information</div>
                    <strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Card')) }}<br>
                    <strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'Paid') }}<br>
                    @if($order->notes)
                        <br><strong>Order Notes:</strong><br>
                        {{ $order->notes }}
                    @endif
                </div>
            </div>
            <div class="summary-right">
                <table class="summary-table">
                    <tr>
                        <td>Subtotal:</td>
                        <td>${{ number_format($order->subtotal ?? $order->total, 2) }}</td>
                    </tr>
                    @if($order->discount && $order->discount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td>-${{ number_format($order->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Shipping:</td>
                        <td>${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax (GST):</td>
                        <td>${{ number_format($order->tax ?? ($order->total * 0.1), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total:</td>
                        <td>${{ number_format($order->total, 2) }} AUD</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="thank-you">Thank you for your order!</p>
            <p>If you have any questions about this invoice, please contact us at orders@zambezimeats.com.au</p>
            <p>Zambezi Meats © {{ date('Y') }} | www.zambezimeats.com.au</p>
        </div>
    </div>
</body>
</html>
