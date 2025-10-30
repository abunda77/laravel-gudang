<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
            ],
            [
                'name' => 'Furniture',
                'description' => 'Office and home furniture',
            ],
            [
                'name' => 'Stationery',
                'description' => 'Office supplies and stationery items',
            ],
            [
                'name' => 'Hardware',
                'description' => 'Tools and hardware equipment',
            ],
            [
                'name' => 'Textiles',
                'description' => 'Fabrics and textile products',
            ],
            [
                'name' => 'Food & Beverage',
                'description' => 'Food and beverage products',
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
