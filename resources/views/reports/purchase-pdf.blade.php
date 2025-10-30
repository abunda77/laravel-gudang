<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Report</title>
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
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #4caf50;
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

        table.purchases {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }

        table.purchases th {
            background: #4caf50;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }

        table.purchases td {
            padding: 4px;
            border-bottom: 1px solid #ddd;
        }

        table.purchases tr:nth-child(even) {
            background: #f9f9f9;
        }

        table.purchases tr.discrepancy {
            background: #fff8e1;
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
            background: #e8f5e9 !important;
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

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            background: #fff3cd;
            color: #856404;
        }

        .alert {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 8px;
            margin-top: 15px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PURCHASE REPORT</h1>
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
            @if (!empty($filters['supplierId']))
                <tr>
                    <td>Supplier:</td>
                    <td>{{ \App\Models\Supplier::find($filters['supplierId'])?->name ?? 'N/A' }}</td>
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
                <td>Total Items Purchased:</td>
                <td>{{ number_format($purchaseData->count()) }}</td>
            </tr>
            <tr>
                <td>Total Quantity Received:</td>
                <td>{{ number_format($totalQuantity) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td>Total Purchase Value:</td>
                <td style="color: #2e7d32; font-size: 14px;"><strong>Rp
                        {{ number_format($totalValue, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <h3 style="margin-top: 20px; margin-bottom: 5px; font-size: 12px;">Purchase Transaction Details</h3>

    <table class="purchases">
        <thead>
            <tr>
                <th style="width: 60px;">Date</th>
                <th style="width: 70px;">Inbound #</th>
                <th style="width: 70px;">PO #</th>
                <th>Supplier</th>
                <th>Product</th>
                <th class="text-right" style="width: 45px;">Ordered</th>
                <th class="text-right" style="width: 45px;">Received</th>
                <th class="text-right" style="width: 70px;">Unit Price</th>
                <th class="text-right" style="width: 80px;">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseData as $item)
                <tr class="{{ $item['ordered_quantity'] != $item['received_quantity'] ? 'discrepancy' : '' }}">
                    <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}</td>
                    <td>{{ $item['inbound_number'] }}</td>
                    <td>{{ $item['po_number'] }}</td>
                    <td>{{ $item['supplier_name'] }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="text-right">{{ number_format($item['ordered_quantity']) }}</td>
                    <td class="text-right">
                        {{ number_format($item['received_quantity']) }}
                        @if ($item['ordered_quantity'] != $item['received_quantity'])
                            <span class="badge">!</span>
                        @endif
                    </td>
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

    @php
        $discrepancies = $purchaseData->filter(fn($item) => $item['ordered_quantity'] != $item['received_quantity']);
    @endphp

    @if ($discrepancies->isNotEmpty())
        <div class="alert">
            <strong>⚠ Quantity Discrepancies:</strong> {{ $discrepancies->count() }} item(s) have differences between
            ordered and received quantities (highlighted in yellow and marked with !).
        </div>
    @endif

    @if ($purchaseData->isNotEmpty())
        <div class="insights">
            <h3>Purchase Analysis</h3>

            <table style="width: 48%; float: left; margin-right: 4%;">
                <tr style="background: #e0e0e0;">
                    <td colspan="2" style="padding: 4px; font-weight: bold;">Top 5 Products by Quantity</td>
                </tr>
                @foreach ($purchaseData->groupBy('product_name')->map(fn($items) => ['name' => $items->first()['product_name'], 'qty' => $items->sum('received_quantity')])->sortByDesc('qty')->take(5) as $product)
                    <tr>
                        <td style="padding: 3px;">{{ $product['name'] }}</td>
                        <td style="padding: 3px; text-align: right;">
                            <strong>{{ number_format($product['qty']) }}</strong></td>
                    </tr>
                @endforeach
            </table>

            <table style="width: 48%; float: left;">
                <tr style="background: #e0e0e0;">
                    <td colspan="2" style="padding: 4px; font-weight: bold;">Top 5 Suppliers by Value</td>
                </tr>
                @foreach ($purchaseData->groupBy('supplier_name')->map(fn($items) => ['name' => $items->first()['supplier_name'], 'value' => $items->sum('total_value')])->sortByDesc('value')->take(5) as $supplier)
                    <tr>
                        <td style="padding: 3px;">{{ $supplier['name'] }}</td>
                        <td style="padding: 3px; text-align: right;"><strong>Rp
                                {{ number_format($supplier['value'], 0, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </table>

            <div style="clear: both;"></div>

            <div style="margin-top: 15px; padding: 8px; background: #e3f2fd; border-radius: 3px;">
                <strong style="font-size: 10px;">Key Metrics:</strong>
                <ul style="margin: 5px 0; padding-left: 15px; font-size: 9px;">
                    <li>Average Transaction Value: Rp
                        {{ number_format($totalTransactions > 0 ? $totalValue / $totalTransactions : 0, 0, ',', '.') }}
                    </li>
                    <li>Average Items per Transaction:
                        {{ $totalTransactions > 0 ? number_format($purchaseData->count() / $totalTransactions, 1) : 0 }}
                    </li>
                    <li>Average Quantity per Item:
                        {{ $purchaseData->count() > 0 ? number_format($totalQuantity / $purchaseData->count(), 1) : 0 }}
                    </li>
                    <li>Fulfillment Rate:
                        {{ $purchaseData->sum('ordered_quantity') > 0 ? number_format(($purchaseData->sum('received_quantity') / $purchaseData->sum('ordered_quantity')) * 100, 1) : 0 }}%
                    </li>
                </ul>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>

</html>
