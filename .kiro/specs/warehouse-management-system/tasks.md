# Implementation Plan

-   [x] 1. Setup Laravel project and install dependencies

    -   Install Laravel 12 with composer
    -   Install Filament 4 package
    -   Install Spatie Laravel Permission package
    -   Install Laravel DomPDF package
    -   Configure database connection in .env
    -   _Requirements: All requirements (foundation)_

-   [x] 2. Create database migrations for master data tables

    -   Create product_categories migration with name and description fields
    -   Create products migration with SKU, name, description, unit, prices, category_id, minimum_stock, and rack_location fields
    -   Create customers migration with name, company, address, email, phone, and type fields
    -   Create suppliers migration with name, contact, address, bank_account, and supplied_products fields
    -   Create drivers migration with name, phone, photo, and id_card_number fields
    -   Create vehicles migration with license_plate, vehicle_type, and ownership_status fields
    -   Add appropriate indexes to all tables
    -   _Requirements: 1.1, 2.1, 3.1, 4.1_

-   [x] 3. Create database migrations for transaction tables

    -   Create purchase_orders migration with po_number, supplier_id, dates, status, notes, and total_amount fields
    -   Create purchase_order_items migration with purchase_order_id, product_id, ordered_quantity, and unit_price fields
    -   Create sales_orders migration with so_number, customer_id, order_date, status, notes, total_amount, and sales_user_id fields
    -   Create sales_order_items migration with sales_order_id, product_id, quantity, and unit_price fields
    -   Create inbound_operations migration with inbound_number, purchase_order_id, received_date, notes, and received_by fields
    -   Create inbound_operation_items migration with inbound_operation_id, product_id, ordered_quantity, and received_quantity fields
    -   Create outbound_operations migration with outbound_number, sales_order_id, shipped_date, notes, and prepared_by fields
    -   Create outbound_operation_items migration with outbound_operation_id, product_id, and shipped_quantity fields
    -   Add appropriate indexes and foreign keys
    -   _Requirements: 5.1, 5.2, 6.1, 7.1, 7.2, 8.1_

-   [x] 4. Create stock movement and document migrations

    -   Create stock_movements migration with product_id, quantity, type, reference_type, reference_id, notes, and created_by fields
    -   Create stock_opnames migration with opname_number, opname_date, notes, and created_by fields
    -   Create stock_opname_items migration with stock_opname_id, product_id, system_stock, physical_stock, and variance fields
    -   Create delivery_orders migration with do_number, outbound_operation_id, driver_id, vehicle_id, delivery_date, recipient_name, notes, and barcode fields
    -   Create invoices migration with invoice_number, sales_order_id, invoice_date, due_date, payment_status, and total_amount fields
    -   Add indexes for performance optimization
    -   _Requirements: 6.4, 8.4, 10.1, 10.4, 11.1, 12.1_

-   [x] 5. Create Eloquent models for master data

    -   Create Product model with relationships to category, variants, stockMovements, purchaseOrderItems, and salesOrderItems
    -   Implement getCurrentStock(), getStockValue(), and isLowStock() methods in Product model
    -   Create ProductCategory model with relationship to products
    -   Create ProductVariant model with relationship to product
    -   Create Customer model with relationship to salesOrders
    -   Create Supplier model with relationship to purchaseOrders
    -   Create Driver model with relationship to deliveryOrders
    -   Create Vehicle model with relationship to deliveryOrders
    -   Define fillable fields and casts for all models
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 3.1, 4.1_

-   [x] 6. Create Eloquent models for transactions

    -   Create PurchaseOrder model with relationships to supplier, items, and inboundOperations
    -   Create PurchaseOrderItem model with relationships to purchaseOrder and product
    -   Create SalesOrder model with relationships to customer, items, outboundOperations, and salesUser
    -   Implement canBeApproved() and checkStockAvailability() methods in SalesOrder model
    -   Create SalesOrderItem model with relationships to salesOrder and product
    -   Create InboundOperation model with relationships to purchaseOrder, items, stockMovements, and receiver
    -   Create InboundOperationItem model with relationships to inboundOperation and product
    -   Create OutboundOperation model with relationships to salesOrder, items, stockMovements, deliveryOrder, and preparer
    -   Create OutboundOperationItem model with relationships to outboundOperation and product
    -   Define fillable fields, casts, and enums for all models
    -   _Requirements: 5.1, 5.2, 5.4, 6.1, 6.2, 7.1, 7.3, 7.6, 8.1, 8.2_

-   [x] 7. Create stock movement and document models

    -   Create StockMovement model with morphTo relationship to reference and belongsTo relationships to product and creator
    -   Create StockMovementType enum with INBOUND, OUTBOUND, ADJUSTMENT_PLUS, and ADJUSTMENT_MINUS values
    -   Create StockOpname model with relationships to items and stockMovements
    -   Create StockOpnameItem model with relationships to stockOpname and product
    -   Create DeliveryOrder model with relationships to outboundOperation, driver, and vehicle
    -   Create Invoice model with relationship to salesOrder
    -   Create InvoiceStatus enum with PAID and UNPAID values
    -   Define fillable fields and casts for all models
    -   _Requirements: 6.4, 8.4, 9.5, 10.1, 10.5, 11.1, 11.3, 12.1, 12.3_

-   [x] 8. Implement StockMovementService

    -   Create StockMovementService class in app/Services directory
    -   Implement recordInbound() method to create stock movements for inbound operations with database transaction
    -   Implement recordOutbound() method to create stock movements for outbound operations with database transaction
    -   Implement recordAdjustment() method to create stock movements for stock opname adjustments
    -   Implement getCurrentStock() method to calculate current stock from sum of stock movements
    -   Implement checkAvailability() method to verify stock availability for sales orders
    -   Add proper error handling and validation
    -   _Requirements: 6.4, 8.4, 9.1, 9.5, 10.4, 10.5_

-   [x] 9. Implement DocumentGenerationService

    -   Create DocumentGenerationService class in app/Services directory
    -   Implement generateDeliveryOrder() method to create PDF from delivery order data
    -   Implement generateInvoice() method to create PDF from invoice data
    -   Implement generateBarcode() method to create barcode for delivery order tracking
    -   Create Blade templates for delivery order PDF layout
    -   Create Blade templates for invoice PDF layout
    -   _Requirements: 11.4, 11.5, 12.4_

-   [x] 10. Implement ReportService

    -   Create ReportService class in app/Services directory
    -   Implement getStockCard() method to retrieve all stock movements for a product with running balance calculation
    -   Implement getLowStockProducts() method to filter products below minimum stock threshold
    -   Implement getStockValuationReport() method to calculate total inventory value
    -   Implement getSalesReport() method with filters for date range, product, customer, and sales user
    -   Implement getPurchaseReport() method with filters for date range, product, and supplier
    -   Add proper query optimization with eager loading
    -   _Requirements: 17.1, 17.2, 17.3, 18.1, 19.1, 20.1, 20.2, 20.3, 20.4, 20.5, 21.1, 21.2, 21.3, 21.4_

-   [x] 11. Setup Spatie Laravel Permission for role-based access control

    -   Run Spatie permission migrations
    -   Create seeder for roles: super_admin, warehouse_admin, warehouse_operator, sales, and accounting
    -   Create seeder for permissions mapped to each role
    -   Assign permissions to roles according to requirements
    -   Create policies for Product, Customer, Supplier, PurchaseOrder, SalesOrder, InboundOperation, OutboundOperation, and Invoice models
    -   Implement authorization logic in each policy based on user roles
    -   _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_

-   [x] 12. Create Filament resources for master data

-   [x] 12.1 Create Product resource with form and table schemas

    -   Generate ProductResource with pages for list, create, and edit
    -   Create ProductForm schema class with sections for basic information, pricing & stock, and variants using repeater
    -   Create ProductTable schema class with columns for SKU, name, category, current stock with badge color, and selling price
    -   Add filters for category and low stock products
    -   Add search functionality for SKU and name
    -   Implement authorization using ProductPolicy
    -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

-   [x] 12.2 Create Customer resource with form and table schemas

    -   Generate CustomerResource with pages for list, create, and edit
    -   Create CustomerForm schema class with fields for name, company, address, email, phone, and type
    -   Create CustomerTable schema class with columns for name, company, email, phone, and type
    -   Add infolist to display transaction history on view page
    -   Add search and filter capabilities
    -   _Requirements: 2.1, 2.2, 2.3_

-   [x] 12.3 Create Supplier resource with form and table schemas

    -   Generate SupplierResource with pages for list, create, and edit
    -   Create SupplierForm schema class with fields for name, contact, address, bank account, and supplied products
    -   Create SupplierTable schema class with columns for name, contact, and address
    -   Add relationship to purchase orders for traceability
    -   _Requirements: 3.1, 3.2, 3.3_

-   [x] 12.4 Create Driver and Vehicle resources

    -   Generate DriverResource with form for name, phone, photo upload, and ID card number
    -   Generate VehicleResource with form for license plate, vehicle type select, and ownership status select
    -   Create table schemas with appropriate columns and filters
    -   _Requirements: 4.1, 4.2_

-   [x] 13. Create Filament resources for purchase workflow

-   [x] 13.1 Create PurchaseOrder resource with form and table schemas

    -   Generate PurchaseOrderResource with pages for list, create, and edit
    -   Create PurchaseOrderForm schema class with supplier select, order date, expected date, and repeater for items
    -   Implement automatic PO number generation in model boot method
    -   Create PurchaseOrderTable schema class with columns for PO number, supplier, order date, status badge, and total amount
    -   Add filters for status and supplier
    -   Implement status tracking (draft, sent, partially_received, completed)
    -   _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

-   [x] 13.2 Create InboundOperation resource with form and table schemas

    -   Generate InboundOperationResource with pages for list, create, and edit
    -   Create InboundOperationForm schema class with purchase order select that auto-loads product list
    -   Add repeater for items showing ordered quantity and input for received quantity
    -   Implement automatic inbound number generation
    -   Create InboundOperationTable schema class with columns for inbound number, PO number, received date, and receiver
    -   Add action to confirm inbound that triggers StockMovementService.recordInbound()
    -   Implement automatic purchase order status update based on received quantities
    -   _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

-   [x] 14. Create Filament resources for sales workflow

-   [x] 14.1 Create SalesOrder resource with form and table schemas

    -   Generate SalesOrderResource with pages for list, create, and edit
    -   Create SalesOrderForm schema class with customer select, order date, and repeater for items with stock availability display
    -   Implement automatic SO number generation in model boot method
    -   Create SalesOrderTable schema class with columns for SO number, customer, order date, status badge, and total amount
    -   Add filters for status and customer
    -   Implement stock availability check before approval using StockMovementService
    -   Add custom action for approval that validates stock and updates status
    -   _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

-   [x] 14.2 Create OutboundOperation resource with form and table schemas

    -   Generate OutboundOperationResource with pages for list, create, and edit
    -   Create OutboundOperationForm schema class with approved sales order select that auto-loads product list
    -   Add repeater for items with confirmation checkboxes
    -   Implement automatic outbound number generation
    -   Create OutboundOperationTable schema class with columns for outbound number, SO number, shipped date, and preparer
    -   Add action to confirm outbound that triggers StockMovementService.recordOutbound()
    -   Implement automatic sales order status update based on fulfilled quantities
    -   _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

-   [x] 15. Create Filament resources for stock management

-   [x] 15.1 Create inventory viewing page

    -   Create custom Filament page for real-time inventory display
    -   Display current stock calculated from StockMovementService.getCurrentStock() for all products
    -   Add filters for product category
    -   Add search by SKU and product name
    -   Highlight products with stock below minimum threshold using badge colors
    -   _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

-   [x] 15.2 Create StockOpname resource with form and table schemas

    -   Generate StockOpnameResource with pages for list, create, and edit
    -   Create StockOpnameForm schema class with opname date and repeater for products with system stock display and physical stock input
    -   Implement automatic variance calculation (physical - system stock)
    -   Implement automatic opname number generation
    -   Create StockOpnameTable schema class with columns for opname number, date, and created by
    -   Add action to confirm opname that triggers StockMovementService.recordAdjustment() for each variance
    -   _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

-   [x] 16. Create Filament resources for documents

-   [x] 16.1 Create DeliveryOrder resource with form and table schemas

    -   Generate DeliveryOrderResource with pages for list, create, and edit
    -   Create DeliveryOrderForm schema class with outbound operation select, driver select, vehicle select, and delivery date
    -   Implement automatic DO number and barcode generation using DocumentGenerationService
    -   Create DeliveryOrderTable schema class with columns for DO number, outbound number, driver, vehicle, and delivery date
    -   Add action to print delivery order PDF using DocumentGenerationService.generateDeliveryOrder()
    -   _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

-   [x] 16.2 Create Invoice resource with form and table schemas

    -   Generate InvoiceResource with pages for list, create, and edit
    -   Create InvoiceForm schema class with sales order or outbound operation select, invoice date, due date, and payment status
    -   Implement automatic invoice number generation
    -   Create InvoiceTable schema class with columns for invoice number, customer, invoice date, payment status badge, and total amount
    -   Add action to print invoice PDF using DocumentGenerationService.generateInvoice()
    -   Add action to send invoice via email
    -   _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

-   [x] 17. Create dashboard widgets

-   [x] 17.1 Create StatsOverview widget

    -   Create StatsOverview widget class extending StatsOverviewWidget
    -   Implement stat for total stock value calculated from all products using StockMovementService
    -   Implement stat for total customer count
    -   Implement stat for today's inbound operation count
    -   Implement stat for today's outbound operation count
    -   Register widget in Dashboard page
    -   _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5_

-   [x] 17.2 Create chart widgets

    -   Create SalesChart widget class extending ChartWidget for line chart
    -   Implement getData() method to fetch outbound operation counts for last 7 days
    -   Create InboundVsOutboundChart widget class for comparison chart
    -   Register widgets in Dashboard page
    -   _Requirements: 15.1, 15.2, 15.3_

-   [x] 17.3 Create table widgets for quick information

    -   Create TopProductsTable widget class extending TableWidget
    -   Implement query to fetch top 5 best-selling products based on outbound operation items
    -   Create LowStockTable widget class extending TableWidget
    -   Implement query to fetch products with stock below minimum threshold
    -   Create RecentActivityTable widget class to display recent inbound and outbound operations
    -   Register widgets in Dashboard page
    -   _Requirements: 16.1, 16.2, 16.3_

-   [x] 18. Create report pages

-   [x] 18.1 Create StockCardReport page

    -   Create custom Filament page for stock card report
    -   Add form with product select, start date, and end date filters
    -   Implement generate() method that calls ReportService.getStockCard()
    -   Display stock movements table with columns for date, type, reference, quantity, and running balance
    -   Add exportPdf() action to generate PDF using Blade template
    -   _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5_

-   [x] 18.2 Create LowStockReport page

    -   Create custom Filament page for low stock report
    -   Implement table display using ReportService.getLowStockProducts()
    -   Display columns for SKU, name, current stock, and minimum threshold with badge colors
    -   Add export to PDF and Excel actions
    -   _Requirements: 18.1, 18.2, 18.3_

-   [x] 18.3 Create StockValuationReport page

    -   Create custom Filament page for stock valuation report
    -   Add form with product category filter
    -   Implement calculation using ReportService.getStockValuationReport()
    -   Display table with columns for product, current stock, purchase price, and total value
    -   Display total inventory value summary
    -   Add export to PDF and Excel actions
    -   _Requirements: 19.1, 19.2, 19.3, 19.4_

-   [x] 18.4 Create SalesReport page

    -   Create custom Filament page for sales report
    -   Add form with filters for date range (daily, monthly, yearly), product, customer, and sales user
    -   Implement generate() method that calls ReportService.getSalesReport() with filters
    -   Display table with columns for date, customer, product, quantity, and value
    -   Display total sales quantity and value summary
    -   Add export to PDF and Excel actions
    -   _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 20.6, 20.7_

-   [x] 18.5 Create PurchaseReport page

    -   Create custom Filament page for purchase report
    -   Add form with filters for date range (daily, monthly, yearly), product, and supplier
    -   Implement generate() method that calls ReportService.getPurchaseReport() with filters
    -   Display table with columns for date, supplier, product, quantity, and value
    -   Display total purchase quantity and value summary
    -   Add export to PDF and Excel actions
    -   _Requirements: 21.1, 21.2, 21.3, 21.4, 21.5, 21.6_

-   [x] 19. Implement performance optimizations

    -   Add database indexes to stock_movements table for product_id and created_at
    -   Add database indexes to purchase_orders and sales_orders tables for status and dates
    -   Implement eager loading in all Filament table queries to prevent N+1 queries
    -   Add caching for dashboard statistics with 5-minute TTL
    -   Add caching for product stock calculations with 1-hour TTL
    -   Implement cache invalidation on stock movement creation
    -   _Requirements: 9.5, 14.5, 15.3_

-   [x] 20. Setup queue jobs for heavy operations

    -   Configure queue driver in .env (Redis or database)
    -   Create GenerateMonthlyReport job class implementing ShouldQueue
    -   Implement handle() method to generate report using ReportService and store in storage
    -   Create notification class for report completion
    -   Add Filament action to dispatch GenerateMonthlyReport job
    -   Create supervisor configuration for queue workers
    -   _Requirements: 20.7, 21.6_

-   [x] 21. Configure Filament panel and navigation

    -   Configure Filament panel in FilamentServiceProvider with branding and colors
    -   Organize navigation groups: Master Data, Transactions, Stock Management, Documents, and Reports
    -   Set navigation icons for all resources using Heroicons
    -   Configure navigation order for logical grouping
    -   Add navigation badges for low stock count and pending approvals
    -   _Requirements: All requirements (user interface)_

-   [x] 22. Implement error handling and validation

    -   Create InsufficientStockException custom exception class
    -   Create InvalidStatusTransitionException custom exception class
    -   Implement stock availability validation in SalesOrder model before approval
    -   Implement duplicate document number prevention in all document models using boot method
    -   Wrap all stock-modifying operations in database transactions
    -   Add try-catch blocks in service methods with proper error messages
    -   _Requirements: 6.4, 7.6, 8.4, 10.5_

-   [x] 23. Write unit tests for services

    -   Create StockMovementServiceTest with tests for recordInbound(), recordOutbound(), recordAdjustment(), getCurrentStock(), and checkAvailability() methods
    -   Create DocumentGenerationServiceTest with tests for PDF generation methods
    -   Create ReportServiceTest with tests for all report generation methods
    -   Use factories for test data creation
    -   _Requirements: 6.4, 8.4, 9.5, 10.5, 11.4, 12.4, 17.1, 18.1, 19.1, 20.1, 21.1_

-   [x] 24. Write feature tests for workflows

    -   Create InboundOperationTest to test complete inbound workflow from PO to stock increase
    -   Create OutboundOperationTest to test complete outbound workflow from SO to stock decrease
    -   Create StockOpnameTest to test stock adjustment workflow
    -   Create DeliveryOrderTest to test delivery order generation workflow
    -   Verify database state and stock movements after each workflow
    -   _Requirements: 6.1, 6.4, 8.1, 8.4, 10.1, 10.5, 11.1_

-   [x] 25. Write Filament resource tests

    -   Create ProductResourceTest to test list page rendering and product creation
    -   Create PurchaseOrderResourceTest to test PO creation and approval workflow
    -   Create SalesOrderResourceTest to test SO creation with stock validation
    -   Create InboundOperationResourceTest to test inbound processing
    -   Create OutboundOperationResourceTest to test outbound processing
    -   Use Livewire testing utilities for form interactions
    -   _Requirements: 1.1, 5.1, 7.1, 6.1, 8.1_

-   [x] 26. Create seeders for demo data

    -   Create ProductCategorySeeder with sample categories
    -   Create ProductSeeder with sample products linked to categories
    -   Create CustomerSeeder with sample customers
    -   Create SupplierSeeder with sample suppliers
    -   Create DriverSeeder and VehicleSeeder with sample data
    -   Create UserSeeder with users for each role
    -   Create DatabaseSeeder to run all seeders in correct order
    -   _Requirements: All requirements (demo data)_

-   [x] 27. Setup application configuration and deployment

    -   Configure environment variables for production
    -   Setup database backup cron job
    -   Configure Laravel Telescope for monitoring in development
    -   Setup error tracking with Sentry or similar service
    -   Configure rate limiting for API endpoints
    -   Create deployment documentation with server requirements
    -   Setup supervisor configuration for queue workers
    -   Configure cron jobs for scheduled tasks
    -   _Requirements: All requirements (deployment)_
