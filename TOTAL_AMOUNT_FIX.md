# Total Amount Calculation Fix

## Problem

Total amount pada Purchase Order dan Sales Order tidak dihitung dengan benar karena:

1. Logic perhitungan hanya ada di Create/Edit pages
2. Ketika status diupdate dari tempat lain (misalnya dari InboundOperation), total_amount tidak diupdate
3. Tidak ada mekanisme otomatis untuk menghitung ulang total saat items berubah

## Solution

Implementasi observer pattern untuk otomatis menghitung total_amount setiap kali items berubah:

### 1. Model Methods

Ditambahkan method di `PurchaseOrder` dan `SalesOrder`:

-   `calculateTotalAmount()`: Menghitung total dari semua items
-   `updateTotalAmount()`: Update total_amount di database

### 2. Observers

Dibuat observer untuk otomatis update total:

-   `PurchaseOrderItemObserver`: Monitor perubahan pada purchase order items
-   `SalesOrderItemObserver`: Monitor perubahan pada sales order items

Observer akan trigger update total saat:

-   Item dibuat (created)
-   Item diupdate (updated)
-   Item dihapus (deleted)
-   Item direstore (restored)
-   Item force deleted (forceDeleted)

### 3. Registration

Observer didaftarkan di `AppServiceProvider`:

```php
PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
SalesOrderItem::observe(SalesOrderItemObserver::class);
```

### 4. Page Hooks

Logic perhitungan di Create/Edit pages dipindahkan ke `afterCreate()` dan `afterSave()` hooks untuk memastikan total dihitung setelah items tersimpan.

## Fix Commands

Untuk memperbaiki data yang sudah ada:

```bash
php artisan fix:purchase-order-totals
php artisan fix:sales-order-totals
```

## Benefits

-   Total amount selalu akurat
-   Otomatis update saat items berubah
-   Tidak perlu manual calculation di setiap tempat
-   Konsisten di seluruh aplikasi
