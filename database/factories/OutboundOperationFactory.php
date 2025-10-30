<?php

namespace Database\Factories;

use App\Models\OutboundOperation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutboundOperationFactory extends Factory
{
    protected $model = OutboundOperation::class;

    public function definition(): array
    {
        return [
            'outbound_number' => null, // Will be auto-generated
            'sales_order_id' => SalesOrder::factory(),
            'shipped_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'prepared_by' => User::factory(),
        ];
    }
}
