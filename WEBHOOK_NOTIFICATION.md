# Webhook Notification untuk n8n

## Overview

Sistem ini mengirimkan notifikasi webhook ke n8n saat ada item baru yang dibuat pada Purchase Order atau Sales Order.

## Konfigurasi

### Environment Variable

Tambahkan URL webhook n8n ke file `.env`:

```env
# Webhook untuk Purchase Order
WEBHOOK_WA_N8N_PURCHASEORDER=https://your-n8n-instance.com/webhook/purchase-order

# Webhook untuk Sales Order
WEBHOOK_WA_N8N_SALESORDER=https://your-n8n-instance.com/webhook/sales-order
```

Jika variable ini kosong, webhook tidak akan dikirim. Anda dapat menggunakan URL yang sama atau berbeda untuk masing-masing webhook.

## Event yang Dikirim

### 1. Purchase Order Item Created

**Event:** `purchase_order_item_created`

**Payload JSON:**

```json
{
  "event": "purchase_order_item_created",
  "timestamp": "2025-11-05T10:30:00+07:00",
  "data": {
    "item_id": 123,
    "purchase_order": {
      "id": 45,
      "order_number": "PO-20251105-0001",
      "status": "pending",
      "order_date": "2025-11-05",
      "supplier": {
        "id": 10,
        "name": "PT Supplier ABC"
      },
      "total_amount": 5000000
    },
    "product": {
      "id": 25,
      "name": "Produk A",
      "sku": "PRD-001"
    },
    "variant": {
      "id": 30,
      "name": "Varian Merah",
      "sku": "PRD-001-RED"
    },
    "quantity": 100,
    "unit_price": 50000,
    "subtotal": 5000000,
    "notes": "Catatan tambahan"
  }
}
```

**Catatan:**
- Field `variant` akan `null` jika produk tidak memiliki varian
- Field `notes` akan `null` jika tidak ada catatan

### 2. Sales Order Item Created

**Event:** `sales_order_item_created`

**Payload JSON:**

```json
{
  "event": "sales_order_item_created",
  "timestamp": "2025-11-05T10:30:00+07:00",
  "data": {
    "item_id": 456,
    "sales_order": {
      "id": 78,
      "order_number": "SO-20251105-0001",
      "status": "pending",
      "order_date": "2025-11-05",
      "customer": {
        "id": 15,
        "name": "PT Customer XYZ"
      },
      "total_amount": 7500000
    },
    "product": {
      "id": 25,
      "name": "Produk A",
      "sku": "PRD-001"
    },
    "variant": {
      "id": 30,
      "name": "Varian Merah",
      "sku": "PRD-001-RED"
    },
    "quantity": 150,
    "unit_price": 50000,
    "subtotal": 7500000,
    "notes": "Catatan tambahan"
  }
}
```

**Catatan:**
- Field `variant` akan `null` jika produk tidak memiliki varian
- Field `notes` akan `null` jika tidak ada catatan

## Implementasi

### Observer Files

1. **PurchaseOrderItemObserver** (`app/Observers/PurchaseOrderItemObserver.php`)
   - Mengirim webhook saat item purchase order dibuat
   
2. **SalesOrderItemObserver** (`app/Observers/SalesOrderItemObserver.php`)
   - Mengirim webhook saat item sales order dibuat

### Error Handling

- Webhook menggunakan timeout 5 detik
- Jika gagal, error akan dicatat di log tanpa mengganggu proses utama
- Log error dapat dilihat di `storage/logs/laravel.log`

### Testing Webhook

Untuk testing webhook, Anda bisa:

1. Setup n8n webhook endpoint (bisa 1 atau 2 endpoint terpisah)
2. Tambahkan URL ke `.env`:
   - `WEBHOOK_WA_N8N_PURCHASEORDER` untuk notifikasi Purchase Order
   - `WEBHOOK_WA_N8N_SALESORDER` untuk notifikasi Sales Order
3. Buat Purchase Order atau Sales Order baru dengan item
4. Cek log n8n untuk melihat payload yang diterima

### Contoh n8n Workflow

#### Opsi 1: Menggunakan 1 Webhook Endpoint (URL yang sama)

Jika Anda menggunakan URL yang sama untuk kedua webhook, n8n dapat membedakan event berdasarkan field `event` dalam payload:

1. **Webhook Node**: Terima POST request dari Laravel
2. **Switch Node**: Bedakan berdasarkan `{{ $json.event }}`
   - Case 1: `purchase_order_item_created`
   - Case 2: `sales_order_item_created`
3. **Function Node**: Parse data dan format pesan WhatsApp sesuai event
4. **WhatsApp Node**: Kirim notifikasi ke nomor tujuan

#### Opsi 2: Menggunakan 2 Webhook Endpoint Terpisah

Jika Anda menggunakan URL berbeda, buat 2 workflow terpisah:

**Workflow 1: Purchase Order**
1. **Webhook Node**: `/webhook/purchase-order`
2. **Function Node**: Format pesan untuk Purchase Order
3. **WhatsApp Node**: Kirim ke nomor admin purchasing

**Workflow 2: Sales Order**
1. **Webhook Node**: `/webhook/sales-order`
2. **Function Node**: Format pesan untuk Sales Order
3. **WhatsApp Node**: Kirim ke nomor admin sales

Contoh format pesan WhatsApp:

**Purchase Order:**
```
🛒 *Purchase Order Baru*

PO Number: PO-20251105-0001
Supplier: PT Supplier ABC
Produk: Produk A (Varian Merah)
Quantity: 100 unit
Harga: Rp 50.000
Subtotal: Rp 5.000.000

Total Order: Rp 5.000.000
```

**Sales Order:**
```
📦 *Sales Order Baru*

SO Number: SO-20251105-0001
Customer: PT Customer XYZ
Produk: Produk A (Varian Merah)
Quantity: 150 unit
Harga: Rp 50.000
Subtotal: Rp 7.500.000

Total Order: Rp 7.500.000
```

## Troubleshooting

### Webhook tidak terkirim

1. Cek apakah `WEBHOOK_WA_N8N_PURCHASEORDER` atau `WEBHOOK_WA_N8N_SALESORDER` sudah diset di `.env`
2. Cek log error di `storage/logs/laravel.log`
3. Pastikan URL webhook n8n dapat diakses dari server Laravel
4. Cek firewall atau network restrictions

### Timeout Error

Jika sering timeout, pertimbangkan:
- Menggunakan queue untuk mengirim webhook secara asynchronous
- Meningkatkan timeout di observer (default 5 detik)
- Cek performa n8n instance

## Future Improvements

- [ ] Kirim webhook secara asynchronous menggunakan queue
- [ ] Tambahkan retry mechanism
- [ ] Tambahkan webhook signature untuk security
- [ ] Support multiple webhook URLs
- [ ] Tambahkan event untuk update dan delete
