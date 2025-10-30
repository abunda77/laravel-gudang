<?php

namespace Database\Factories;

use App\Models\InboundOperation;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboundOperationFactory extends Factory
{
    protected $model = InboundOperation::class;

    public function definition(): array
    {
        return [
            'inbound_number' => null, // Will be auto-generated
            'purchase_order_id' => PurchaseOrder::factory(),
            'received_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'received_by' => User::factory(),
        ];
    }
}
