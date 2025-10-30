<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Elektronik Jaya',
                'contact' => 'Budi Santoso - 0812-3456-7890',
                'address' => 'Jl. Industri Raya No. 100, Tangerang',
                'bank_account' => 'BCA 1234567890',
                'supplied_products' => 'Electronics, Computer Accessories',
            ],
            [
                'name' => 'CV Mebel Indah',
                'contact' => 'Siti Nurhaliza - 0813-4567-8901',
                'address' => 'Jl. Furniture Street No. 50, Bekasi',
                'bank_account' => 'Mandiri 2345678901',
                'supplied_products' => 'Office Furniture, Home Furniture',
            ],
            [
                'name' => 'PT Alat Tulis Sejahtera',
                'contact' => 'Ahmad Wijaya - 0814-5678-9012',
                'address' => 'Jl. Stationery Plaza No. 25, Jakarta',
                'bank_account' => 'BNI 3456789012',
                'supplied_products' => 'Stationery, Office Supplies',
            ],
            [
                'name' => 'UD Perkakas Mandiri',
                'contact' => 'Dewi Lestari - 0815-6789-0123',
                'address' => 'Jl. Hardware Center No. 75, Bogor',
                'bank_account' => 'BRI 4567890123',
                'supplied_products' => 'Tools, Hardware Equipment',
            ],
            [
                'name' => 'PT Tekstil Nusantara',
                'contact' => 'Rudi Hartono - 0816-7890-1234',
                'address' => 'Jl. Textile Industry No. 200, Bandung',
                'bank_account' => 'BCA 5678901234',
                'supplied_products' => 'Fabrics, Textile Materials',
            ],
            [
                'name' => 'CV Makanan Minuman Berkah',
                'contact' => 'Rina Kusuma - 0817-8901-2345',
                'address' => 'Jl. Food District No. 150, Depok',
                'bank_account' => 'Mandiri 6789012345',
                'supplied_products' => 'Food Products, Beverages',
            ],
            [
                'name' => 'PT Global Electronics',
                'contact' => 'Hendra Gunawan - 0818-9012-3456',
                'address' => 'Jl. Technology Park No. 300, Jakarta',
                'bank_account' => 'BNI 7890123456',
                'supplied_products' => 'Electronics, IT Equipment',
            ],
            [
                'name' => 'UD Sumber Rejeki',
                'contact' => 'Yanti Permata - 0819-0123-4567',
                'address' => 'Jl. Wholesale Market No. 400, Tangerang',
                'bank_account' => 'BRI 8901234567',
                'supplied_products' => 'General Supplies, Mixed Products',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
