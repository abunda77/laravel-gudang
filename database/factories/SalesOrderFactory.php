<?php

namespace Database\Factories;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'so_number' => null, // Will be auto-generated
            'customer_id' => Customer::factory(),
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => SalesOrderStatus::DRAFT,
            'notes' => $this->faker->optional()->sentence(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'sales_user_id' => User::factory(),
        ];
    }
}
