<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronics = ProductCategory::where('name', 'Electronics')->first();
        $furniture = ProductCategory::where('name', 'Furniture')->first();
        $stationery = ProductCategory::where('name', 'Stationery')->first();
        $hardware = ProductCategory::where('name', 'Hardware')->first();
        $textiles = ProductCategory::where('name', 'Textiles')->first();
        $foodBeverage = ProductCategory::where('name', 'Food & Beverage')->first();

        $products = [
            // Electronics
            [
                'sku' => 'ELC-001',
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with USB receiver',
                'unit' => 'pcs',
                'purchase_price' => 75000,
                'selling_price' => 125000,
                'category_id' => $electronics->id,
                'minimum_stock' => 20,
                'rack_location' => 'A1-01',
            ],
            [
                'sku' => 'ELC-002',
                'name' => 'USB Keyboard',
                'description' => 'Standard USB keyboard with numeric pad',
                'unit' => 'pcs',
                'purchase_price' => 100000,
                'selling_price' => 175000,
                'category_id' => $electronics->id,
                'minimum_stock' => 15,
                'rack_location' => 'A1-02',
            ],
            [
                'sku' => 'ELC-003',
                'name' => 'LED Monitor 24"',
                'description' => '24-inch Full HD LED monitor',
                'unit' => 'pcs',
                'purchase_price' => 1500000,
                'selling_price' => 2200000,
                'category_id' => $electronics->id,
                'minimum_stock' => 10,
                'rack_location' => 'A1-03',
            ],
            [
                'sku' => 'ELC-004',
                'name' => 'USB Flash Drive 32GB',
                'description' => '32GB USB 3.0 flash drive',
                'unit' => 'pcs',
                'purchase_price' => 50000,
                'selling_price' => 85000,
                'category_id' => $electronics->id,
                'minimum_stock' => 50,
                'rack_location' => 'A1-04',
            ],
            
            // Furniture
            [
                'sku' => 'FRN-001',
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair with adjustable height',
                'unit' => 'pcs',
                'purchase_price' => 750000,
                'selling_price' => 1200000,
                'category_id' => $furniture->id,
                'minimum_stock' => 5,
                'rack_location' => 'B1-01',
            ],
            [
                'sku' => 'FRN-002',
                'name' => 'Office Desk',
                'description' => 'Wooden office desk 120x60cm',
                'unit' => 'pcs',
                'purchase_price' => 1200000,
                'selling_price' => 1800000,
                'category_id' => $furniture->id,
                'minimum_stock' => 5,
                'rack_location' => 'B1-02',
            ],
            [
                'sku' => 'FRN-003',
                'name' => 'Filing Cabinet',
                'description' => '4-drawer metal filing cabinet',
                'unit' => 'pcs',
                'purchase_price' => 900000,
                'selling_price' => 1400000,
                'category_id' => $furniture->id,
                'minimum_stock' => 3,
                'rack_location' => 'B1-03',
            ],
            
            // Stationery
            [
                'sku' => 'STN-001',
                'name' => 'A4 Paper',
                'description' => 'A4 copy paper 80gsm (500 sheets)',
                'unit' => 'ream',
                'purchase_price' => 35000,
                'selling_price' => 55000,
                'category_id' => $stationery->id,
                'minimum_stock' => 100,
                'rack_location' => 'C1-01',
            ],
            [
                'sku' => 'STN-002',
                'name' => 'Ballpoint Pen',
                'description' => 'Blue ballpoint pen',
                'unit' => 'box',
                'purchase_price' => 15000,
                'selling_price' => 25000,
                'category_id' => $stationery->id,
                'minimum_stock' => 50,
                'rack_location' => 'C1-02',
            ],
            [
                'sku' => 'STN-003',
                'name' => 'Stapler',
                'description' => 'Heavy duty stapler',
                'unit' => 'pcs',
                'purchase_price' => 25000,
                'selling_price' => 45000,
                'category_id' => $stationery->id,
                'minimum_stock' => 20,
                'rack_location' => 'C1-03',
            ],
            [
                'sku' => 'STN-004',
                'name' => 'Notebook A5',
                'description' => 'A5 spiral notebook 100 pages',
                'unit' => 'pcs',
                'purchase_price' => 12000,
                'selling_price' => 22000,
                'category_id' => $stationery->id,
                'minimum_stock' => 30,
                'rack_location' => 'C1-04',
            ],
            
            // Hardware
            [
                'sku' => 'HRD-001',
                'name' => 'Screwdriver Set',
                'description' => '10-piece screwdriver set',
                'unit' => 'set',
                'purchase_price' => 85000,
                'selling_price' => 135000,
                'category_id' => $hardware->id,
                'minimum_stock' => 15,
                'rack_location' => 'D1-01',
            ],
            [
                'sku' => 'HRD-002',
                'name' => 'Hammer',
                'description' => 'Claw hammer 16oz',
                'unit' => 'pcs',
                'purchase_price' => 45000,
                'selling_price' => 75000,
                'category_id' => $hardware->id,
                'minimum_stock' => 10,
                'rack_location' => 'D1-02',
            ],
            [
                'sku' => 'HRD-003',
                'name' => 'Measuring Tape',
                'description' => '5-meter measuring tape',
                'unit' => 'pcs',
                'purchase_price' => 30000,
                'selling_price' => 50000,
                'category_id' => $hardware->id,
                'minimum_stock' => 20,
                'rack_location' => 'D1-03',
            ],
            
            // Textiles
            [
                'sku' => 'TXT-001',
                'name' => 'Cotton Fabric',
                'description' => 'Plain cotton fabric per meter',
                'unit' => 'meter',
                'purchase_price' => 25000,
                'selling_price' => 45000,
                'category_id' => $textiles->id,
                'minimum_stock' => 100,
                'rack_location' => 'E1-01',
            ],
            [
                'sku' => 'TXT-002',
                'name' => 'Polyester Fabric',
                'description' => 'Polyester fabric per meter',
                'unit' => 'meter',
                'purchase_price' => 30000,
                'selling_price' => 55000,
                'category_id' => $textiles->id,
                'minimum_stock' => 80,
                'rack_location' => 'E1-02',
            ],
            
            // Food & Beverage
            [
                'sku' => 'FNB-001',
                'name' => 'Instant Coffee',
                'description' => 'Instant coffee 100g jar',
                'unit' => 'jar',
                'purchase_price' => 35000,
                'selling_price' => 55000,
                'category_id' => $foodBeverage->id,
                'minimum_stock' => 50,
                'rack_location' => 'F1-01',
            ],
            [
                'sku' => 'FNB-002',
                'name' => 'Green Tea',
                'description' => 'Green tea bags (25 bags)',
                'unit' => 'box',
                'purchase_price' => 25000,
                'selling_price' => 42000,
                'category_id' => $foodBeverage->id,
                'minimum_stock' => 40,
                'rack_location' => 'F1-02',
            ],
            [
                'sku' => 'FNB-003',
                'name' => 'Mineral Water',
                'description' => 'Mineral water 600ml (24 bottles)',
                'unit' => 'carton',
                'purchase_price' => 45000,
                'selling_price' => 70000,
                'category_id' => $foodBeverage->id,
                'minimum_stock' => 30,
                'rack_location' => 'F1-03',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
