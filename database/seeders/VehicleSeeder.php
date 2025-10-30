<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'license_plate' => 'B 1234 ABC',
                'vehicle_type' => 'truck',
                'ownership_status' => 'owned',
            ],
            [
                'license_plate' => 'B 5678 DEF',
                'vehicle_type' => 'truck',
                'ownership_status' => 'owned',
            ],
            [
                'license_plate' => 'B 9012 GHI',
                'vehicle_type' => 'van',
                'ownership_status' => 'owned',
            ],
            [
                'license_plate' => 'B 3456 JKL',
                'vehicle_type' => 'van',
                'ownership_status' => 'rented',
            ],
            [
                'license_plate' => 'B 7890 MNO',
                'vehicle_type' => 'truck',
                'ownership_status' => 'rented',
            ],
            [
                'license_plate' => 'B 2468 PQR',
                'vehicle_type' => 'van',
                'ownership_status' => 'owned',
            ],
            [
                'license_plate' => 'B 1357 STU',
                'vehicle_type' => 'truck',
                'ownership_status' => 'rented',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
