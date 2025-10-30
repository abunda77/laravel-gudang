<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
        }

        .header p {
            margin: 5px 0;
        }

        .info-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .info-box {
            width: 48%;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #2563eb;
            color: white;
            border: 1px solid #1e40af;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 40%;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .totals-row.grand-total {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
            border: none;
        }

        .payment-info {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
        }

        .payment-status.paid {
            background-color: #10b981;
            color: white;
        }

        .payment-status.unpaid {
            background-color: #f59e0b;
            color: white;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .notes-section {
            margin-top: 20px;
            padding: 10px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p style="font-size: 16px; font-weight: bold;">{{ $invoice->invoice_number }}</p>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3 style="margin-top: 0; color: #2563eb;">Bill To:</h3>
            <div class="info-row">
                <strong>{{ $invoice->salesOrder->customer->name }}</strong>
            </div>
            @if ($invoice->salesOrder->customer->company)
                <div class="info-row">
                    {{ $invoice->salesOrder->customer->company }}
                </div>
            @endif
            <div class="info-row">
                {{ $invoice->salesOrder->customer->address }}
            </div>
            <div class="info-row">
                Email: {{ $invoice->salesOrder->customer->email }}
            </div>
            <div class="info-row">
                Phone: {{ $invoice->salesOrder->customer->phone }}
            </div>
        </div>
        <div class="info-box" style="text-align: right;">
            <div class="info-row">
                <span class="info-label">Invoice Date:</span>
                <span class="info-value">{{ $invoice->invoice_date->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span class="info-value">{{ $invoice->due_date->format('d F Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sales Order:</span>
                <span class="info-value">{{ $invoice->salesOrder->so_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Status:</span>
                <span class="payment-status {{ $invoice->payment_status->value }}">
                    {{ strtoupper($invoice->payment_status->value) }}
                </span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 35%;">Product Name</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 10%; text-align: right;">Quantity</th>
                <th style="width: 12%; text-align: right;">Unit Price</th>
                <th style="width: 13%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
            @endphp
            @foreach ($invoice->salesOrder->items as $index => $item)
                @php
                    $itemSubtotal = $item->quantity * $item->unit_price;
                    $subtotal += $itemSubtotal;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->unit }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 0) }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->unit_price, 0) }}</td>
                    <td style="text-align: right;">Rp {{ number_format($itemSubtotal, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($subtotal, 0) }}</span>
        </div>
        <div class="totals-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($invoice->total_amount, 0) }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    @if ($invoice->salesOrder->notes)
        <div class="notes-section">
            <strong>Notes:</strong><br>
            {{ $invoice->salesOrder->notes }}
        </div>
    @endif

    <div class="payment-info">
        <h4 style="margin-top: 0;">Payment Information</h4>
        <p style="margin: 5px 0;">Please make payment to the following account:</p>
        <p style="margin: 5px 0;"><strong>Bank:</strong> [Your Bank Name]</p>
        <p style="margin: 5px 0;"><strong>Account Number:</strong> [Your Account Number]</p>
        <p style="margin: 5px 0;"><strong>Account Name:</strong> [Your Company Name]</p>
        <p style="margin: 10px 0 5px 0; font-size: 11px; color: #666;">
            Please include invoice number {{ $invoice->invoice_number }} in your payment reference.
        </p>
    </div>

    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Printed on {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>

</html>
