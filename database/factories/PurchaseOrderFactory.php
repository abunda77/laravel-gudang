<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => null, // Will be auto-generated
            'supplier_id' => Supplier::factory(),
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expected_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => PurchaseOrderStatus::DRAFT,
            'notes' => $this->faker->optional()->sentence(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'created_by' => User::factory(),
        ];
    }
}
