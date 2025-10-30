<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Product permissions
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Customer permissions
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            
            // Supplier permissions
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            
            // Purchase Order permissions
            'view_purchase_orders',
            'create_purchase_orders',
            'edit_purchase_orders',
            'delete_purchase_orders',
            'approve_purchase_orders',
            
            // Sales Order permissions
            'view_sales_orders',
            'create_sales_orders',
            'edit_sales_orders',
            'delete_sales_orders',
            'approve_sales_orders',
            
            // Inbound Operation permissions
            'view_inbound_operations',
            'create_inbound_operations',
            'edit_inbound_operations',
            'delete_inbound_operations',
            'confirm_inbound_operations',
            
            // Outbound Operation permissions
            'view_outbound_operations',
            'create_outbound_operations',
            'edit_outbound_operations',
            'delete_outbound_operations',
            'confirm_outbound_operations',
            
            // Invoice permissions
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            
            // Stock Management permissions
            'view_stock',
            'view_stock_opname',
            'create_stock_opname',
            'confirm_stock_opname',
            
            // Report permissions
            'view_reports',
            'export_reports',
            
            // Dashboard permissions
            'view_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - Full access
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Warehouse Admin - Master data management, purchase orders, and approval functions
        $warehouseAdmin = Role::create(['name' => 'warehouse_admin']);
        $warehouseAdmin->givePermissionTo([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_purchase_orders', 'create_purchase_orders', 'edit_purchase_orders', 'delete_purchase_orders', 'approve_purchase_orders',
            'view_sales_orders', 'approve_sales_orders',
            'view_inbound_operations', 'view_outbound_operations',
            'view_stock', 'view_stock_opname', 'create_stock_opname', 'confirm_stock_opname',
            'view_reports', 'export_reports',
            'view_dashboard',
        ]);

        // Warehouse Operator - Inbound and outbound operation processing
        $warehouseOperator = Role::create(['name' => 'warehouse_operator']);
        $warehouseOperator->givePermissionTo([
            'view_products',
            'view_inbound_operations', 'create_inbound_operations', 'edit_inbound_operations', 'confirm_inbound_operations',
            'view_outbound_operations', 'create_outbound_operations', 'edit_outbound_operations', 'confirm_outbound_operations',
            'view_stock',
            'view_dashboard',
        ]);

        // Sales - Sales order creation and customer data viewing
        $sales = Role::create(['name' => 'sales']);
        $sales->givePermissionTo([
            'view_products',
            'view_customers', 'create_customers', 'edit_customers',
            'view_sales_orders', 'create_sales_orders', 'edit_sales_orders',
            'view_stock',
            'view_dashboard',
        ]);

        // Accounting - Invoice management and reports
        $accounting = Role::create(['name' => 'accounting']);
        $accounting->givePermissionTo([
            'view_sales_orders',
            'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
            'view_reports', 'export_reports',
            'view_dashboard',
        ]);
    }
}
