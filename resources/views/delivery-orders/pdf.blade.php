<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $deliveryOrder->do_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #000;
        }
        
        .container {
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header p {
            font-size: 8pt;
            color: #333;
        }
        
        .document-info {
            margin-bottom: 12px;
        }
        
        .document-info table {
            width: 100%;
        }
        
        .document-info td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 8.5pt;
        }
        
        .document-info td:first-child {
            width: 110px;
            font-weight: bold;
        }
        
        .document-info td:nth-child(2) {
            width: 8px;
        }
        
        .two-column-section {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 9pt;
            margin: 8px 0 6px 0;
            padding: 4px;
            background-color: #f0f0f0;
            border-left: 3px solid #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
        }
        
        .items-table td:nth-child(1) {
            text-align: center;
            width: 25px;
        }
        
        .items-table td:nth-child(4),
        .items-table td:nth-child(5) {
            text-align: center;
        }
        
        .signature-section {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 5px;
        }
        
        .signature-box p {
            margin-bottom: 40px;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 3px;
            margin: 0 15px;
            font-size: 8pt;
        }
        
        .notes {
            margin-top: 10px;
            padding: 6px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 8pt;
        }
        
        .notes strong {
            display: block;
            margin-bottom: 3px;
        }
        
        .barcode {
            text-align: center;
            margin-top: 12px;
            padding: 6px;
        }
        
        .barcode img {
            height: 35px;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SURAT JALAN</h1>
            <p>BOSCO MANDIRI SYSTEM</p>
        </div>

        <!-- Document Information -->
        <div class="document-info">
            <table>
                <tr>
                    <td>No. Surat Jalan</td>
                    <td>:</td>
                    <td><strong>{{ $deliveryOrder->do_number }}</strong></td>
                </tr>
                <tr>
                    <td>No. Outbound</td>
                    <td>:</td>
                    <td>{{ $deliveryOrder->outboundOperation->outbound_number }}</td>
                </tr>
                <tr>
                    <td>No. Sales Order</td>
                    <td>:</td>
                    <td>{{ $deliveryOrder->outboundOperation->salesOrder->so_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal Pengiriman</td>
                    <td>:</td>
                    <td>{{ $deliveryOrder->delivery_date->format('d F Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Customer & Delivery Information (Two Columns) -->
        <div class="two-column-section">
            <div class="column">
                <div class="section-title">Informasi Pelanggan</div>
                <div class="document-info">
                    <table>
                        <tr>
                            <td>Nama Pelanggan</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->outboundOperation->salesOrder->customer->name }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->outboundOperation->salesOrder->customer->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->outboundOperation->salesOrder->customer->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Penerima</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->recipient_name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="column">
                <div class="section-title">Informasi Pengiriman</div>
                <div class="document-info">
                    <table>
                        <tr>
                            <td>Nama Driver</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->driver->name }}</td>
                        </tr>
                        <tr>
                            <td>No. Telepon</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->driver->phone }}</td>
                        </tr>
                        <tr>
                            <td>Kendaraan</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->vehicle->license_plate }}</td>
                        </tr>
                        <tr>
                            <td>Jenis</td>
                            <td>:</td>
                            <td>{{ $deliveryOrder->vehicle->vehicle_type }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="section-title">Rincian Barang</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Varian</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryOrder->outboundOperation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->productVariant ? $item->productVariant->name : '-' }}</td>
                    <td>{{ number_format($item->shipped_quantity, 0, ',', '.') }}</td>
                    <td>{{ $item->product->unit }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Notes -->
        @if($deliveryOrder->notes || $deliveryOrder->outboundOperation->notes)
        <div class="notes">
            <strong>Catatan:</strong>
            @if($deliveryOrder->notes)
            <p>{{ $deliveryOrder->notes }}</p>
            @endif
            @if($deliveryOrder->outboundOperation->notes)
            <p>{{ $deliveryOrder->outboundOperation->notes }}</p>
            @endif
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Disiapkan Oleh,</p>
                <div class="signature-line">
                    {{ $deliveryOrder->outboundOperation->preparer->name ?? '_______________' }}
                </div>
            </div>
            <div class="signature-box">
                <p>Driver,</p>
                <div class="signature-line">
                    {{ $deliveryOrder->driver->name }}
                </div>
            </div>
            <div class="signature-box">
                <p>Penerima,</p>
                <div class="signature-line">
                    {{ $deliveryOrder->recipient_name ?? '_______________' }}
                </div>
            </div>
        </div>

        <!-- Barcode -->
        @if($deliveryOrder->barcode)
        <div class="barcode">
            <img src="data:image/png;base64,{{ $deliveryOrder->barcode }}" alt="Barcode">
            <p style="font-size: 8pt; margin-top: 5px;">{{ $deliveryOrder->do_number }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }}</p>
            <p>Warehouse Management System - Surat Jalan</p>
        </div>
    </div>
</body>
</html>
