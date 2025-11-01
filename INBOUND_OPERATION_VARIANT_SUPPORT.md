# Inbound Operation - Product Variant Support

## Overview

Inbound Operation sekarang mendukung penerimaan barang untuk produk dengan varian. Sistem akan otomatis merecord stock movement untuk setiap varian yang diterima.

## Perubahan Database

### Tabel yang Diupdate

1. **purchase_order_items** - Tambah kolom `product_variant_id`
2. **inbound_operation_items** - Tambah kolom `product_variant_id`
3. **sales_order_items** - Tambah kolom `product_variant_id`
4. **outbound_operation_items** - Tambah kolom `product_variant_id`

Semua kolom `product_variant_id` bersifat **nullable** untuk backward compatibility.

## Perubahan Model

### Updated Models

-   `PurchaseOrderItem` - Tambah field `product_variant_id` dan relasi `productVariant()`
-   `InboundOperationItem` - Tambah field `product_variant_id` dan relasi `productVariant()`
-   `SalesOrderItem` - Tambah field `product_variant_id` dan relasi `productVariant()`
-   `OutboundOperationItem` - Tambah field `product_variant_id` dan relasi `productVariant()`

## Perubahan Form

### InboundOperationForm

Form sekarang menampilkan kolom varian jika produk memiliki varian:

```php
// Kolom Product (selalu tampil)
Forms\Components\Select::make('product_id')
    ->label('Product')
    ->disabled()
    ->dehydrated()

// Kolom Variant (hanya tampil jika ada varian)
Forms\Components\Select::make('product_variant_id')
    ->label('Variant')
    ->visible(fn (callable $get) => $get('product_variant_id') !== null)
    ->disabled()
    ->dehydrated()
```

### Item Label

Label item di repeater sekarang menampilkan nama varian jika ada:

-   Tanpa varian: "Kaos Polos"
-   Dengan varian: "Kaos Polos - Merah - Large"

## Stock Movement Integration

### Automatic Stock Recording

Saat Inbound Operation dibuat, sistem otomatis:

1. Mengambil semua items dari inbound operation
2. Memanggil `StockMovementService::recordInbound()` dengan data:
    - `product_id`
    - `product_variant_id` (jika ada)
    - `received_quantity`
3. Membuat stock movement record untuk setiap item
4. Menampilkan notifikasi sukses/gagal

### Code Implementation

```php
// Di CreateInboundOperation::afterCreate()
protected function afterCreate(): void
{
    $stockService = app(StockMovementService::class);

    $items = $this->record->items->map(function ($item) {
        return [
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'received_quantity' => $item->received_quantity,
        ];
    })->toArray();

    $stockService->recordInbound($this->record, $items);
}
```

## Workflow

### Skenario 1: Produk Tanpa Varian

1. Buat Purchase Order untuk produk tanpa varian
2. Buat Inbound Operation dari PO tersebut
3. Form menampilkan produk tanpa kolom varian
4. Saat disimpan, stock movement dicatat dengan:
    - `product_id` = ID produk
    - `product_variant_id` = NULL

### Skenario 2: Produk Dengan Varian

1. Buat Purchase Order untuk produk dengan varian (misal: Kaos Merah-L, Kaos Biru-M)
2. Buat Inbound Operation dari PO tersebut
3. Form menampilkan:
    - Kolom Product: "Kaos Polos"
    - Kolom Variant: "Merah - Large"
4. Saat disimpan, stock movement dicatat dengan:
    - `product_id` = ID produk induk
    - `product_variant_id` = ID varian spesifik

## Business Rules

1. **Purchase Order Items**

    - Jika produk memiliki varian, PO item HARUS memiliki `product_variant_id`
    - Jika produk tidak memiliki varian, `product_variant_id` = NULL

2. **Inbound Operation Items**

    - Inherit `product_variant_id` dari Purchase Order Item
    - Tidak bisa diubah saat create/edit inbound operation

3. **Stock Movement**

    - Selalu menggunakan `product_variant_id` dari inbound operation item
    - Stok dicatat per varian jika ada, atau per produk jika tidak ada varian

4. **Backward Compatibility**
    - Data existing tanpa varian tetap berfungsi normal
    - Migration tidak mengubah data existing
    - Semua kolom `product_variant_id` nullable

## Migration

Jalankan migration untuk menerapkan perubahan:

```bash
php artisan migrate
```

Migration akan:

-   Menambahkan kolom `product_variant_id` ke 4 tabel items
-   Membuat foreign key constraint ke `product_variants`
-   Membuat index untuk performa

## Testing

### Test Case 1: Inbound Tanpa Varian

```php
// Buat PO item tanpa varian
$poItem = PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $product->id,
    'product_variant_id' => null,
    'ordered_quantity' => 100,
    'unit_price' => 50000,
]);

// Buat inbound operation
$inbound = InboundOperation::create([...]);
$inboundItem = InboundOperationItem::create([
    'inbound_operation_id' => $inbound->id,
    'product_id' => $product->id,
    'product_variant_id' => null,
    'ordered_quantity' => 100,
    'received_quantity' => 100,
]);

// Verify stock movement
$movement = StockMovement::where('product_id', $product->id)
    ->whereNull('product_variant_id')
    ->first();

assertEquals(100, $movement->quantity);
```

### Test Case 2: Inbound Dengan Varian

```php
// Buat varian
$variant = ProductVariant::create([
    'product_id' => $product->id,
    'name' => 'Merah - Large',
    'sku' => 'KAOS-001-RED-L',
]);

// Buat PO item dengan varian
$poItem = PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $product->id,
    'product_variant_id' => $variant->id,
    'ordered_quantity' => 50,
    'unit_price' => 50000,
]);

// Buat inbound operation
$inbound = InboundOperation::create([...]);
$inboundItem = InboundOperationItem::create([
    'inbound_operation_id' => $inbound->id,
    'product_id' => $product->id,
    'product_variant_id' => $variant->id,
    'ordered_quantity' => 50,
    'received_quantity' => 50,
]);

// Verify stock movement
$movement = StockMovement::where('product_id', $product->id)
    ->where('product_variant_id', $variant->id)
    ->first();

assertEquals(50, $movement->quantity);
assertEquals(50, $variant->getCurrentStock());
```

## Next Steps

Fitur yang perlu diupdate selanjutnya:

1. **PurchaseOrderForm** - Tambah support untuk memilih varian saat membuat PO
2. **SalesOrderForm** - Tambah support untuk memilih varian saat membuat SO
3. **OutboundOperationForm** - Tambah support untuk varian saat pengiriman
4. **StockOpname** - Tambah support untuk stock opname per varian
5. **Reports** - Update laporan untuk menampilkan data per varian

## Notes

-   Form saat ini masih read-only untuk varian (disabled)
-   Varian harus dipilih di Purchase Order, tidak bisa diubah di Inbound Operation
-   Untuk mengubah varian, harus edit Purchase Order terlebih dahulu
