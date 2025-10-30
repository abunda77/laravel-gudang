<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Stock Card Report - {{ $product->sku }}</title>
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

        .product-info {
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }

        .product-info table {
            width: 100%;
        }

        .product-info td {
            padding: 3px 0;
        }

        .product-info td:first-child {
            font-weight: bold;
            width: 150px;
        }

        table.movements {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.movements th {
            background: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table.movements td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }

        table.movements tr:nth-child(even) {
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

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .summary {
            margin-top: 30px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 5px 0;
        }

        .summary td:first-child {
            font-weight: bold;
        }

        .summary td:last-child {
            text-align: right;
            font-size: 14px;
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
        <h1>STOCK CARD REPORT</h1>
        <p>{{ config('app.name', 'Warehouse Management System') }}</p>
        <p>Generated on {{ now()->format('d F Y H:i') }}</p>
    </div>

    <div class="product-info">
        <table>
            <tr>
                <td>Product SKU:</td>
                <td>{{ $product->sku }}</td>
            </tr>
            <tr>
                <td>Product Name:</td>
                <td>{{ $product->name }}</td>
            </tr>
            <tr>
                <td>Category:</td>
                <td>{{ $product->category->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Unit:</td>
                <td>{{ $product->unit }}</td>
            </tr>
            <tr>
                <td>Report Period:</td>
                <td>
                    @if ($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @elseif($startDate)
                        From {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    @elseif($endDate)
                        Until {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else
                        All Time
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="movements">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Running Balance</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @php
                            $badgeClass = match ($movement->type->value) {
                                'inbound' => 'badge-success',
                                'outbound' => 'badge-danger',
                                'adjustment_plus' => 'badge-info',
                                'adjustment_minus' => 'badge-warning',
                                default => 'badge-info',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ str_replace('_', ' ', ucfirst($movement->type->value)) }}
                        </span>
                    </td>
                    <td>
                        @if ($movement->reference)
                            @php
                                $type = class_basename($movement->reference_type);
                                $number = match ($type) {
                                    'InboundOperation' => $movement->reference->inbound_number ?? '-',
                                    'OutboundOperation' => $movement->reference->outbound_number ?? '-',
                                    'StockOpname' => $movement->reference->opname_number ?? '-',
                                    default => '-',
                                };
                            @endphp
                            {{ $type }}: {{ $number }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if ($movement->quantity >= 0)
                            <span style="color: #155724;">+{{ $movement->quantity }}</span>
                        @else
                            <span style="color: #721c24;">{{ $movement->quantity }}</span>
                        @endif
                    </td>
                    <td class="text-right"><strong>{{ $movement->running_balance }}</strong></td>
                    <td>{{ $movement->creator->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No movements found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Total Movements:</td>
                <td>{{ $movements->count() }}</td>
            </tr>
            <tr>
                <td>Final Stock Quantity:</td>
                <td><strong>{{ $movements->last()->running_balance ?? 0 }}</strong></td>
            </tr>
            <tr>
                <td>Purchase Price per Unit:</td>
                <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Stock Value:</td>
                <td><strong>Rp
                        {{ number_format(($movements->last()->running_balance ?? 0) * $product->purchase_price, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>

</html>
