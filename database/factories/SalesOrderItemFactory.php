<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderItemFactory extends Factory
{
    protected $model = SalesOrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        
        return [
            'sales_order_id' => SalesOrder::factory(),
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_price' => $product->selling_price,
        ];
    }
}
