<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Order Details Report</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @foreach ($orders as $item)
            @php $subtotal += $item->amount; @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                    <td>${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                <td>${{ number_format($subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
