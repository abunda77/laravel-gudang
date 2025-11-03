<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Supplier List Report</title>
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

        table.suppliers {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.suppliers th {
            background: #1976d2;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.suppliers td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.suppliers tr:nth-child(even) {
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
        <h1>SUPPLIER LIST REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Suppliers:</td>
                <td>{{ number_format($totalSuppliers) }}</td>
            </tr>
            <tr>
                <td>Active Suppliers:</td>
                <td>{{ $suppliers->filter(fn($s) => $s->purchaseOrders->count() > 0)->count() }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Supplier Details</h3>

    <table class="suppliers">
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Contact Person</th>
                <th>Address</th>
                <th>Bank Account</th>
                <th class="text-center">Total POs</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->contact ?? '-' }}</td>
                    <td>{{ $supplier->address ?? '-' }}</td>
                    <td>{{ $supplier->bank_account ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge badge-warning">{{ $supplier->purchaseOrders->count() }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Report shows all registered suppliers and their purchase order history.</p>
    </div>
</body>

</html>