<!DOCTYPE html>
<html>
<head>
    <title>Revenue Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .meta { margin-bottom: 20px; color: #666; }
        .total { font-weight: bold; margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Revenue Report</h1>
        <p>Smart Cabana Booking System</p>
    </div>

    <div class="meta">
        <p>Report Period: {{ $startDate }} to {{ $endDate }}</p>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Gross Revenue (LKR)</th>
                <th>Platform Commission (LKR)</th>
                <th>Owner Earnings (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row->month }}</td>
                <td>{{ number_format($row->gross_revenue, 2) }}</td>
                <td>{{ number_format($row->platform_commission, 2) }}</td>
                <td>{{ number_format($row->owner_earnings, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Total Gross Revenue: {{ number_format($data->sum('gross_revenue'), 2) }} LKR</p>
        <p>Total Platform Commission: {{ number_format($data->sum('platform_commission'), 2) }} LKR</p>
    </div>
</body>
</html>
