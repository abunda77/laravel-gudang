<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole('super_admin');

        // Warehouse Admin
        $warehouseAdmin = User::create([
            'name' => 'Warehouse Admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $warehouseAdmin->assignRole('warehouse_admin');

        // Warehouse Operators
        $operator1 = User::create([
            'name' => 'Operator One',
            'email' => 'operator1@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $operator1->assignRole('warehouse_operator');

        $operator2 = User::create([
            'name' => 'Operator Two',
            'email' => 'operator2@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $operator2->assignRole('warehouse_operator');

        // Sales Users
        $sales1 = User::create([
            'name' => 'Sales One',
            'email' => 'sales1@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $sales1->assignRole('sales');

        $sales2 = User::create([
            'name' => 'Sales Two',
            'email' => 'sales2@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $sales2->assignRole('sales');

        // Accounting Users
        $accounting1 = User::create([
            'name' => 'Accounting One',
            'email' => 'accounting1@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $accounting1->assignRole('accounting');

        $accounting2 = User::create([
            'name' => 'Accounting Two',
            'email' => 'accounting2@warehouse.com',
            'password' => Hash::make('password'),
        ]);
        $accounting2->assignRole('accounting');
    }
}
