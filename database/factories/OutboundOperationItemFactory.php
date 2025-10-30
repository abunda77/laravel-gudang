<?php

namespace Database\Factories;

use App\Models\OutboundOperationItem;
use App\Models\OutboundOperation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundOperationItemFactory extends Factory
{
    protected $model = OutboundOperationItem::class;

    public function definition(): array
    {
        return [
            'outbound_operation_id' => OutboundOperation::factory(),
            'product_id' => Product::factory(),
            'shipped_quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
