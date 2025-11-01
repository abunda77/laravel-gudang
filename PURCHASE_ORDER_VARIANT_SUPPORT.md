# Purchase Order - Product Variant Support

## Overview

Purchase Order Form sekarang mendukung pemilihan varian produk saat membuat order. Ini adalah langkah pertama dalam workflow variant management.

## Perubahan Form

### Kolom Baru: Product Variant

Form PO Items sekarang memiliki kolom **Variant** yang:

-   **Tampil otomatis** jika produk memiliki varian
-   **Required** jika produk memiliki varian
-   **Hidden** jika produk tidak memiliki varian
-   **Auto-select** jika produk hanya memiliki 1 varian

### Layout Kolom

```
Produk TANPA varian:
| Product (2 col) | Quantity | Unit Price | Subtotal |

Produk DENGAN varian:
| Product (1 col) | Variant (1 col) | Quantity | Unit Price | Subtotal |
```

## Cara Kerja

### 1. Pilih Produk

Saat memilih produk:

```php
->afterStateUpdated(function ($state, callable $set, callable $get) {
    // Reset variant
    $set('product_variant_id', null);

    if ($state) {
        $product = Product::with('variants')->find($state);
        if ($product) {
            // Set default price
            $set('unit_price', $product->purchase_price);

            // Auto-select jika hanya 1 varian
            if ($product->variants->count() === 1) {
                $set('product_variant_id', $product->variants->first()->id);
            }
        }
    }
})
```

### 2. Kolom Variant Tampil

Kolom variant akan tampil jika:

```php
->visible(function (callable $get) {
    $productId = $get('product_id');
    if (!$productId) return false;

    $product = Product::with('variants')->find($productId);
    return $product && $product->variants->isNotEmpty();
})
```

### 3. Variant Required

Jika produk memiliki varian, field variant menjadi required:

```php
->required(function (callable $get) {
    $productId = $get('product_id');
    if (!$productId) return false;

    $product = Product::with('variants')->find($productId);
    return $product && $product->variants->isNotEmpty();
})
```

## Workflow Lengkap

### Skenario: Order INDOMIE GORENG dengan 3 Varian

#### Step 1: Buat Purchase Order

1. Klik "Create Purchase Order"
2. Pilih Supplier
3. Isi informasi PO

#### Step 2: Tambah Item - Varian ORIGINAL

1. Klik "Add Product"
2. **Product**: Pilih "INDOMIE GORENG"
3. **Variant**: Kolom ini **TAMPIL** dengan pilihan:
    - ORIGINAL
    - BARBEQUE
    - AYAM PANGGANG
4. Pilih **ORIGINAL**
5. **Quantity**: 100
6. **Unit Price**: Rp 107,000 (auto-filled)
7. **Subtotal**: Rp 10,700,000 (auto-calculated)

#### Step 3: Tambah Item - Varian BARBEQUE

1. Klik "Add Product" lagi
2. **Product**: Pilih "INDOMIE GORENG" (boleh sama!)
3. **Variant**: Pilih **BARBEQUE**
4. **Quantity**: 50
5. **Unit Price**: Rp 107,000
6. **Subtotal**: Rp 5,350,000

#### Step 4: Tambah Item - Varian AYAM PANGGANG

1. Klik "Add Product" lagi
2. **Product**: Pilih "INDOMIE GORENG"
3. **Variant**: Pilih **AYAM PANGGANG**
4. **Quantity**: 75
5. **Unit Price**: Rp 107,000
6. **Subtotal**: Rp 8,025,000

#### Step 5: Save Purchase Order

Total: Rp 24,075,000

**Data yang tersimpan:**

```php
PurchaseOrder {
    po_number: "PO-20251101-0001",
    supplier_id: 1,
    status: "sent",
    items: [
        {
            product_id: 5,  // INDOMIE GORENG
            product_variant_id: 10,  // ORIGINAL
            ordered_quantity: 100,
            unit_price: 107000,
        },
        {
            product_id: 5,  // INDOMIE GORENG (sama!)
            product_variant_id: 11,  // BARBEQUE
            ordered_quantity: 50,
            unit_price: 107000,
        },
        {
            product_id: 5,  // INDOMIE GORENG (sama!)
            product_variant_id: 12,  // AYAM PANGGANG
            ordered_quantity: 75,
            unit_price: 107000,
        }
    ]
}
```

### Step 6: Buat Inbound Operation

1. Klik "Create Inbound Operation"
2. Pilih PO yang baru dibuat
3. Form akan menampilkan **3 items** dengan kolom variant:

```
Item 1: INDOMIE GORENG - ORIGINAL
├─ Product: INDOMIE GORENG
├─ Variant: ORIGINAL  ← TAMPIL
├─ Ordered Qty: 100 box
├─ Received Qty: [100] box
└─ Variance: 0 (Match)

Item 2: INDOMIE GORENG - BARBEQUE
├─ Product: INDOMIE GORENG
├─ Variant: BARBEQUE  ← TAMPIL
├─ Ordered Qty: 50 box
├─ Received Qty: [50] box
└─ Variance: 0 (Match)

Item 3: INDOMIE GORENG - AYAM PANGGANG
├─ Product: INDOMIE GORENG
├─ Variant: AYAM PANGGANG  ← TAMPIL
├─ Ordered Qty: 75 box
├─ Received Qty: [75] box
└─ Variance: 0 (Match)
```

### Step 7: Stock Movement Tercatat

Saat Inbound Operation disimpan:

```php
// Movement 1
StockMovement::create([
    'product_id' => 5,
    'product_variant_id' => 10,  // ORIGINAL
    'quantity' => 100,
    'type' => 'inbound',
]);

// Movement 2
StockMovement::create([
    'product_id' => 5,
    'product_variant_id' => 11,  // BARBEQUE
    'quantity' => 50,
    'type' => 'inbound',
]);

// Movement 3
StockMovement::create([
    'product_id' => 5,
    'product_variant_id' => 12,  // AYAM PANGGANG
    'quantity' => 75,
    'type' => 'inbound',
]);
```

### Step 8: Cek Stok

```php
// Stok per varian
$variantOriginal->getCurrentStock();     // 100
$variantBarbeque->getCurrentStock();     // 50
$variantAyamPanggang->getCurrentStock(); // 75

// Total stok produk induk
$product->getCurrentStock();  // 225 (100 + 50 + 75)
```

## Fitur Tambahan

### Auto-Select Single Variant

Jika produk hanya memiliki 1 varian, sistem otomatis memilihnya:

```php
// Produk: KAOS POLOS
// Varian: Hanya "Merah - Large"

// Saat pilih produk, variant otomatis terisi "Merah - Large"
if ($product->variants->count() === 1) {
    $set('product_variant_id', $product->variants->first()->id);
}
```

### Item Label dengan Variant

Label item di repeater menampilkan nama varian:

```
Tanpa varian: "INDOMIE GORENG"
Dengan varian: "INDOMIE GORENG - ORIGINAL"
```

### Produk yang Sama, Varian Berbeda

Sekarang bisa menambahkan produk yang sama dengan varian berbeda dalam 1 PO:

```
✅ INDOMIE GORENG - ORIGINAL
✅ INDOMIE GORENG - BARBEQUE
✅ INDOMIE GORENG - AYAM PANGGANG
```

Sebelumnya, produk yang sama tidak bisa dipilih lagi (disabled).

## Validasi

### Required Variant

Jika produk memiliki varian, field variant **WAJIB** diisi:

```
❌ Product: INDOMIE GORENG, Variant: (kosong)
   Error: "The variant field is required."

✅ Product: INDOMIE GORENG, Variant: ORIGINAL
   Valid!
```

### Optional Variant

Jika produk tidak memiliki varian, field variant tidak tampil dan tidak required:

```
✅ Product: GULA PASIR (tanpa varian)
   Variant field tidak tampil
   Valid!
```

## Migration

Pastikan migration sudah dijalankan:

```bash
php artisan migrate
```

Ini akan menambahkan kolom `product_variant_id` ke tabel `purchase_order_items`.

## Testing

### Test 1: Produk Tanpa Varian

```php
// Buat PO item tanpa varian
$poItem = PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $productGula->id,
    'product_variant_id' => null,  // NULL untuk produk tanpa varian
    'ordered_quantity' => 100,
    'unit_price' => 15000,
]);

// Verify
assertNull($poItem->product_variant_id);
```

### Test 2: Produk Dengan Varian

```php
// Buat PO item dengan varian
$poItem = PurchaseOrderItem::create([
    'purchase_order_id' => $po->id,
    'product_id' => $productIndomie->id,
    'product_variant_id' => $variantOriginal->id,  // Harus ada!
    'ordered_quantity' => 100,
    'unit_price' => 107000,
]);

// Verify
assertEquals($variantOriginal->id, $poItem->product_variant_id);
assertEquals('ORIGINAL', $poItem->productVariant->name);
```

## Troubleshooting

### Kolom Variant Tidak Tampil

**Penyebab:**

-   Produk tidak memiliki varian di master data

**Solusi:**

1. Buka master data Product
2. Tambahkan varian di section "Product Variants"
3. Refresh form PO

### Error: "The variant field is required"

**Penyebab:**

-   Produk memiliki varian tapi tidak dipilih

**Solusi:**

-   Pilih salah satu varian dari dropdown

### Tidak Bisa Pilih Produk yang Sama

**Penyebab:**

-   Kode lama masih ada (sudah dihapus di update ini)

**Solusi:**

-   Pastikan menggunakan kode terbaru
-   Sekarang bisa pilih produk yang sama dengan varian berbeda

## Summary

✅ Purchase Order Form sekarang mendukung product variant
✅ Kolom variant tampil otomatis jika produk punya varian
✅ Variant required jika produk punya varian
✅ Auto-select jika hanya 1 varian
✅ Bisa order produk sama dengan varian berbeda
✅ Data variant tersimpan di `purchase_order_items`
✅ Data variant diteruskan ke Inbound Operation
✅ Stock movement tercatat per varian
