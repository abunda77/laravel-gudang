<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Agus Setiawan',
                'phone' => '0821-1111-2222',
                'photo' => null,
                'id_card_number' => '3171011234567890',
            ],
            [
                'name' => 'Bambang Prasetyo',
                'phone' => '0822-2222-3333',
                'photo' => null,
                'id_card_number' => '3171022345678901',
            ],
            [
                'name' => 'Cahyo Nugroho',
                'phone' => '0823-3333-4444',
                'photo' => null,
                'id_card_number' => '3171033456789012',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'phone' => '0824-4444-5555',
                'photo' => null,
                'id_card_number' => '3171044567890123',
            ],
            [
                'name' => 'Eko Wijaya',
                'phone' => '0825-5555-6666',
                'photo' => null,
                'id_card_number' => '3171055678901234',
            ],
            [
                'name' => 'Fajar Ramadhan',
                'phone' => '0826-6666-7777',
                'photo' => null,
                'id_card_number' => '3171066789012345',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
