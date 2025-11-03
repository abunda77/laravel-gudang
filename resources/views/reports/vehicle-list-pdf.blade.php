<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Vehicle List Report</title>
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

        table.vehicles {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.vehicles th {
            background: #1976d2;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.vehicles td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.vehicles tr:nth-child(even) {
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

        .badge-warning {
            background: #fff3cd;
            color: #856404;
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
        <h1>VEHICLE LIST REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Vehicles:</td>
                <td>{{ number_format($totalVehicles) }}</td>
            </tr>
            <tr>
                <td>Owned Vehicles:</td>
                <td>{{ $vehicles->where('ownership_status', 'owned')->count() }}</td>
            </tr>
            <tr>
                <td>Rented Vehicles:</td>
                <td>{{ $vehicles->where('ownership_status', 'rented')->count() }}</td>
            </tr>
            <tr>
                <td>Truck Type:</td>
                <td>{{ $vehicles->where('vehicle_type', 'truck')->count() }}</td>
            </tr>
            <tr>
                <td>Van Type:</td>
                <td>{{ $vehicles->where('vehicle_type', 'van')->count() }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Vehicle Details</h3>

    <table class="vehicles">
        <thead>
            <tr>
                <th>License Plate</th>
                <th>Type</th>
                <th>Ownership</th>
                <th class="text-center">Total Deliveries</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehicles as $vehicle)
                <tr>
                    <td><strong>{{ $vehicle->license_plate }}</strong></td>
                    <td>
                        <span class="badge {{ $vehicle->vehicle_type === 'truck' ? 'badge-success' : 'badge-info' }}">
                            {{ ucfirst($vehicle->vehicle_type) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $vehicle->ownership_status === 'owned' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($vehicle->ownership_status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success">{{ $vehicle->deliveryOrders->count() }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Report shows all registered vehicles and their delivery history.</p>
    </div>
</body>

</html>