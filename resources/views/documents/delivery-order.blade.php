<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Delivery Order - {{ $deliveryOrder->do_number }}</title>
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
            font-size: 24px;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            width: 150px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            border: 1px solid #333;
            padding: 8px;
        }

        .barcode-section {
            text-align: center;
            margin: 20px 0;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DELIVERY ORDER</h1>
        <p>{{ $deliveryOrder->do_number }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Delivery Date:</div>
            <div class="info-value">{{ $deliveryOrder->delivery_date->format('d F Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Outbound Number:</div>
            <div class="info-value">{{ $deliveryOrder->outboundOperation->outbound_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sales Order:</div>
            <div class="info-value">{{ $deliveryOrder->outboundOperation->salesOrder->so_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Customer:</div>
            <div class="info-value">{{ $deliveryOrder->outboundOperation->salesOrder->customer->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Customer Address:</div>
            <div class="info-value">{{ $deliveryOrder->outboundOperation->salesOrder->customer->address }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Recipient:</div>
            <div class="info-value">{{ $deliveryOrder->recipient_name }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Driver:</div>
            <div class="info-value">{{ $deliveryOrder->driver->name }} ({{ $deliveryOrder->driver->phone }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Vehicle:</div>
            <div class="info-value">{{ $deliveryOrder->vehicle->license_plate }}
                ({{ $deliveryOrder->vehicle->vehicle_type }})</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 40%;">Product Name</th>
                <th style="width: 15%;">Unit</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 10%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deliveryOrder->outboundOperation->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->unit }}</td>
                    <td style="text-align: right;">{{ number_format($item->shipped_quantity, 0) }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($deliveryOrder->notes)
        <div class="info-section">
            <div class="info-label">Notes:</div>
            <div style="margin-top: 5px;">{{ $deliveryOrder->notes }}</div>
        </div>
    @endif

    <div class="barcode-section">
        <img src="data:image/svg+xml;base64,{{ $deliveryOrder->barcode }}" alt="Barcode"
            style="max-width: 300px; height: auto;">
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div>Prepared By</div>
            <div class="signature-line">
                {{ $deliveryOrder->outboundOperation->preparer->name }}
            </div>
        </div>
        <div class="signature-box">
            <div>Received By</div>
            <div class="signature-line">
                (Recipient Signature)
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Printed on {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>

</html>
