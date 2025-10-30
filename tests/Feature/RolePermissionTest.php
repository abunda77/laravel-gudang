<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use App\Models\Invoice;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_roles_are_created_correctly(): void
    {
        $this->assertEquals(5, Role::count());
        
        $roles = ['super_admin', 'warehouse_admin', 'warehouse_operator', 'sales', 'accounting'];
        foreach ($roles as $roleName) {
            $this->assertDatabaseHas('roles', ['name' => $roleName]);
        }
    }

    public function test_permissions_are_created_correctly(): void
    {
        $this->assertGreaterThan(40, Permission::count());
        
        $criticalPermissions = [
            'view_products',
            'create_products',
            'view_sales_orders',
            'approve_sales_orders',
            'confirm_inbound_operations',
        ];
        
        foreach ($criticalPermissions as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission]);
        }
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $superAdmin = Role::findByName('super_admin');
        $totalPermissions = Permission::count();
        
        $this->assertEquals($totalPermissions, $superAdmin->permissions->count());
    }

    public function test_warehouse_admin_has_correct_permissions(): void
    {
        $warehouseAdmin = Role::findByName('warehouse_admin');
        
        $this->assertTrue($warehouseAdmin->hasPermissionTo('view_products'));
        $this->assertTrue($warehouseAdmin->hasPermissionTo('create_products'));
        $this->assertTrue($warehouseAdmin->hasPermissionTo('approve_purchase_orders'));
        $this->assertTrue($warehouseAdmin->hasPermissionTo('approve_sales_orders'));
        $this->assertTrue($warehouseAdmin->hasPermissionTo('view_reports'));
    }

    public function test_warehouse_operator_has_limited_permissions(): void
    {
        $warehouseOperator = Role::findByName('warehouse_operator');
        
        $this->assertTrue($warehouseOperator->hasPermissionTo('view_products'));
        $this->assertTrue($warehouseOperator->hasPermissionTo('confirm_inbound_operations'));
        $this->assertTrue($warehouseOperator->hasPermissionTo('confirm_outbound_operations'));
        
        $this->assertFalse($warehouseOperator->hasPermissionTo('create_products'));
        $this->assertFalse($warehouseOperator->hasPermissionTo('approve_sales_orders'));
    }

    public function test_sales_role_has_correct_permissions(): void
    {
        $sales = Role::findByName('sales');
        
        $this->assertTrue($sales->hasPermissionTo('view_customers'));
        $this->assertTrue($sales->hasPermissionTo('create_customers'));
        $this->assertTrue($sales->hasPermissionTo('create_sales_orders'));
        
        $this->assertFalse($sales->hasPermissionTo('approve_sales_orders'));
        $this->assertFalse($sales->hasPermissionTo('view_invoices'));
    }

    public function test_accounting_role_has_correct_permissions(): void
    {
        $accounting = Role::findByName('accounting');
        
        $this->assertTrue($accounting->hasPermissionTo('view_invoices'));
        $this->assertTrue($accounting->hasPermissionTo('create_invoices'));
        $this->assertTrue($accounting->hasPermissionTo('view_reports'));
        
        $this->assertFalse($accounting->hasPermissionTo('create_products'));
        $this->assertFalse($accounting->hasPermissionTo('create_sales_orders'));
    }

    public function test_user_with_role_can_access_permitted_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole('warehouse_admin');
        
        $this->assertTrue($user->can('viewAny', Product::class));
        $this->assertTrue($user->can('create', Product::class));
    }

    public function test_user_without_permission_cannot_access_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sales');
        
        $this->assertFalse($user->can('create', Product::class));
        $this->assertFalse($user->can('viewAny', Invoice::class));
    }
}
