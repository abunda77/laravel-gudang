<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 10px;
        }

        .filter-info {
            margin-bottom: 15px;
            padding: 8px;
            background: #e3f2fd;
            border-radius: 5px;
            font-size: 10px;
        }

        .filter-info table {
            width: 100%;
        }

        .filter-info td {
            padding: 2px 0;
        }

        .filter-info td:first-child {
            font-weight: bold;
            width: 100px;
        }

        .summary {
            margin-bottom: 20px;
            background: #fff8e1;
            padding: 10px;
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
            width: 150px;
        }

        .summary td:last-child {
            text-align: right;
            font-size: 12px;
        }

        table.sales {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }

        table.sales th {
            background: #1976d2;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }

        table.sales td {
            padding: 4px;
            border-bottom: 1px solid #ddd;
        }

        table.sales tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
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
            margin-top: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
            page-break-inside: avoid;
        }

        .insights h3 {
            margin-top: 0;
            font-size: 12px;
            color: #333;
        }

        .insights table {
            width: 100%;
            font-size: 9px;
            margin-top: 5px;
        }

        .insights td {
            padding: 3px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SALES REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ $generatedAt->format('d F Y H:i') }}</p>
    </div>

    <div class="filter-info">
        <table>
            <tr>
                <td>Period:</td>
                <td>{{ \Carbon\Carbon::parse($filters['startDate'])->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($filters['endDate'])->format('d M Y') }}</td>
            </tr>
            @if (!empty($filters['productId']))
                <tr>
                    <td>Product:</td>
                    <td>{{ \App\Models\Product::find($filters['productId'])?->name ?? 'N/A' }}</td>
                </tr>
            @endif
            @if (!empty($filters['customerId']))
                <tr>
                    <td>Customer:</td>
                    <td>{{ \App\Models\Customer::find($filters['customerId'])?->name ?? 'N/A' }}</td>
                </tr>
            @endif
            @if (!empty($filters['salesUserId']))
                <tr>
                    <td>Sales User:</td>
                    <td>{{ \App\Models\User::find($filters['salesUserId'])?->name ?? 'N/A' }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td>Total Transactions:</td>
                <td>{{ number_format($totalTransactions) }}</td>
            </tr>
            <tr>
                <td>Total Items Sold:</td>
                <td>{{ number_format($salesData->count()) }}</td>
            </tr>
            <tr>
                <td>Total Quantity:</td>
                <td>{{ number_format($totalQuantity) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td>Total Sales Value:</td>
                <td style="color: #f57c00; font-size: 14px;"><strong>Rp
                        {{ number_format($totalValue, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 20px; margin-bottom: 5px; font-size: 12px;">Sales Transaction Details</h3>

    <table class="sales">
        <thead>
            <tr>
                <th style="width: 60px;">Date</th>
                <th style="width: 70px;">Outbound #</th>
                <th style="width: 70px;">SO #</th>
                <th>Customer</th>
                <th>Sales User</th>
                <th>Product</th>
                <th class="text-right" style="width: 40px;">Qty</th>
                <th class="text-right" style="width: 70px;">Unit Price</th>
                <th class="text-right" style="width: 80px;">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesData as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}</td>
                    <td>{{ $item['outbound_number'] }}</td>
                    <td>{{ $item['so_number'] }}</td>
                    <td>{{ $item['customer_name'] }}</td>
                    <td>{{ $item['sales_user'] ?? '-' }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="text-right">{{ number_format($item['quantity']) }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ number_format($item['total_value'], 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalQuantity) }}</strong></td>
                <td></td>
                <td class="text-right"><strong>Rp {{ number_format($totalValue, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if ($salesData->isNotEmpty())
        <div class="insights">
            <h3>Sales Analysis</h3>

            <table style="width: 48%; float: left; margin-right: 4%;">
                <tr style="background: #e0e0e0;">
                    <td colspan="2" style="padding: 4px; font-weight: bold;">Top 5 Products by Quantity</td>
                </tr>
                @foreach ($salesData->groupBy('product_name')->map(fn($items) => ['name' => $items->first()['product_name'], 'qty' => $items->sum('quantity')])->sortByDesc('qty')->take(5) as $product)
                    <tr>
                        <td style="padding: 3px;">{{ $product['name'] }}</td>
                        <td style="padding: 3px; text-align: right;">
                            <strong>{{ number_format($product['qty']) }}</strong></td>
                    </tr>
                @endforeach
            </table>

            <table style="width: 48%; float: left;">
                <tr style="background: #e0e0e0;">
                    <td colspan="2" style="padding: 4px; font-weight: bold;">Top 5 Customers by Value</td>
                </tr>
                @foreach ($salesData->groupBy('customer_name')->map(fn($items) => ['name' => $items->first()['customer_name'], 'value' => $items->sum('total_value')])->sortByDesc('value')->take(5) as $customer)
                    <tr>
                        <td style="padding: 3px;">{{ $customer['name'] }}</td>
                        <td style="padding: 3px; text-align: right;"><strong>Rp
                                {{ number_format($customer['value'], 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </table>

            <div style="clear: both;"></div>

            <div style="margin-top: 15px; padding: 8px; background: #e8f5e9; border-radius: 3px;">
                <strong style="font-size: 10px;">Key Metrics:</strong>
                <ul style="margin: 5px 0; padding-left: 15px; font-size: 9px;">
                    <li>Average Transaction Value: Rp
                        {{ number_format($totalTransactions > 0 ? $totalValue / $totalTransactions : 0, 0, ',', '.') }}
                    </li>
                    <li>Average Items per Transaction:
                        {{ $totalTransactions > 0 ? number_format($salesData->count() / $totalTransactions, 1) : 0 }}
                    </li>
                    <li>Average Quantity per Item:
                        {{ $salesData->count() > 0 ? number_format($totalQuantity / $salesData->count(), 1) : 0 }}</li>
                </ul>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>

</html>
