<?php

namespace Database\Factories;

use App\Models\InboundOperationItem;
use App\Models\InboundOperation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboundOperationItemFactory extends Factory
{
    protected $model = InboundOperationItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        
        return [
            'inbound_operation_id' => InboundOperation::factory(),
            'product_id' => Product::factory(),
            'ordered_quantity' => $quantity,
            'received_quantity' => $quantity, // Default to same as ordered
        ];
    }
}
