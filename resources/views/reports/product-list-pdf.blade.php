<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Product List Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
        }

        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 8px;
        }

        .summary {
            margin-bottom: 15px;
            background: #fff8e1;
            padding: 8px;
            border-radius: 3px;
            border: 1px solid #ffc107;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 3px 0;
            font-size: 9px;
        }

        .summary td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .summary td:last-child {
            text-align: right;
            font-size: 10px;
        }

        .summary .total-value {
            font-size: 12px;
            color: #f57c00;
            font-weight: bold;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.products th {
            background: #1976d2;
            color: white;
            padding: 4px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }

        table.products td {
            padding: 3px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }

        table.products tr:nth-child(even) {
            background: #f9f9f9;
        }

        table.products tr.low-stock {
            background: #fff3cd;
        }

        table.products tr.out-of-stock {
            background: #ffebee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .total-row {
            background: #fff8e1 !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }

        h3 {
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PRODUCT LIST REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Products:</td>
                <td>{{ number_format($totalProducts) }}</td>
            </tr>
            <tr>
                <td>Products with Stock:</td>
                <td>{{ $products->filter(fn($p) => $p->getCurrentStock() > 0)->count() }}</td>
            </tr>
            <tr>
                <td>Products Out of Stock:</td>
                <td>{{ $products->filter(fn($p) => $p->getCurrentStock() == 0)->count() }}</td>
            </tr>
            <tr>
                <td>Low Stock Products:</td>
                <td>{{ $products->filter(fn($p) => $p->getCurrentStock() > 0 && $p->getCurrentStock() < $p->minimum_stock)->count() }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td>Total Inventory Value:</td>
                <td class="total-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Product Details</h3>

    <table class="products">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Current Stock</th>
                <th class="text-right">Selling Price</th>
                <th class="text-right">Purchase Price</th>
                <th class="text-right">Stock Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="{{ $product->getCurrentStock() == 0 ? 'out-of-stock' : ($product->getCurrentStock() < $product->minimum_stock ? 'low-stock' : '') }}">
                    <td><strong>{{ $product->sku }}</strong></td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td class="text-right">
                        @if ($product->getCurrentStock() > 0)
                            @if ($product->getCurrentStock() < $product->minimum_stock)
                                <span class="badge badge-warning">{{ number_format($product->getCurrentStock()) }} {{ $product->unit }}</span>
                            @else
                                <span class="badge badge-success">{{ number_format($product->getCurrentStock()) }} {{ $product->unit }}</span>
                            @endif
                        @else
                            <span class="badge badge-danger">0 {{ $product->unit }}</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($product->getCurrentStock() * $product->purchase_price, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL INVENTORY VALUE:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalValue, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Values are calculated based on current stock levels and purchase prices.</p>
    </div>
</body>

</html>