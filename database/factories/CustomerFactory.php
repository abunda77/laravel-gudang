<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company' => $this->faker->optional()->company(),
            'address' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'type' => $this->faker->randomElement(['wholesale', 'retail']),
        ];
    }
}
