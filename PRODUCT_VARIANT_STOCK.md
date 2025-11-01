# Product Variant Stock Management

## Overview

Sistem WMS ini mendukung tracking stok untuk produk dengan varian. Setiap varian produk memiliki stok yang terpisah dan independen.

## Konsep Dasar

### Produk Tanpa Varian

-   Stok dicatat langsung pada produk induk
-   `StockMovement` memiliki `product_id` dan `product_variant_id = NULL`

### Produk Dengan Varian

-   Stok dicatat per varian
-   `StockMovement` memiliki `product_id` DAN `product_variant_id`
-   Stok produk induk = jumlah total stok semua varian

## Database Schema

### Tabel: `stock_movements`

```sql
- product_id (required) - ID produk induk
- product_variant_id (nullable) - ID varian (jika ada)
- quantity (integer) - Jumlah perubahan stok
- type (enum) - Tipe movement
```

### Tabel: `product_variants`

```sql
- id
- product_id - ID produk induk
- name - Nama varian (e.g., "Merah - Large")
- sku - SKU unik untuk varian
```

## Penggunaan

### 1. Mendapatkan Stok Produk

```php
// Produk tanpa varian
$product = Product::find(1);
$stock = $product->getCurrentStock(); // Stok produk langsung

// Produk dengan varian
$product = Product::find(2);
$stock = $product->getCurrentStock(); // Total stok semua varian
```

### 2. Mendapatkan Stok Varian

```php
$variant = ProductVariant::find(1);
$stock = $variant->getCurrentStock(); // Stok varian spesifik
```

### 3. Recording Stock Movement

#### Inbound (Penerimaan Barang)

```php
$stockService = app(StockMovementService::class);

// Untuk produk tanpa varian
$stockService->recordInbound($inbound, [
    [
        'product_id' => 1,
        'received_quantity' => 100,
    ]
]);

// Untuk produk dengan varian
$stockService->recordInbound($inbound, [
    [
        'product_id' => 2,
        'product_variant_id' => 5, // Varian: Merah - Large
        'received_quantity' => 50,
    ],
    [
        'product_id' => 2,
        'product_variant_id' => 6, // Varian: Biru - Medium
        'received_quantity' => 30,
    ]
]);
```

#### Outbound (Pengiriman Barang)

```php
// Untuk produk tanpa varian
$stockService->recordOutbound($outbound, [
    [
        'product_id' => 1,
        'shipped_quantity' => 20,
    ]
]);

// Untuk produk dengan varian
$stockService->recordOutbound($outbound, [
    [
        'product_id' => 2,
        'product_variant_id' => 5,
        'shipped_quantity' => 10,
    ]
]);
```

### 4. Checking Stock Availability

```php
$unavailable = $stockService->checkAvailability([
    [
        'product_id' => 2,
        'product_variant_id' => 5,
        'quantity' => 100,
    ]
]);

if (!empty($unavailable)) {
    // Handle insufficient stock
    foreach ($unavailable as $item) {
        echo "Stok tidak cukup untuk {$item['product_name']}";
        echo "Dibutuhkan: {$item['required']}, Tersedia: {$item['available']}";
    }
}
```

## Business Rules

1. **Konsistensi Varian**

    - Jika produk memiliki varian, SEMUA stock movement harus menggunakan `product_variant_id`
    - Tidak boleh ada stock movement langsung ke produk induk jika produk memiliki varian

2. **Perhitungan Stok Produk Induk**

    - Stok produk induk = SUM(stok semua varian)
    - Ini dihitung secara otomatis oleh method `getCurrentStock()` pada model `Product`

3. **Low Stock Alert**

    - Untuk produk dengan varian, alert dihitung per varian
    - Threshold menggunakan `minimum_stock` dari produk induk

4. **Stock Opname**
    - Saat ini stock opname belum mendukung varian
    - Akan ditambahkan di update berikutnya

## Migration

Untuk menerapkan perubahan ini:

```bash
php artisan migrate
```

Migration akan menambahkan kolom `product_variant_id` ke tabel `stock_movements` tanpa mengubah data yang sudah ada.

## Caching

Stok di-cache untuk performa:

-   Cache key produk: `product_stock_{product_id}`
-   Cache key varian: `product_variant_stock_{variant_id}`
-   TTL: 1 jam
-   Cache otomatis di-invalidate saat ada stock movement baru

## Contoh Skenario

### Skenario 1: Toko Baju dengan Varian Warna dan Ukuran

```php
// Produk: Kaos Polos
$product = Product::create([
    'sku' => 'KAOS-001',
    'name' => 'Kaos Polos',
    'unit' => 'pcs',
    'purchase_price' => 50000,
    'selling_price' => 75000,
]);

// Varian
$variantMerahL = ProductVariant::create([
    'product_id' => $product->id,
    'name' => 'Merah - Large',
    'sku' => 'KAOS-001-RED-L',
]);

$variantBiruM = ProductVariant::create([
    'product_id' => $product->id,
    'name' => 'Biru - Medium',
    'sku' => 'KAOS-001-BLUE-M',
]);

// Terima barang
$stockService->recordInbound($inbound, [
    ['product_id' => $product->id, 'product_variant_id' => $variantMerahL->id, 'received_quantity' => 100],
    ['product_id' => $product->id, 'product_variant_id' => $variantBiruM->id, 'received_quantity' => 150],
]);

// Cek stok
echo $variantMerahL->getCurrentStock(); // 100
echo $variantBiruM->getCurrentStock(); // 150
echo $product->getCurrentStock(); // 250 (total)
```

## Notes

-   Sistem ini backward compatible dengan produk tanpa varian
-   Produk existing tanpa varian akan tetap berfungsi normal
-   Untuk produk baru dengan varian, pastikan selalu mengisi `product_variant_id` saat recording stock movement
