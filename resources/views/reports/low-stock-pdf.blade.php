<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Low Stock Report</title>
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
            margin-bottom: 20px;
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffc107;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 5px 0;
        }

        .summary td:first-child {
            font-weight: bold;
            width: 200px;
        }

        .summary td:last-child {
            text-align: right;
            font-size: 16px;
            color: #d32f2f;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.products th {
            background: #d32f2f;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.products td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.products tr:nth-child(even) {
            background: #f9f9f9;
        }

        table.products tr.critical {
            background: #ffebee;
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

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-critical {
            background: #d32f2f;
            color: white;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .alert {
            background: #ffebee;
            border-left: 4px solid #d32f2f;
            padding: 10px;
            margin-bottom: 20px;
        }

        .alert strong {
            color: #d32f2f;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LOW STOCK REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    @if ($products->isNotEmpty())
        <div class="alert">
            <strong>⚠ ATTENTION:</strong> The following products are below their minimum stock threshold and require
            immediate attention.
        </div>

        <div class="summary">
            <table>
                <tr>
                    <td>Total Products Below Minimum:</td>
                    <td><strong>{{ $products->count() }}</strong></td>
                </tr>
                <tr>
                    <td>Total Shortage Quantity:</td>
                    <td><strong>{{ $products->sum('shortage') }}</strong></td>
                </tr>
                <tr>
                    <td>Products Out of Stock:</td>
                    <td><strong>{{ $products->filter(fn($p) => $p['current_stock'] == 0)->count() }}</strong></td>
                </tr>
            </table>
        </div>

        <table class="products">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th class="text-right">Current Stock</th>
                    <th class="text-right">Minimum Stock</th>
                    <th class="text-right">Shortage</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products->sortByDesc('shortage') as $product)
                    <tr class="{{ $product['current_stock'] == 0 ? 'critical' : '' }}">
                        <td>{{ $product['sku'] }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['category'] ?? '-' }}</td>
                        <td class="text-right">
                            <span class="badge badge-danger">{{ $product['current_stock'] }}</span>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-warning">{{ $product['minimum_stock'] }}</span>
                        </td>
                        <td class="text-right">
                            <strong style="color: #d32f2f;">{{ $product['shortage'] }}</strong>
                        </td>
                        <td class="text-center">
                            @if ($product['current_stock'] == 0)
                                <span class="badge badge-critical">OUT OF STOCK</span>
                            @else
                                <span class="badge badge-danger">LOW STOCK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px;">
            <strong>Recommendations:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Prioritize restocking products marked as "OUT OF STOCK"</li>
                <li>Create purchase orders for products with high shortage quantities</li>
                <li>Review minimum stock thresholds for frequently low-stock items</li>
                <li>Consider increasing safety stock for critical products</li>
            </ul>
        </div>
    @else
        <div style="text-align: center; padding: 40px; background: #e8f5e9; border-radius: 5px;">
            <h2 style="color: #2e7d32; margin: 0;">✓ All Stock Levels are Good!</h2>
            <p style="color: #666; margin-top: 10px;">No products are currently below their minimum stock threshold.</p>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Please take immediate action on products marked as critical.</p>
    </div>
</body>

</html>
