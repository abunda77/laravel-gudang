<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Report - {{ $month->format('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
            color: #1a1a1a;
        }

        .header .subtitle {
            font-size: 11pt;
            color: #666;
            margin-bottom: 3px;
        }

        .header .period {
            font-size: 10pt;
            color: #888;
        }

        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table thead {
            background-color: #2c3e50;
            color: white;
        }

        table thead th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #1a252f;
        }

        table tbody td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f4f8;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .summary-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .summary-value {
            font-weight: bold;
            color: #3498db;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PRODUCT REPORT</h1>
        <div class="subtitle">Laporan Produk</div>
        <div class="period">Per {{ $generatedAt->format('d F Y H:i:s') }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Total Produk:</span>
            <span class="info-value">{{ number_format($reportData['total_products']) }} items</span>
        </div>
        <div class="info-row">
            <span class="info-label">Produk Low Stock:</span>
            <span class="info-value">{{ number_format($reportData['low_stock_count']) }} items</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Nilai Beli:</span>
            <span class="info-value">Rp {{ number_format($reportData['total_purchase_value'], 0, ',', '.') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Nilai Jual:</span>
            <span class="info-value">Rp {{ number_format($reportData['total_selling_value'], 0, ',', '.') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Potensi Profit:</span>
            <span class="info-value">Rp {{ number_format($reportData['total_potential_profit'], 0, ',', '.') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">SKU</th>
                <th style="width: 20%;">Nama Produk</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 7%;">Stok</th>
                <th style="width: 7%;">Min</th>
                <th style="width: 10%;">Harga Beli</th>
                <th style="width: 10%;">Harga Jual</th>
                <th style="width: 12%;">Nilai Beli</th>
                <th style="width: 12%;">Nilai Jual</th>
                <th style="width: 4%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['items'] as $item)
                <tr>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['category'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item['current_stock']) }} {{ $item['unit'] }}</td>
                    <td class="text-right">{{ number_format($item['minimum_stock']) }}</td>
                    <td class="text-right">Rp {{ number_format($item['purchase_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['selling_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['purchase_value'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['selling_value'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item['is_low_stock'])
                            <span class="badge badge-danger">LOW</span>
                        @else
                            <span class="badge badge-success">OK</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data produk</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-row">
            <span class="summary-label">Total Produk:</span>
            <span class="summary-value">{{ number_format($reportData['total_products']) }} items</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Nilai Pembelian:</span>
            <span class="summary-value">Rp {{ number_format($reportData['total_purchase_value'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Nilai Penjualan:</span>
            <span class="summary-value">Rp {{ number_format($reportData['total_selling_value'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Potensi Profit:</span>
            <span class="summary-value">Rp {{ number_format($reportData['total_potential_profit'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Margin Profit:</span>
            <span class="summary-value">
                @if($reportData['total_purchase_value'] > 0)
                    {{ number_format(($reportData['total_potential_profit'] / $reportData['total_purchase_value']) * 100, 2) }}%
                @else
                    0%
                @endif
            </span>
        </div>
    </div>

    <div class="footer">
        <p>Report generated on {{ $generatedAt->format('d F Y H:i:s') }} by {{ $generatedBy }}</p>
        <p>Warehouse Management System - Product Report</p>
    </div>
</body>
</html>
