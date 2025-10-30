<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Anderson',
                'company' => 'Tech Solutions Inc.',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'email' => 'john.anderson@techsolutions.com',
                'phone' => '021-5551234',
                'type' => 'wholesale',
            ],
            [
                'name' => 'Sarah Williams',
                'company' => 'Global Trading Co.',
                'address' => 'Jl. Gatot Subroto No. 456, Jakarta',
                'email' => 'sarah.williams@globaltrading.com',
                'phone' => '021-5555678',
                'type' => 'wholesale',
            ],
            [
                'name' => 'Michael Chen',
                'company' => 'Pacific Enterprises',
                'address' => 'Jl. Thamrin No. 789, Jakarta',
                'email' => 'michael.chen@pacificenterprise.com',
                'phone' => '021-5559012',
                'type' => 'wholesale',
            ],
            [
                'name' => 'Emily Rodriguez',
                'company' => 'Metro Supplies',
                'address' => 'Jl. Kuningan No. 321, Jakarta',
                'email' => 'emily.rodriguez@metrosupplies.com',
                'phone' => '021-5553456',
                'type' => 'wholesale',
            ],
            [
                'name' => 'David Thompson',
                'company' => 'Retail Plus',
                'address' => 'Jl. Senopati No. 654, Jakarta',
                'email' => 'david.thompson@retailplus.com',
                'phone' => '021-5557890',
                'type' => 'retail',
            ],
            [
                'name' => 'Lisa Martinez',
                'company' => 'Office Depot',
                'address' => 'Jl. Rasuna Said No. 987, Jakarta',
                'email' => 'lisa.martinez@officedepot.com',
                'phone' => '021-5552345',
                'type' => 'retail',
            ],
            [
                'name' => 'Robert Kim',
                'company' => 'Smart Store',
                'address' => 'Jl. Casablanca No. 147, Jakarta',
                'email' => 'robert.kim@smartstore.com',
                'phone' => '021-5556789',
                'type' => 'retail',
            ],
            [
                'name' => 'Jennifer Lee',
                'company' => 'Quick Shop',
                'address' => 'Jl. Menteng No. 258, Jakarta',
                'email' => 'jennifer.lee@quickshop.com',
                'phone' => '021-5550123',
                'type' => 'retail',
            ],
            [
                'name' => 'James Wilson',
                'company' => 'Business Hub',
                'address' => 'Jl. Kemang No. 369, Jakarta',
                'email' => 'james.wilson@businesshub.com',
                'phone' => '021-5554567',
                'type' => 'wholesale',
            ],
            [
                'name' => 'Amanda Brown',
                'company' => 'Value Mart',
                'address' => 'Jl. Blok M No. 741, Jakarta',
                'email' => 'amanda.brown@valuemart.com',
                'phone' => '021-5558901',
                'type' => 'retail',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
