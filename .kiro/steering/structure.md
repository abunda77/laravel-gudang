# Project Structure

## Directory Organization

### Application Layer (`app/`)

```
app/
├── Console/Commands/       # Artisan commands (e.g., BackupDatabase)
├── Enums/                  # Enum classes for status types
│   ├── InvoiceStatus.php
│   ├── PurchaseOrderStatus.php
│   ├── SalesOrderStatus.php
│   └── StockMovementType.php
├── Exceptions/             # Custom exception classes
│   ├── InsufficientStockException.php
│   └── InvalidStatusTransitionException.php
├── Filament/               # Filament admin panel components
│   ├── Pages/              # Custom Filament pages (Dashboard, Reports)
│   ├── Resources/          # CRUD resources for models
│   └── Widgets/            # Dashboard widgets
├── Http/Controllers/       # HTTP controllers
├── Jobs/                   # Queue jobs (e.g., GenerateMonthlyReport)
├── Models/                 # Eloquent models
├── Notifications/          # Notification classes
├── Policies/               # Authorization policies
├── Providers/              # Service providers
└── Services/               # Business logic services
    ├── DocumentGenerationService.php
    ├── ReportService.php
    └── StockMovementService.php
```

### Filament Resources Pattern

Each Filament resource follows a modular structure:

```
app/Filament/Resources/
├── ProductResource/
│   ├── Pages/              # List, Create, Edit pages
│   ├── Schemas/            # Separated form and table schemas
│   │   ├── ProductForm.php
│   │   └── ProductTable.php
│   └── ProductResource.php # Main resource class
```

**Key Resources:**

-   ProductResource (with variants support)
-   CustomerResource
-   SupplierResource
-   PurchaseOrderResource
-   SalesOrderResource
-   InboundOperationResource
-   OutboundOperationResource
-   DeliveryOrderResource
-   InvoiceResource
-   StockOpnameResource
-   DriverResource
-   VehicleResource

### Models (`app/Models/`)

**Master Data:**

-   Product, ProductCategory, ProductVariant
-   Customer, Supplier
-   Driver, Vehicle

**Transactions:**

-   PurchaseOrder, PurchaseOrderItem
-   SalesOrder, SalesOrderItem
-   InboundOperation, InboundOperationItem
-   OutboundOperation, OutboundOperationItem
-   StockMovement (critical for inventory tracking)
-   StockOpname, StockOpnameItem
-   DeliveryOrder
-   Invoice

### Configuration (`config/`)

-   `app.php` - Application settings (locale: id)
-   `database.php` - Database connections
-   `queue.php` - Queue configuration
-   `cache.php` - Cache configuration
-   `permission.php` - Spatie permission settings
-   `filesystems.php` - Storage configuration
-   `mail.php` - Email settings
-   `telescope.php` - Debugging tool config

### Database (`database/`)

```
database/
├── factories/              # Model factories for testing
├── migrations/             # Database migrations
└── seeders/                # Database seeders
```

### Resources (`resources/`)

```
resources/
├── css/                    # Stylesheets
│   └── app.css
├── js/                     # JavaScript files
│   └── app.js
└── views/                  # Blade templates
    └── documents/          # PDF templates for DO and invoices
```

### Tests (`tests/`)

```
tests/
├── Feature/                # Feature/integration tests
├── Unit/                   # Unit tests
└── TestCase.php            # Base test case
```

### Public Assets (`public/`)

```
public/
├── css/                    # Compiled CSS
├── js/                     # Compiled JavaScript
├── fonts/                  # Web fonts
├── images/                 # Static images
└── storage/                # Symlinked storage
```

## Architectural Patterns

### Service Layer Pattern

Business logic is encapsulated in service classes:

-   `StockMovementService` - Handles all stock operations
-   `DocumentGenerationService` - PDF generation
-   `ReportService` - Complex report queries

### Repository Pattern (Implicit)

Eloquent models act as repositories with custom query methods.

### Policy-Based Authorization

Each resource has a corresponding policy class for authorization checks.

### Schema Separation

Filament forms and tables are separated into dedicated schema classes for better maintainability.

## Naming Conventions

### Files & Classes

-   Models: Singular PascalCase (e.g., `Product.php`)
-   Controllers: PascalCase with suffix (e.g., `ProductController.php`)
-   Services: PascalCase with suffix (e.g., `StockMovementService.php`)
-   Enums: PascalCase (e.g., `StockMovementType.php`)

### Database

-   Tables: Plural snake_case (e.g., `purchase_orders`)
-   Pivot tables: Alphabetical order (e.g., `product_supplier`)
-   Foreign keys: Singular with `_id` (e.g., `product_id`)

### Routes

-   Resource routes: Plural kebab-case (e.g., `/purchase-orders`)
-   API routes: Prefixed with `/api/v1/`

### Document Numbers

-   Purchase Order: `PO-YYYYMMDD-####`
-   Sales Order: `SO-YYYYMMDD-####`
-   Inbound: `IN-YYYYMMDD-####`
-   Outbound: `OUT-YYYYMMDD-####`
-   Delivery Order: `DO-YYYYMMDD-####`
-   Invoice: `INV-YYYYMMDD-####`

## Key Conventions

1. **All stock changes must go through StockMovementService**
2. **Use database transactions for multi-step operations**
3. **Policies must be defined for all resources**
4. **Form and table schemas should be in separate files**
5. **Use Enums for status fields**
6. **Follow PSR-12 coding standards (enforced by Pint)**
7. **Write tests for business logic in services**
8. **Use factories for test data generation**
