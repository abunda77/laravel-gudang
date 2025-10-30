<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'license_plate' => $this->faker->unique()->bothify('??-####-??'),
            'vehicle_type' => $this->faker->randomElement(['truck', 'van']),
            'ownership_status' => $this->faker->randomElement(['owned', 'rented']),
        ];
    }
}
