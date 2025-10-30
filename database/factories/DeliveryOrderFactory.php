<?php

namespace Database\Factories;

use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\OutboundOperation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryOrderFactory extends Factory
{
    protected $model = DeliveryOrder::class;

    public function definition(): array
    {
        return [
            'do_number' => 'DO-' . now()->format('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'outbound_operation_id' => OutboundOperation::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'delivery_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'recipient_name' => $this->faker->name(),
            'notes' => $this->faker->optional()->sentence(),
            'barcode' => 'BARCODE-' . $this->faker->unique()->numerify('##########'), // Simple barcode for testing
        ];
    }
}
