# Filament Shield Setup

## Installation Summary

Filament Shield has been successfully installed and configured for the Warehouse Management System.

## What Was Done

1. **Package Installation**

    - Installed `bezhansalleh/filament-shield` v4.0.2
    - Installed dependency `bezhansalleh/filament-plugin-essentials` v1.0.0

2. **User Model Configuration**

    - Added `HasRoles` trait to `App\Models\User`
    - User model is now ready for role-based permissions

3. **Plugin Registration**

    - Registered `FilamentShieldPlugin` in `AdminPanelProvider`
    - Plugin is active on the admin panel

4. **Permissions & Policies Generated**

    - Generated **152 permissions** for all resources, pages, and widgets
    - Generated **13 policies** for all models
    - Processed **22 entities** (Resources, Pages, Widgets)

5. **Super Admin Created**
    - User: erieputranto@gmail.com (ID: 2)
    - Role: super_admin
    - Has full access to all resources

## Generated Policies

The following policies were automatically generated:

-   CustomerPolicy
-   DeliveryOrderPolicy
-   DriverPolicy
-   InboundOperationPolicy
-   InvoicePolicy
-   OutboundOperationPolicy
-   ProductPolicy
-   PurchaseOrderPolicy
-   RolePolicy
-   SalesOrderPolicy
-   StockOpnamePolicy
-   SupplierPolicy
-   VehiclePolicy

## Permission Format

Permissions follow the PascalCase format with `:` separator:

-   Format: `{Action}:{Resource}`
-   Examples: `ViewAny:Product`, `Create:Customer`, `Update:SalesOrder`

## Accessing Role Management

The Role Resource is available at:

-   URL: `/admin/shield/roles`
-   Navigation: Automatically added to the admin panel

## Next Steps

1. **Create Additional Roles**

    - Navigate to `/admin/shield/roles`
    - Create roles like: Warehouse Manager, Sales Staff, Accounting, etc.
    - Assign appropriate permissions to each role

2. **Assign Roles to Users**

    - Edit user resources to assign roles
    - Users can have multiple roles
    - Permissions are cumulative

3. **Customize Permissions (Optional)**
    - Edit `config/filament-shield.php` to customize behavior
    - Adjust permission naming, policy methods, etc.

## Configuration

The Shield configuration is located at:

-   `config/filament-shield.php`

Key configuration options:

-   `shield_resource.slug`: URL for role management
-   `shield_resource.tabs`: Enable/disable permission tabs
-   `permissions.separator`: Permission key separator (default: `:`)
-   `permissions.case`: Permission key case (default: `pascal`)

## Commands Reference

```bash
# Generate permissions for all entities
php artisan shield:generate --all --panel=admin

# Create super admin
php artisan shield:super-admin

# Publish Shield resource (if customization needed)
php artisan shield:publish --panel=admin

# Setup Shield (fresh install)
php artisan shield:setup --fresh
```

## Database Tables

Shield uses Spatie Laravel Permission tables:

-   `roles` - Stores roles
-   `permissions` - Stores permissions
-   `model_has_roles` - User-role assignments
-   `model_has_permissions` - Direct user permissions
-   `role_has_permissions` - Role-permission assignments

## Current Statistics

-   **Roles**: 1 (super_admin)
-   **Permissions**: 152
-   **Policies**: 13
-   **Users with Roles**: 1 (Super Admin)

## Resources

-   [Shield Documentation](https://filamentphp.com/plugins/bezhansalleh-shield)
-   [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
