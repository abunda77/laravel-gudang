# Contoh n8n Workflow untuk Webhook Notification

## Opsi 1: Single Webhook Endpoint (Recommended)

Menggunakan 1 webhook endpoint untuk kedua event, membedakan berdasarkan field `event`.

### Konfigurasi .env
```env
WEBHOOK_WA_N8N_PURCHASEORDER=https://n8n.produkmastah.com/webhook/warehouse-notification
WEBHOOK_WA_N8N_SALESORDER=https://n8n.produkmastah.com/webhook/warehouse-notification
```

### n8n Workflow Structure

```
[Webhook] → [Switch] → [Function: Format PO] → [WhatsApp: Send to Purchasing]
                    ↓
                    → [Function: Format SO] → [WhatsApp: Send to Sales]
```

### Node Configuration

#### 1. Webhook Node
- **Name**: Warehouse Webhook
- **HTTP Method**: POST
- **Path**: `warehouse-notification`
- **Response Mode**: On Received

#### 2. Switch Node
- **Name**: Route by Event Type
- **Mode**: Rules
- **Rules**:
  - Rule 1: `{{ $json.event }}` equals `purchase_order_item_created`
  - Rule 2: `{{ $json.event }}` equals `sales_order_item_created`

#### 3. Function Node - Purchase Order
- **Name**: Format PO Message
- **JavaScript Code**:

```javascript
const data = items[0].json.data;

let message = '🛒 *Purchase Order Baru*\n\n';
message += `PO Number: ${data.purchase_order.order_number}\n`;
message += `Supplier: ${data.purchase_order.supplier.name}\n`;
message += `Status: ${data.purchase_order.status}\n`;
message += `Tanggal: ${data.purchase_order.order_date}\n\n`;

message += `📦 *Detail Item*\n`;
message += `Produk: ${data.product.name}`;
if (data.variant) {
  message += ` (${data.variant.name})`;
}
message += `\nSKU: ${data.product.sku}`;
if (data.variant) {
  message += ` / ${data.variant.sku}`;
}
message += `\nQuantity: ${data.quantity} unit\n`;
message += `Harga: Rp ${data.unit_price.toLocaleString('id-ID')}\n`;
message += `Subtotal: Rp ${data.subtotal.toLocaleString('id-ID')}\n\n`;

if (data.notes) {
  message += `📝 Catatan: ${data.notes}\n\n`;
}

message += `💰 *Total Order: Rp ${data.purchase_order.total_amount.toLocaleString('id-ID')}*`;

return [{
  json: {
    message: message,
    phone: '628123456789', // Nomor admin purchasing
    event: 'purchase_order'
  }
}];
```

#### 4. Function Node - Sales Order
- **Name**: Format SO Message
- **JavaScript Code**:

```javascript
const data = items[0].json.data;

let message = '📦 *Sales Order Baru*\n\n';
message += `SO Number: ${data.sales_order.order_number}\n`;
message += `Customer: ${data.sales_order.customer.name}\n`;
message += `Status: ${data.sales_order.status}\n`;
message += `Tanggal: ${data.sales_order.order_date}\n\n`;

message += `📦 *Detail Item*\n`;
message += `Produk: ${data.product.name}`;
if (data.variant) {
  message += ` (${data.variant.name})`;
}
message += `\nSKU: ${data.product.sku}`;
if (data.variant) {
  message += ` / ${data.variant.sku}`;
}
message += `\nQuantity: ${data.quantity} unit\n`;
message += `Harga: Rp ${data.unit_price.toLocaleString('id-ID')}\n`;
message += `Subtotal: Rp ${data.subtotal.toLocaleString('id-ID')}\n\n`;

if (data.notes) {
  message += `📝 Catatan: ${data.notes}\n\n`;
}

message += `💰 *Total Order: Rp ${data.sales_order.total_amount.toLocaleString('id-ID')}*`;

return [{
  json: {
    message: message,
    phone: '628987654321', // Nomor admin sales
    event: 'sales_order'
  }
}];
```

#### 5. WhatsApp Node (untuk masing-masing branch)
- **Name**: Send WhatsApp Notification
- **Operation**: Send Message
- **Phone Number**: `{{ $json.phone }}`
- **Message**: `{{ $json.message }}`

---

## Opsi 2: Separate Webhook Endpoints

Menggunakan 2 webhook endpoint terpisah untuk Purchase Order dan Sales Order.

### Konfigurasi .env
```env
WEBHOOK_WA_N8N_PURCHASEORDER=https://n8n.produkmastah.com/webhook/purchase-order
WEBHOOK_WA_N8N_SALESORDER=https://n8n.produkmastah.com/webhook/sales-order
```

### Workflow 1: Purchase Order Notification

```
[Webhook: /purchase-order] → [Function: Format PO] → [WhatsApp: Send]
```

### Workflow 2: Sales Order Notification

```
[Webhook: /sales-order] → [Function: Format SO] → [WhatsApp: Send]
```

Gunakan Function Node yang sama seperti Opsi 1.

---

## Opsi 3: Advanced - Multiple Recipients

Mengirim notifikasi ke beberapa nomor berdasarkan kondisi tertentu.

### Function Node dengan Multiple Recipients

```javascript
const data = items[0].json.data;
const event = items[0].json.event;

let message = '';
let recipients = [];

if (event === 'purchase_order_item_created') {
  message = '🛒 *Purchase Order Baru*\n\n';
  message += `PO Number: ${data.purchase_order.order_number}\n`;
  message += `Supplier: ${data.purchase_order.supplier.name}\n`;
  message += `Total: Rp ${data.purchase_order.total_amount.toLocaleString('id-ID')}`;
  
  // Kirim ke admin purchasing
  recipients.push('628123456789');
  
  // Jika total > 10 juta, kirim juga ke manager
  if (data.purchase_order.total_amount > 10000000) {
    recipients.push('628111222333');
  }
  
} else if (event === 'sales_order_item_created') {
  message = '📦 *Sales Order Baru*\n\n';
  message += `SO Number: ${data.sales_order.order_number}\n`;
  message += `Customer: ${data.sales_order.customer.name}\n`;
  message += `Total: Rp ${data.sales_order.total_amount.toLocaleString('id-ID')}`;
  
  // Kirim ke admin sales
  recipients.push('628987654321');
  
  // Jika total > 10 juta, kirim juga ke manager
  if (data.sales_order.total_amount > 10000000) {
    recipients.push('628111222333');
  }
}

// Return array untuk setiap recipient
return recipients.map(phone => ({
  json: {
    message: message,
    phone: phone
  }
}));
```

Kemudian tambahkan **Split In Batches** node sebelum WhatsApp node untuk mengirim ke multiple recipients.

---

## Testing Webhook

### Menggunakan curl

**Test Purchase Order Webhook:**
```bash
curl -X POST https://n8n.produkmastah.com/webhook/warehouse-notification \
  -H "Content-Type: application/json" \
  -d '{
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
      "notes": "Test webhook"
    }
  }'
```

**Test Sales Order Webhook:**
```bash
curl -X POST https://n8n.produkmastah.com/webhook/warehouse-notification \
  -H "Content-Type: application/json" \
  -d '{
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
      "variant": null,
      "quantity": 150,
      "unit_price": 50000,
      "subtotal": 7500000,
      "notes": "Test webhook"
    }
  }'
```

---

## Tips & Best Practices

1. **Error Handling**: Tambahkan error handling node untuk menangkap kegagalan pengiriman
2. **Logging**: Simpan log webhook ke database atau file untuk audit trail
3. **Rate Limiting**: Jika volume tinggi, pertimbangkan untuk menambahkan delay atau batching
4. **Retry Mechanism**: Tambahkan retry logic jika pengiriman WhatsApp gagal
5. **Testing**: Selalu test dengan data dummy sebelum production
6. **Security**: Pertimbangkan menambahkan authentication header atau signature verification
7. **Monitoring**: Setup monitoring untuk memastikan webhook berjalan dengan baik

---

## Troubleshooting

### Webhook tidak menerima data
- Cek apakah URL webhook benar di `.env`
- Pastikan n8n workflow sudah active
- Cek firewall atau network restrictions

### Format pesan tidak sesuai
- Cek function node JavaScript code
- Pastikan field yang diakses ada dalam payload
- Gunakan `console.log()` untuk debugging

### WhatsApp tidak terkirim
- Cek konfigurasi WhatsApp node
- Pastikan nomor telepon format benar (628xxx)
- Cek quota atau limit WhatsApp API
