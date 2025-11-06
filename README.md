# Warehouse Management System

Sistem Manajemen Gudang berbasis web yang dibangun dengan Laravel 12 dan Filament 4.0 untuk mengelola operasi pergudangan, pembelian, penjualan, dan logistik.

## Fitur Utama

-   **Manajemen Inventori**: Kelola produk, kategori, varian, dan stok dengan sistem event sourcing
-   **Manajemen Pembelian**: Buat dan kelola purchase order dari supplier
-   **Manajemen Penjualan**: Proses sales order dan transaksi pelanggan
-   **Operasi Gudang**: Tangani operasi inbound/outbound dengan pencatatan stock movement
-   **Logistik**: Kelola delivery order dengan penugasan driver dan kendaraan
-   **Keuangan**: Generate invoice dan tracking pembayaran
-   **Pelaporan**: Kartu stok, alert stok rendah, laporan penjualan, dan valuasi stok
-   **Queue Jobs**: Generate laporan bulanan secara background tanpa blocking UI
-   **Webhook Notifications**: Integrasi dengan n8n untuk notifikasi WhatsApp otomatis
-   **Role & Permissions**: Sistem otorisasi berbasis role menggunakan Filament Shield

## Teknologi

-   **Backend**: Laravel 12 (PHP ^8.2)
-   **Admin Panel**: Filament 4.0
-   **Database**: MySQL/PostgreSQL (SQLite untuk development)
-   **Frontend**: Vite 6 + Tailwind CSS 4
-   **Queue**: Database driver (Redis untuk production)
-   **Cache**: Database driver (Redis untuk production)
-   **PDF Generation**: DomPDF
-   **Permissions**: Spatie Laravel Permission + Filament Shield
-   **Performance**: Laravel Octane (optional)

## Instalasi

### Requirements

-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   MySQL/PostgreSQL (atau SQLite untuk development)

### Setup

1. Clone repository dan install dependencies:

```bash
composer install
npm install
```

2. Setup environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Konfigurasi database di `.env`, lalu jalankan migrasi:

```bash
php artisan migrate --seed
```

4. Buat symlink storage:

```bash
php artisan storage:link
```

5. Build assets:

```bash
npm run build
```

## Development

Jalankan development server dengan satu command:

```bash
composer dev
```

Atau jalankan secara terpisah:

```bash
php artisan serve          # Laravel server
php artisan queue:work     # Queue worker
php artisan pail          # Log viewer
npm run dev               # Vite dev server
```

### Development dengan Laravel Octane (Optional)

Untuk performa lebih tinggi, gunakan Laravel Octane:

```bash
# Dengan RoadRunner (memerlukan ext-sockets)
php artisan octane:start --server=roadrunner

# Dengan Swoole (memerlukan ext-swoole)
php artisan octane:start --server=swoole

# Dengan FrankenPHP (Linux/macOS/WSL/Docker only)
php artisan octane:start --server=frankenphp
```

**Catatan untuk Windows:**
- RoadRunner memerlukan PHP extension `sockets`
- Swoole memerlukan PHP extension `swoole` (install via PECL atau Laragon)
- FrankenPHP tidak support Windows native, gunakan WSL atau Docker
- Untuk production di Windows, disarankan menggunakan Docker atau WSL

## Testing

```bash
# Run semua tests
php artisan test

# Run dengan coverage
php artisan test --coverage

# Run specific suite
php artisan test --testsuite=Feature
```

## Code Quality

```bash
# Format code dengan Laravel Pint
./vendor/bin/pint

# Check tanpa fixing
./vendor/bin/pint --test
```

## Deployment

Lihat dokumentasi lengkap di:

-   [DEPLOYMENT.md](DEPLOYMENT.md) - Panduan deployment
-   [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) - Checklist production

Quick deployment:

```bash
./deploy.sh
```

## Dokumentasi

### Fitur Utama
-   [PRODUCT_VARIANT_STOCK.md](PRODUCT_VARIANT_STOCK.md) - Manajemen stok variant
-   [QUEUE_IMPLEMENTATION.md](QUEUE_IMPLEMENTATION.md) - Implementasi queue system
-   [WEBHOOK_NOTIFICATION.md](WEBHOOK_NOTIFICATION.md) - Integrasi webhook dengan n8n
-   [PERFORMANCE_OPTIMIZATIONS.md](PERFORMANCE_OPTIMIZATIONS.md) - Optimasi performa

### Panduan Teknis
-   [CARA_KERJA_VARIANT_INBOUND.md](CARA_KERJA_VARIANT_INBOUND.md) - Cara kerja variant inbound
-   [PURCHASE_ORDER_VARIANT_SUPPORT.md](PURCHASE_ORDER_VARIANT_SUPPORT.md) - Support variant di PO
-   [INBOUND_OPERATION_VARIANT_SUPPORT.md](INBOUND_OPERATION_VARIANT_SUPPORT.md) - Support variant di inbound
-   [TOTAL_AMOUNT_FIX.md](TOTAL_AMOUNT_FIX.md) - Fix perhitungan total amount

### Setup & Deployment
-   [DEPLOYMENT.md](DEPLOYMENT.md) - Panduan deployment lengkap
-   [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) - Checklist production
-   [QUEUE_SETUP.md](QUEUE_SETUP.md) - Setup queue workers
-   [SHIELD_SETUP.md](SHIELD_SETUP.md) - Setup role & permissions
-   [FILAMENT_4_MIGRATION.md](FILAMENT_4_MIGRATION.md) - Migrasi ke Filament 4

## Aturan Bisnis Penting

1. **Semua perubahan stok harus melalui StockMovementService**
2. **Gunakan database transactions untuk operasi multi-step**
3. **Stock quantity dihitung dari sum of movements (event sourcing)**
4. **Audit trail lengkap untuk semua transaksi**
5. **Role-based access control untuk semua resource**
6. **Total amount dihitung otomatis via observer pattern**
7. **Queue jobs untuk operasi berat (laporan bulanan)**
8. **Webhook notifications untuk integrasi eksternal**

## Struktur Nomor Dokumen

-   Purchase Order: `PO-YYYYMMDD-####`
-   Sales Order: `SO-YYYYMMDD-####`
-   Inbound: `IN-YYYYMMDD-####`
-   Outbound: `OUT-YYYYMMDD-####`
-   Delivery Order: `DO-YYYYMMDD-####`
-   Invoice: `INV-YYYYMMDD-####`

## Fitur Tambahan

### Queue Jobs
Generate laporan bulanan secara background:
- Laporan Penjualan
- Laporan Pembelian
- Valuasi Stok
- Alert Stok Rendah

Lihat [QUEUE_IMPLEMENTATION.md](QUEUE_IMPLEMENTATION.md) untuk detail.

### Webhook Notifications
Integrasi dengan n8n untuk notifikasi WhatsApp otomatis saat:
- Purchase Order Item dibuat
- Sales Order Item dibuat

Lihat [WEBHOOK_NOTIFICATION.md](WEBHOOK_NOTIFICATION.md) untuk konfigurasi.

### Role & Permissions
Sistem otorisasi berbasis role menggunakan Filament Shield:
- 152 permissions untuk semua resources
- 13 policies untuk authorization
- Super admin dengan akses penuh

Lihat [SHIELD_SETUP.md](SHIELD_SETUP.md) untuk setup.

## License

Proprietary - All rights reserved.
