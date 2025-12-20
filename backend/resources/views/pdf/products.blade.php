<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Export - Zambezi Meats</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #d32f2f;
        }

        .header h1 {
            font-size: 20pt;
            color: #d32f2f;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12pt;
            color: #666;
        }

        .meta-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }

        .meta-info table {
            width: 100%;
        }

        .meta-info td {
            padding: 3px 0;
        }

        .meta-info .label {
            font-weight: bold;
            width: 120px;
        }

        .filters {
            margin-bottom: 15px;
            padding: 8px;
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
        }

        .filters strong {
            color: #1976d2;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.products thead {
            background: #d32f2f;
            color: white;
        }

        table.products th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }

        table.products td {
            padding: 6px 5px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9pt;
        }

        table.products tbody tr:nth-child(even) {
            background: #fafafa;
        }

        table.products tbody tr:hover {
            background: #f5f5f5;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .status-active {
            background: #4caf50;
            color: white;
        }

        .status-inactive {
            background: #9e9e9e;
            color: white;
        }

        .stock-low {
            color: #ff9800;
            font-weight: bold;
        }

        .stock-out {
            color: #f44336;
            font-weight: bold;
        }

        .stock-ok {
            color: #4caf50;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-top: 2px solid #d32f2f;
        }

        .summary table {
            width: 50%;
            margin-left: auto;
        }

        .summary td {
            padding: 4px 0;
        }

        .summary .label {
            font-weight: bold;
            text-align: right;
            padding-right: 15px;
        }

        .summary .value {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Zambezi Meats</h1>
        <div class="subtitle">Products Export Report</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Generated:</td>
                <td>{{ $generated_at }}</td>
                <td class="label">Total Products:</td>
                <td>{{ $total_products }}</td>
            </tr>
            <tr>
                <td class="label">Stock Value:</td>
                <td>A${{ number_format($total_stock_value, 2) }}</td>
                <td class="label"></td>
                <td></td>
            </tr>
        </table>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Applied Filters:</strong>
        @if(isset($filters['category']))
            Category: {{ $filters['category'] }} &nbsp;|&nbsp;
        @endif
        @if(isset($filters['status']))
            Status: {{ ucfirst($filters['status']) }} &nbsp;|&nbsp;
        @endif
        @if(isset($filters['stock_status']))
            Stock Status: {{ ucfirst(str_replace('_', ' ', $filters['stock_status'])) }} &nbsp;|&nbsp;
        @endif
        @if(isset($filters['search']))
            Search: "{{ $filters['search'] }}"
        @endif
    </div>
    @endif

    <table class="products">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th style="text-align: right;">Price (AUD)</th>
                <th style="text-align: right;">Sale Price</th>
                <th style="text-align: center;">Stock</th>
                <th style="text-align: center;">Unit</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td style="text-align: right;">A${{ number_format($product->price_aud, 2) }}</td>
                <td style="text-align: right;">
                    @if($product->sale_price_aud)
                        A${{ number_format($product->sale_price_aud, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">
                    @if($product->stock == 0)
                        <span class="stock-out">{{ $product->stock }}</span>
                    @elseif($product->stock < 10)
                        <span class="stock-low">{{ $product->stock }}</span>
                    @else
                        <span class="stock-ok">{{ $product->stock }}</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $product->unit }}</td>
                <td style="text-align: center;">
                    @if($product->is_active)
                        <span class="status-badge status-active">Active</span>
                    @else
                        <span class="status-badge status-inactive">Inactive</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Products:</td>
                <td class="value">{{ $total_products }}</td>
            </tr>
            <tr>
                <td class="label">Total Stock Value:</td>
                <td class="value">A${{ number_format($total_stock_value, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div>© {{ date('Y') }} Zambezi Meats - Premium Quality Butchery</div>
        <div>Generated on {{ $generated_at }} | Page <span class="pagenum"></span></div>
    </div>
</body>
</html>
