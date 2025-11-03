<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Driver List Report</title>
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

        table.drivers {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.drivers th {
            background: #1976d2;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.drivers td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.drivers tr:nth-child(even) {
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

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .photo-cell {
            width: 60px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DRIVER LIST REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Drivers:</td>
                <td>{{ number_format($totalDrivers) }}</td>
            </tr>
            <tr>
                <td>Active Drivers:</td>
                <td>{{ $drivers->filter(fn($d) => $d->deliveryOrders->count() > 0)->count() }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Driver Details</h3>

    <table class="drivers">
        <thead>
            <tr>
                <th class="photo-cell">Photo</th>
                <th>Driver Name</th>
                <th>Phone</th>
                <th>ID Card</th>
                <th class="text-center">Total Deliveries</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($drivers as $driver)
                <tr>
                    <td class="photo-cell">
                        @if ($driver->photo)
                            <img src="{{ asset('storage/' . $driver->photo) }}" alt="Photo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e0e0; display: inline-block;"></div>
                        @endif
                    </td>
                    <td><strong>{{ $driver->name }}</strong></td>
                    <td>{{ $driver->phone ?? '-' }}</td>
                    <td>{{ $driver->id_card_number ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge badge-success">{{ $driver->deliveryOrders->count() }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Report shows all registered drivers and their delivery history.</p>
    </div>
</body>

</html>