<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Stock Valuation Report</title>
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

        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 5px;
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

        .summary .total-value {
            font-size: 20px;
            color: #f57c00;
            font-weight: bold;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.products th {
            background: #1976d2;
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
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .total-row {
            background: #fff8e1 !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }

        .insights {
            margin-top: 30px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .insights h3 {
            margin-top: 0;
            color: #333;
        }

        .insights ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .insights li {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>STOCK VALUATION REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="filter-info">
        <strong>Category Filter:</strong> {{ $categoryName }}
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Products:</td>
                <td>{{ number_format($totalProducts) }}</td>
            </tr>
            <tr>
                <td>Total Stock Quantity:</td>
                <td>{{ number_format($products->sum('current_stock')) }}</td>
            </tr>
            <tr>
                <td>Products with Stock:</td>
                <td>{{ $products->filter(fn($p) => $p['current_stock'] > 0)->count() }}</td>
            </tr>
            <tr>
                <td>Products Out of Stock:</td>
                <td>{{ $products->filter(fn($p) => $p['current_stock'] == 0)->count() }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td>Total Inventory Value:</td>
                <td class="total-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 30px; margin-bottom: 10px;">Product Valuation Details</h3>

    <table class="products">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Current Stock</th>
                <th class="text-right">Purchase Price</th>
                <th class="text-right">Stock Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products->sortByDesc('stock_value') as $product)
                <tr class="{{ $product['current_stock'] == 0 ? 'out-of-stock' : '' }}">
                    <td>{{ $product['sku'] }}</td>
                    <td>{{ $product['name'] }}</td>
                    <td>{{ $product['category'] ?? '-' }}</td>
                    <td class="text-right">
                        @if ($product['current_stock'] > 0)
                            <span class="badge badge-success">{{ number_format($product['current_stock']) }}</span>
                        @else
                            <span class="badge badge-danger">0</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($product['purchase_price'], 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp
                            {{ number_format($product['stock_value'], 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL INVENTORY VALUE:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalValue, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if ($products->isNotEmpty())
        <div class="insights">
            <h3>Top 5 Most Valuable Products</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #e0e0e0;">
                        <th style="padding: 5px; text-align: left;">Product Name</th>
                        <th style="padding: 5px; text-align: right;">Stock Value</th>
                        <th style="padding: 5px; text-align: right;">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products->sortByDesc('stock_value')->take(5) as $product)
                        <tr>
                            <td style="padding: 5px;">{{ $product['name'] }}</td>
                            <td style="padding: 5px; text-align: right;">Rp
                                {{ number_format($product['stock_value'], 0, ',', '.') }}</td>
                            <td style="padding: 5px; text-align: right;">
                                {{ $totalValue > 0 ? number_format(($product['stock_value'] / $totalValue) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <strong>Key Metrics:</strong>
                <ul>
                    <li>Average Stock Value per Product: Rp
                        {{ number_format($totalProducts > 0 ? $totalValue / $totalProducts : 0, 0, ',', '.') }}</li>
                    <li>Stock Coverage:
                        {{ $totalProducts > 0 ? number_format(($products->filter(fn($p) => $p['current_stock'] > 0)->count() / $totalProducts) * 100, 1) : 0 }}%
                        of products have stock</li>
                </ul>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Values are calculated based on purchase prices and current stock levels.</p>
    </div>
</body>

</html>
