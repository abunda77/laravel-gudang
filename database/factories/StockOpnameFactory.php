<?php

namespace Database\Factories;

use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockOpnameFactory extends Factory
{
    protected $model = StockOpname::class;

    public function definition(): array
    {
        return [
            'opname_number' => null, // Will be auto-generated
            'opname_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
