<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            line-height: 1.3;
            font-size: 11px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .logo {
            width: 160px;
            height: auto;
            margin-right: 15px;
        }
        .company-info h2 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }
        .header-right {
            text-align: right;
        }
        .header-right h1 {
            margin: 0 0 5px 0;
            font-size: 22px;
            color: #333;
        }
        .header-right p {
            margin: 2px 0;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-overdue {
            background-color: #f5c6cb;
            color: #721c24;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .info-section {
            flex: 1;
        }
        .info-section h3 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .info-section p {
            margin: 2px 0;
            font-size: 10px;
        }
        .customer-info {
            margin-left: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            margin-top: 10px;
            text-align: right;
        }
        .total-section table {
            width: 250px;
            margin-left: auto;
        }
        .total-section th, .total-section td {
            padding: 5px 8px;
            border: none;
        }
        .total-section tr {
            border-bottom: 1px solid #ddd;
        }
        .total-row {
            font-weight: bold;
            font-size: 12px;
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/logo_light.png') }}" alt="Logo" class="logo">
            <div class="company-info">              
                <p>Jl. Wisma Widah Kulon Blok A No.101</p>
                <p>Surabaya, Jawa Timur</p>
                <p>Phone: +62 878-7750-0088</p>
            </div>
        </div>
        <div class="header-right">
            <h1>INVOICE</h1>
            <p><strong>{{ $invoice->invoice_number }}</strong></p>
            <p>Date: {{ $invoice->invoice_date->format('d/m/Y') }}</p>
            <p>Due: {{ $invoice->due_date->format('d/m/Y') }}</p>
            <span class="status-badge status-{{ strtolower($invoice->payment_status->value) }}">
                {{ ucfirst($invoice->payment_status->value) }}
            </span>
        </div>
    </div>

    <div class="invoice-info">
        <div class="info-section customer-info">
            <h3>Bill To</h3>
            <p><strong>{{ $invoice->salesOrder->customer->name }}</strong></p>
            @if($invoice->salesOrder->customer->address)
                <p>{{ $invoice->salesOrder->customer->address }}</p>
            @endif
            @if($invoice->salesOrder->customer->phone)
                <p>Phone: {{ $invoice->salesOrder->customer->phone }}</p>
            @endif
            @if($invoice->salesOrder->customer->email)
                <p>Email: {{ $invoice->salesOrder->customer->email }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Details</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->salesOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        @if($item->productVariant)
                            <br><small style="color: #666;">Variant: {{ $item->productVariant->name }}</small>
                        @endif
                        @if($item->product->sku)
                            <br><small style="color: #999;">SKU: {{ $item->product->sku }}</small>
                        @endif
                        @if($item->productVariant && $item->productVariant->sku)
                            <small style="color: #999;"> | Variant SKU: {{ $item->productVariant->sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">Rp {{ number_format($invoice->salesOrder->items->sum(function($item) { return $item->quantity * $item->unit_price; }), 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
        <h3 style="margin: 0 0 10px 0; font-size: 13px; color: #333;">Rek Pembayaran :</h3>
        <p style="margin: 3px 0; font-size: 11px; font-weight: bold; color: #333;">BCA 1300-770-220</p>
        <p style="margin: 3px 0; font-size: 11px; color: #666;">a.n Bosco Mandiri</p>
    </div>

    <div class="footer">
        <p>Thank you for your business! | Generated on {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>