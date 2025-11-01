# Cara Kerja Product Variant di Inbound Operation

## Penjelasan Form

Form Inbound Operation sudah diupdate untuk mendukung varian, dengan logika sebagai berikut:

### Struktur Kolom

```
| Product | Variant | Ordered Qty | Received Qty | Variance |
|---------|---------|-------------|--------------|----------|
| 1 col   | 1 col   | 1 col       | 1 col        | 1 col    | (jika ada variant)
| 2 col   |         | 1 col       | 1 col        | 1 col    | (jika tidak ada variant)
```

### Logika Tampilan Variant

Kolom **Variant** hanya tampil jika:

```php
->visible(fn (callable $get) => $get('product_variant_id') !== null)
```

Artinya:

-   ✅ Jika `product_variant_id` ada (tidak null) → Kolom variant **TAMPIL**
-   ❌ Jika `product_variant_id` null → Kolom variant **TIDAK TAMPIL**

## Skenario Penggunaan

### Skenario 1: Produk TANPA Varian

**Contoh: INDOMIE GORENG (produk biasa tanpa varian)**

1. **Master Data Product:**

    ```
    Product: INDOMIE GORENG
    SKU: INDOMIE-001
    Variants: (tidak ada)
    ```

2. **Purchase Order Item:**

    ```php
    [
        'product_id' => 1,
        'product_variant_id' => null,  // ← NULL karena tidak ada varian
        'ordered_quantity' => 100,
    ]
    ```

3. **Tampilan Form Inbound:**

    ```
    ┌─────────────────────────────────────────────────────────┐
    │ Product: INDOMIE GORENG                                 │
    │ (Kolom variant TIDAK TAMPIL)                            │
    │ Ordered Qty: 100 box                                    │
    │ Received Qty: [100] box                                 │
    │ Variance: 0 (Match)                                     │
    └─────────────────────────────────────────────────────────┘
    ```

4. **Stock Movement yang Tercatat:**
    ```php
    StockMovement::create([
        'product_id' => 1,
        'product_variant_id' => null,  // ← NULL
        'quantity' => 100,
        'type' => 'inbound',
    ]);
    ```

### Skenario 2: Produk DENGAN Varian

**Contoh: KAOS POLOS dengan varian warna dan ukuran**

1. **Master Data Product:**

    ```
    Product: KAOS POLOS
    SKU: KAOS-001
    Variants:
      - ID: 1, Name: "Merah - Large", SKU: "KAOS-001-RED-L"
      - ID: 2, Name: "Biru - Medium", SKU: "KAOS-001-BLUE-M"
    ```

2. **Purchase Order Items:**

    ```php
    [
        [
            'product_id' => 2,
            'product_variant_id' => 1,  // ← Varian: Merah - Large
            'ordered_quantity' => 50,
        ],
        [
            'product_id' => 2,
            'product_variant_id' => 2,  // ← Varian: Biru - Medium
            'ordered_quantity' => 30,
        ]
    ]
    ```

3. **Tampilan Form Inbound:**

    ```
    ┌─────────────────────────────────────────────────────────┐
    │ Item 1: KAOS POLOS - Merah - Large                      │
    ├─────────────────────────────────────────────────────────┤
    │ Product: KAOS POLOS                                     │
    │ Variant: Merah - Large  ← TAMPIL karena ada variant_id │
    │ Ordered Qty: 50 pcs                                     │
    │ Received Qty: [50] pcs                                  │
    │ Variance: 0 (Match)                                     │
    └─────────────────────────────────────────────────────────┘

    ┌─────────────────────────────────────────────────────────┐
    │ Item 2: KAOS POLOS - Biru - Medium                      │
    ├─────────────────────────────────────────────────────────┤
    │ Product: KAOS POLOS                                     │
    │ Variant: Biru - Medium  ← TAMPIL karena ada variant_id │
    │ Ordered Qty: 30 pcs                                     │
    │ Received Qty: [30] pcs                                  │
    │ Variance: 0 (Match)                                     │
    └─────────────────────────────────────────────────────────┘
    ```

4. **Stock Movement yang Tercatat:**

    ```php
    // Item 1
    StockMovement::create([
        'product_id' => 2,
        'product_variant_id' => 1,  // ← Merah - Large
        'quantity' => 50,
        'type' => 'inbound',
    ]);

    // Item 2
    StockMovement::create([
        'product_id' => 2,
        'product_variant_id' => 2,  // ← Biru - Medium
        'quantity' => 30,
        'type' => 'inbound',
    ]);
    ```

5. **Hasil Stok:**

    ```php
    // Stok per varian
    $variantMerahL->getCurrentStock();  // 50
    $variantBiruM->getCurrentStock();   // 30

    // Total stok produk induk (otomatis sum dari varian)
    $product->getCurrentStock();        // 80 (50 + 30)
    ```

## Mengapa Kolom Variant Tidak Tampil?

Jika Anda tidak melihat kolom variant di form, kemungkinan penyebabnya:

### 1. Produk Tidak Memiliki Varian

Cek di master data Product → Variants:

```sql
SELECT * FROM product_variants WHERE product_id = [ID_PRODUK];
```

Jika hasilnya kosong, berarti produk tidak memiliki varian.

**Solusi:** Tambahkan varian di master data Product terlebih dahulu.

### 2. Purchase Order Tidak Menggunakan Varian

Cek di Purchase Order Items:

```sql
SELECT product_id, product_variant_id, ordered_quantity
FROM purchase_order_items
WHERE purchase_order_id = [ID_PO];
```

Jika `product_variant_id` = NULL, berarti PO dibuat untuk produk tanpa varian.

**Solusi:** Saat membuat Purchase Order, pilih varian yang diinginkan (fitur ini perlu diupdate di PurchaseOrderForm).

### 3. Migration Belum Dijalankan

Pastikan migration sudah dijalankan:

```bash
php artisan migrate
```

Cek apakah kolom sudah ada:

```sql
DESCRIBE purchase_order_items;
DESCRIBE inbound_operation_items;
```

Harus ada kolom `product_variant_id`.

## Cara Testing

### Test 1: Buat Produk dengan Varian

```php
// 1. Buat produk
$product = Product::create([
    'sku' => 'KAOS-001',
    'name' => 'Kaos Polos',
    'unit' => 'pcs',
    'purchase_price' => 50000,
    'selling_price' => 75000,
    'category_id' => 1,
]);

// 2. Buat varian
$variantRed = ProductVariant::create([
    'product_id' => $product->id,
    'name' => 'Merah - Large',
    'sku' => 'KAOS-001-RED-L',
]);

$variantBlue = ProductVariant::create([
    'product_id' => $product->id,
    'name' => 'Biru - Medium',
    'sku' => 'KAOS-001-BLUE-M',
]);
```

### Test 2: Buat Purchase Order dengan Varian

```php
$po = PurchaseOrder::create([
    'po_number' => 'PO-20251101-0001',
    'supplier_id' => 1,
    'order_date' => now(),
    'status' => PurchaseOrderStatus::SENT,
]);

// Item dengan varian
PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $product->id,
    'product_variant_id' => $variantRed->id,  // ← Penting!
    'ordered_quantity' => 50,
    'unit_price' => 50000,
]);
```

### Test 3: Buat Inbound Operation

1. Buka form Create Inbound Operation
2. Pilih Purchase Order yang baru dibuat
3. Form akan menampilkan:
    - Product: "Kaos Polos"
    - Variant: "Merah - Large" ← **Kolom ini akan tampil**
    - Ordered Qty: 50
    - Received Qty: (isi sesuai yang diterima)

### Test 4: Verifikasi Stock Movement

```php
// Cek stock movement
$movement = StockMovement::where('product_id', $product->id)
    ->where('product_variant_id', $variantRed->id)
    ->first();

echo $movement->quantity;  // 50

// Cek stok varian
echo $variantRed->getCurrentStock();  // 50

// Cek total stok produk
echo $product->getCurrentStock();  // 50
```

## Kesimpulan

Form **sudah benar** dan **sudah mendukung varian**. Kolom variant akan otomatis tampil jika:

1. ✅ Produk memiliki varian di master data
2. ✅ Purchase Order item memiliki `product_variant_id`
3. ✅ Migration sudah dijalankan

Jika kolom variant tidak tampil, berarti produk tersebut memang tidak memiliki varian (seperti INDOMIE GORENG di screenshot Anda), dan ini adalah **behavior yang benar**.

## Next Step: Update Purchase Order Form

Untuk bisa memilih varian saat membuat Purchase Order, Anda perlu update `PurchaseOrderForm` agar bisa memilih varian saat menambahkan item. Ini adalah langkah selanjutnya yang perlu dilakukan.
