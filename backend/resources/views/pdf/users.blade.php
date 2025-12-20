<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zambezi Meats - Users Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
        }
        .header h1 {
            color: #8B0000;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 9px;
        }
        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters h3 {
            font-size: 11px;
            margin-bottom: 5px;
            color: #8B0000;
        }
        .filters p {
            font-size: 9px;
            color: #666;
        }
        .summary {
            margin-bottom: 15px;
            padding: 8px;
            background: #fff8dc;
            border-left: 3px solid #8B0000;
        }
        .summary p {
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background: #8B0000;
            color: white;
        }
        th {
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        tbody tr {
            border-bottom: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        td {
            padding: 6px 8px;
            font-size: 9px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-admin {
            background: #4a1d1d;
            color: white;
        }
        .badge-staff {
            background: #0066cc;
            color: white;
        }
        .badge-customer {
            background: #28a745;
            color: white;
        }
        .badge-active {
            background: #28a745;
            color: white;
        }
        .badge-suspended {
            background: #ffc107;
            color: #333;
        }
        .badge-inactive {
            background: #dc3545;
            color: white;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
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
        <p>Users Report</p>
        <p>6/1053 Old Princes Highway, Engadine, NSW 2233, Australia</p>
    </div>

    @if($filters['search'] || $filters['role'] || $filters['status'])
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if($filters['search'])
            <p><strong>Search:</strong> {{ $filters['search'] }}</p>
        @endif
        @if($filters['role'])
            <p><strong>Role:</strong> {{ ucfirst($filters['role']) }}</p>
        @endif
        @if($filters['status'])
            <p><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
        @endif
    </div>
    @endif

    <div class="summary">
        <p><strong>Total Users:</strong> {{ $totalUsers }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">Name</th>
                <th style="width: 25%">Email</th>
                <th style="width: 15%">Phone</th>
                <th style="width: 12%">Role</th>
                <th style="width: 12%">Status</th>
                <th style="width: 11%">Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-{{ $user->role }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $user->status }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                    No users found matching the selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} Zambezi Meats. All rights reserved. | Generated on {{ $generatedAt }}</p>
    </div>
</body>
</html>
