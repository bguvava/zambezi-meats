<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { background: #CF0D0F; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 12px; opacity: 0.9; }
        .company-info { text-align: right; font-size: 10px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { background: #EFEFEF; padding: 8px 10px; font-size: 14px; font-weight: bold; margin-bottom: 10px; border-left: 4px solid #CF0D0F; }
        .summary-grid { display: table; width: 100%; margin-bottom: 20px; }
        .summary-item { display: table-cell; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; text-align: center; }
        .summary-label { font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .summary-value { font-size: 20px; font-weight: bold; color: #CF0D0F; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #4D4B4C; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #EFEFEF; padding: 10px 20px; font-size: 9px; color: #666; border-top: 2px solid #CF0D0F; }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <strong>Zambezi Meats</strong><br>
            Generated: {{ $generated_at }}<br>
            By: {{ $generated_by }}
        </div>
        <h1>{{ $report_title }}</h1>
        <p>Period: {{ $date_range['start'] }} to {{ $date_range['end'] }}</p>
    </div>

    <!-- Summary Statistics -->
    @if(isset($summary))
    <div class="section">
        <div class="section-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Revenue</div>
                <div class="summary-value">${{ $summary['total_revenue'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Orders</div>
                <div class="summary-value">{{ $summary['total_orders'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Avg Order Value</div>
                <div class="summary-value">${{ $summary['avg_order_value'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Items Sold</div>
                <div class="summary-value">{{ $summary['total_items_sold'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Daily Breakdown -->
    @if(isset($daily_breakdown) && count($daily_breakdown) > 0)
    <div class="section">
        <div class="section-title">Daily Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-center">Orders</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daily_breakdown as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td class="text-center">{{ $day['orders'] }}</td>
                    <td class="text-right">${{ number_format($day['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Status Breakdown -->
    @if(isset($status_breakdown) && count($status_breakdown) > 0)
    <div class="section">
        <div class="section-title">Status Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-center">Count</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($status_breakdown as $status)
                <tr>
                    <td>{{ $status['status'] }}</td>
                    <td class="text-center">{{ $status['count'] }}</td>
                    <td class="text-right">${{ number_format($status['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            © {{ date('Y') }} Zambezi Meats. All rights reserved.
        </div>
        <div class="footer-right">
            Page <script type="text/php">
                if (isset($pdf)) {
                    $pdf->page_text(520, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 9, array(0.4, 0.4, 0.4));
                }
            </script>
        </div>
    </div>
</body>
</html>
