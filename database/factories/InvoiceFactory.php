<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => null, // Will be auto-generated
            'sales_order_id' => SalesOrder::factory(),
            'invoice_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'payment_status' => $this->faker->randomElement(InvoiceStatus::cases()),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
