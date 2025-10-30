<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact' => $this->faker->name(),
            'address' => $this->faker->address(),
            'bank_account' => $this->faker->bankAccountNumber(),
            'supplied_products' => $this->faker->optional()->sentence(),
        ];
    }
}
