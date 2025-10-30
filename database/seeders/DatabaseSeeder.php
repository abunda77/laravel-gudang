<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in correct order to maintain referential integrity
        
        // 1. Roles and Permissions (must be first for user role assignment)
        $this->call(RolePermissionSeeder::class);
        
        // 2. Users (depends on roles)
        $this->call(UserSeeder::class);
        
        // 3. Master Data - Categories (no dependencies)
        $this->call(ProductCategorySeeder::class);
        
        // 4. Master Data - Products (depends on categories)
        $this->call(ProductSeeder::class);
        
        // 5. Master Data - Customers (no dependencies)
        $this->call(CustomerSeeder::class);
        
        // 6. Master Data - Suppliers (no dependencies)
        $this->call(SupplierSeeder::class);
        
        // 7. Master Data - Drivers (no dependencies)
        $this->call(DriverSeeder::class);
        
        // 8. Master Data - Vehicles (no dependencies)
        $this->call(VehicleSeeder::class);
        
        $this->command->info('All seeders completed successfully!');
    }
}
