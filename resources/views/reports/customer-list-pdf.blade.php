<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Customer List Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .summary {
            margin-bottom: 30px;
            background: #fff8e1;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffc107;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 8px 0;
        }

        .summary td:first-child {
            font-weight: bold;
            width: 200px;
        }

        .summary td:last-child {
            text-align: right;
            font-size: 16px;
        }

        table.customers {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.customers th {
            background: #1976d2;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.customers td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.customers tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>CUSTOMER LIST REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Customers:</td>
                <td>{{ number_format($totalCustomers) }}</td>
            </tr>
            <tr>
                <td>Wholesale Customers:</td>
                <td>{{ $customers->where('type', 'wholesale')->count() }}</td>
            </tr>
            <tr>
                <td>Retail Customers:</td>
                <td>{{ $customers->where('type', 'retail')->count() }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Customer Details</h3>

    <table class="customers">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Company</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Type</th>
                <th class="text-center">Total Orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->company ?? '-' }}</td>
                    <td>{{ $customer->email ?? '-' }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>
                        @if ($customer->type)
                            <span class="badge {{ $customer->type === 'wholesale' ? 'badge-success' : 'badge-info' }}">
                                {{ ucfirst($customer->type) }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success">{{ $customer->salesOrders->count() }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Report shows all registered customers and their order history.</p>
    </div>
</body>

</html>