<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.5; }
        .header { background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%); color: white; padding: 25px 30px; margin-bottom: 25px; }
        .header-grid { display: table; width: 100%; }
        .header-left { display: table-cell; width: 60%; vertical-align: middle; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: middle; font-size: 9px; }
        .header h1 { font-size: 22px; margin-bottom: 8px; font-weight: 700; }
        .header .subtitle { font-size: 11px; opacity: 0.95; }
        .company-name { font-size: 16px; font-weight: bold; margin-bottom: 3px; }
        .meta-info { opacity: 0.9; line-height: 1.6; }
        
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { background: #EFEFEF; padding: 10px 15px; font-size: 13px; font-weight: bold; margin-bottom: 12px; border-left: 5px solid #CF0D0F; color: #4D4B4C; }
        
        .summary-grid { display: table; width: 100%; margin-bottom: 25px; border-spacing: 10px 0; }
        .summary-item { display: table-cell; padding: 18px; background: #f9f9f9; border-left: 3px solid #CF0D0F; }
        .summary-item:first-child { border-left: 5px solid #CF0D0F; }
        .summary-label { font-size: 9px; color: #6F6F6F; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        .summary-value { font-size: 22px; font-weight: bold; color: #CF0D0F; }
        
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th { background: #4D4B4C; color: white; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; }
        td { padding: 8px 12px; border-bottom: 1px solid #EFEFEF; }
        tr:nth-child(even) { background: #fafafa; }
        tr:hover { background: #f5f5f5; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 8px; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #EFEFEF; padding: 12px 30px; font-size: 8px; color: #6F6F6F; border-top: 3px solid #CF0D0F; }
        .footer-content { display: table; width: 100%; }
        .footer-left { display: table-cell; width: 50%; }
        .footer-right { display: table-cell; width: 50%; text-align: right; }
        
        .note { background: #fff3cd; padding: 10px; border-left: 3px solid #ffc107; font-size: 9px; margin: 10px 0; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-grid">
            <div class="header-left">
                <h1>{{ $report_title }}</h1>
                <div class="subtitle">Period: {{ $date_range['start'] }} to {{ $date_range['end'] }}</div>
            </div>
            <div class="header-right">
                <div class="company-name">Zambezi Meats</div>
                <div class="meta-info">
                    Generated: {{ $generated_at }}<br>
                    By: {{ $generated_by }}
                </div>
            </div>
        </div>
    </div>

    <!-- Content based on report type -->
    @php
        $content = get_defined_vars();
        unset($content['report_title'], $content['date_range'], $content['generated_at'], $content['generated_by']);
    @endphp

    <!-- Auto-render all data sections -->
    @foreach($content as $key => $value)
        @if(is_array($value))
            @if($key === 'summary')
                <!-- Summary Statistics -->
                <div class="section">
                    <div class="section-title">Summary Statistics</div>
                    <div class="summary-grid">
                        @foreach($value as $label => $val)
                            <div class="summary-item">
                                <div class="summary-label">{{ ucwords(str_replace('_', ' ', $label)) }}</div>
                                <div class="summary-value">{{ is_numeric($val) ? (strpos($val, '.') !== false ? '$' . $val : $val) : $val }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($key === 'revenue' || $key === 'expenses' || $key === 'profit')
                <!-- Financial Metrics -->
                <div class="section">
                    <div class="section-title">{{ ucwords($key) }}</div>
                    <table>
                        @foreach($value as $label => $val)
                        <tr>
                            <td style="font-weight: 600;">{{ ucwords(str_replace('_', ' ', $label)) }}</td>
                            <td class="text-right">{{ is_array($val) ? '' : (is_numeric(str_replace(',', '', $val)) ? '$' . $val : $val) }}</td>
                        </tr>
                        @if(is_array($val))
                            @foreach($val as $sublabel => $subval)
                            <tr>
                                <td style="padding-left: 30px;">{{ ucwords(str_replace('_', ' ', $sublabel)) }}</td>
                                <td class="text-right">{{ is_numeric(str_replace(',', '', $subval)) ? '$' . $subval : $subval }}</td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                    </table>
                </div>
            @elseif(in_array($key, ['products', 'categories', 'customers', 'orders', 'top_customers', 'staff_list', 'method_breakdown', 'recent_orders', 'low_selling', 'no_sales']) && !empty($value))
                <!-- Data Tables -->
                <div class="section">
                    <div class="section-title">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                    <table>
                        <thead>
                            <tr>
                                @php
                                    $firstItem = is_array($value) ? reset($value) : [];
                                    if (is_array($firstItem)) {
                                        foreach(array_keys($firstItem) as $column) {
                                            echo '<th>' . ucwords(str_replace('_', ' ', $column)) . '</th>';
                                        }
                                    }
                                @endphp
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($value as $row)
                                @if(is_array($row))
                                <tr>
                                    @foreach($row as $col => $cell)
                                        @php
                                            $class = '';
                                            if (in_array($col, ['revenue', 'total', 'total_spent', 'amount'])) {
                                                $class = 'text-right text-bold';
                                            } elseif (in_array($col, ['rank', 'orders', 'count', 'quantity', 'stock'])) {
                                                $class = 'text-center';
                                            }
                                        @endphp
                                        <td class="{{ $class }}">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(in_array($key, ['daily_breakdown', 'status_breakdown']) && !empty($value))
                <!-- Breakdown Tables -->
                <div class="section">
                    <div class="section-title">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                    <table>
                        <thead>
                            <tr>
                                @php
                                    $firstItem = reset($value);
                                    foreach(array_keys($firstItem) as $column) {
                                        echo '<th>' . ucwords(str_replace('_', ' ', $column)) . '</th>';
                                    }
                                @endphp
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($value as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(!in_array($key, ['__env', '__data', 'app', 'errors', 'obLevel']))
                <!-- Simple Key-Value Pairs -->
                @if(!is_array($value))
                <div class="section">
                    <div class="section-title">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                    <p style="padding: 10px;">{{ $value }}</p>
                </div>
                @endif
            @endif
        @endif
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                © {{ date('Y') }} Zambezi Meats. All rights reserved.
            </div>
            <div class="footer-right">
                This is a system-generated report
            </div>
        </div>
    </div>
</body>
</html>
