# Requirements Document

## Introduction

This document outlines the requirements for a Warehouse Management System (WMS) built with Laravel 12 and Filament 4. The system manages inventory operations including product master data, purchase orders, sales orders, inbound/outbound operations, stock management, and comprehensive reporting capabilities. The system supports multiple user roles (Super Admin, Warehouse Admin, Warehouse Operator, Sales, and Accounting) with role-based access control.

## Glossary

- **WMS**: Warehouse Management System - the complete application system
- **Filament Resource**: A Filament component that provides CRUD operations for a model
- **SKU**: Stock Keeping Unit - unique identifier for each product
- **PO**: Purchase Order - document requesting goods from supplier
- **SO**: Sales Order - document recording customer order
- **Stock Movement**: Any transaction that changes product quantity (inbound, outbound, adjustment)
- **Stock Opname**: Physical stock count and adjustment process
- **Delivery Order**: Document accompanying goods during delivery
- **Stock Card**: Historical record of all movements for a specific product
- **Low Stock**: Product quantity below minimum threshold
- **Inbound**: Process of receiving goods into warehouse
- **Outbound**: Process of releasing goods from warehouse

## Requirements

### Requirement 1: Product Master Data Management

**User Story:** As a Warehouse Admin, I want to manage product master data with complete details, so that I can maintain accurate product information for inventory operations.

#### Acceptance Criteria

1. THE WMS SHALL store product data including SKU code, product name, description, unit of measure, purchase price, and selling price
2. THE WMS SHALL allow assignment of products to categories for grouping purposes
3. THE WMS SHALL support product variants with attributes such as color and size using repeater fields
4. THE WMS SHALL store minimum stock threshold for each product
5. WHERE warehouse uses rack locations, THE WMS SHALL allow storage of rack location data for each product

### Requirement 2: Customer Master Data Management

**User Story:** As a Warehouse Admin, I want to manage customer information with transaction history, so that I can track customer relationships and sales patterns.

#### Acceptance Criteria

1. THE WMS SHALL store customer data including name, company, address, email, phone number, and customer type (wholesale or retail)
2. WHEN viewing customer details, THE WMS SHALL display transaction history linked to sales orders and outbound operations
3. THE WMS SHALL provide search and filter capabilities for customer records

### Requirement 3: Supplier Master Data Management

**User Story:** As a Warehouse Admin, I want to manage supplier information, so that I can track sources for inbound inventory.

#### Acceptance Criteria

1. THE WMS SHALL store supplier data including name, contact information, address, bank account number, and supplied products
2. THE WMS SHALL link suppliers to purchase orders for traceability
3. THE WMS SHALL provide search and filter capabilities for supplier records

### Requirement 4: Supporting Master Data Management

**User Story:** As a Warehouse Admin, I want to manage driver and vehicle information, so that I can assign delivery resources to outbound operations.

#### Acceptance Criteria

1. THE WMS SHALL store driver data including name, phone number, photo, and ID card number
2. THE WMS SHALL store vehicle data including license plate, vehicle type (truck or van), and ownership status (owned or rented)
3. THE WMS SHALL allow assignment of drivers and vehicles to delivery orders

### Requirement 5: Purchase Order Creation and Management

**User Story:** As a Warehouse Admin, I want to create and manage purchase orders, so that I can request goods from suppliers systematically.

#### Acceptance Criteria

1. THE WMS SHALL allow creation of purchase orders with supplier selection
2. WHEN creating a purchase order, THE WMS SHALL allow addition of multiple products with quantities using repeater fields
3. THE WMS SHALL generate unique purchase order numbers automatically
4. THE WMS SHALL track purchase order status (draft, sent, partially received, completed)
5. THE WMS SHALL link purchase orders to subsequent inbound operations

### Requirement 6: Inbound Operations Processing

**User Story:** As a Warehouse Operator, I want to process incoming goods based on purchase orders, so that I can update inventory accurately when goods arrive.

#### Acceptance Criteria

1. WHEN processing inbound operations, THE WMS SHALL allow selection of an existing purchase order
2. WHEN a purchase order is selected, THE WMS SHALL automatically load the product list from that purchase order
3. THE WMS SHALL allow input of received quantity for each product which may differ from ordered quantity
4. WHEN inbound operation is confirmed, THE WMS SHALL increase product stock quantities automatically through stock movement records
5. THE WMS SHALL generate unique inbound operation numbers automatically
6. THE WMS SHALL update purchase order status based on received quantities

### Requirement 7: Sales Order Creation and Management

**User Story:** As a Sales user, I want to create and manage sales orders, so that I can record customer orders systematically.

#### Acceptance Criteria

1. THE WMS SHALL allow creation of sales orders with customer selection
2. WHEN creating a sales order, THE WMS SHALL allow addition of multiple products with quantities
3. WHEN adding products to sales order, THE WMS SHALL display current stock availability
4. THE WMS SHALL generate unique sales order numbers automatically
5. THE WMS SHALL track sales order status (draft, approved, partially fulfilled, completed)
6. THE WMS SHALL prevent sales order approval when product stock is insufficient

### Requirement 8: Outbound Operations Processing

**User Story:** As a Warehouse Operator, I want to process outgoing goods based on approved sales orders, so that I can fulfill customer orders and update inventory accurately.

#### Acceptance Criteria

1. WHEN processing outbound operations, THE WMS SHALL allow selection of an approved sales order
2. WHEN a sales order is selected, THE WMS SHALL automatically load the product list from that sales order
3. THE WMS SHALL allow warehouse operators to confirm product preparation
4. WHEN outbound operation is confirmed, THE WMS SHALL decrease product stock quantities automatically through stock movement records
5. THE WMS SHALL generate unique outbound operation numbers automatically
6. THE WMS SHALL update sales order status based on fulfilled quantities

### Requirement 9: Real-time Inventory Viewing

**User Story:** As a Warehouse Admin, I want to view real-time stock levels for all products, so that I can monitor inventory status at any time.

#### Acceptance Criteria

1. THE WMS SHALL display current stock quantity for all products calculated from stock movement records
2. THE WMS SHALL provide filter capabilities by product category
3. THE WMS SHALL provide search capability by SKU code or product name
4. THE WMS SHALL highlight products with stock below minimum threshold
5. THE WMS SHALL calculate stock quantity as sum of all stock movements rather than storing static values

### Requirement 10: Stock Opname (Physical Count Adjustment)

**User Story:** As a Warehouse Admin, I want to perform stock opname to reconcile physical stock with system records, so that I can maintain inventory accuracy.

#### Acceptance Criteria

1. THE WMS SHALL allow creation of stock opname sessions
2. WHEN performing stock opname, THE WMS SHALL allow selection of products to count
3. THE WMS SHALL allow input of physical stock quantity for each product
4. WHEN physical quantity is entered, THE WMS SHALL calculate variance (shortage or surplus) automatically
5. WHEN stock opname is confirmed, THE WMS SHALL create adjustment stock movement records to reconcile differences
6. THE WMS SHALL generate unique stock opname numbers automatically

### Requirement 11: Delivery Order Generation

**User Story:** As a Warehouse Operator, I want to generate delivery orders for confirmed outbound operations, so that I can provide proper documentation for deliveries.

#### Acceptance Criteria

1. WHEN outbound operation is confirmed, THE WMS SHALL allow creation of delivery order
2. THE WMS SHALL allow assignment of driver and vehicle to delivery order
3. THE WMS SHALL generate unique delivery order numbers automatically
4. THE WMS SHALL generate barcode or QR code for delivery order tracking
5. THE WMS SHALL allow printing of delivery order to PDF format

### Requirement 12: Invoice Generation

**User Story:** As an Accounting user, I want to generate invoices based on completed sales orders or delivered outbound operations, so that I can bill customers properly.

#### Acceptance Criteria

1. THE WMS SHALL allow creation of invoices based on sales orders or outbound operations
2. THE WMS SHALL generate unique invoice numbers automatically
3. THE WMS SHALL track invoice payment status (paid or unpaid)
4. THE WMS SHALL allow printing of invoices to PDF format
5. THE WMS SHALL allow sending invoices via email

### Requirement 13: Role-Based Access Control

**User Story:** As a Super Admin, I want to assign roles to users with specific permissions, so that I can control access to different system functions based on job responsibilities.

#### Acceptance Criteria

1. THE WMS SHALL support Super Admin role with full system access
2. THE WMS SHALL support Warehouse Admin role with access to master data management, purchase orders, and approval functions
3. THE WMS SHALL support Warehouse Operator role with access limited to inbound and outbound operation processing
4. THE WMS SHALL support Sales role with access limited to sales order creation and customer data viewing
5. THE WMS SHALL support Accounting role with access limited to invoice management and reports
6. THE WMS SHALL prevent users from accessing functions outside their assigned role permissions

### Requirement 14: Dashboard Statistics Display

**User Story:** As a Warehouse Admin, I want to view key statistics on the dashboard, so that I can monitor warehouse operations at a glance.

#### Acceptance Criteria

1. THE WMS SHALL display total stock value in currency on the dashboard
2. THE WMS SHALL display total customer count on the dashboard
3. THE WMS SHALL display today's inbound operation count on the dashboard
4. THE WMS SHALL display today's outbound operation count on the dashboard
5. THE WMS SHALL update dashboard statistics in real-time

### Requirement 15: Dashboard Charts Display

**User Story:** As a Warehouse Admin, I want to view trend charts on the dashboard, so that I can analyze patterns in warehouse operations.

#### Acceptance Criteria

1. THE WMS SHALL display sales chart for the last 7 days on the dashboard
2. THE WMS SHALL display inbound versus outbound comparison chart on the dashboard
3. THE WMS SHALL update dashboard charts daily

### Requirement 16: Dashboard Quick Information Tables

**User Story:** As a Warehouse Admin, I want to view quick information tables on the dashboard, so that I can identify items requiring attention.

#### Acceptance Criteria

1. THE WMS SHALL display top 5 best-selling products table on the dashboard
2. THE WMS SHALL display products with low stock (below minimum threshold) table on the dashboard
3. THE WMS SHALL display recent activity log of inbound and outbound operations on the dashboard

### Requirement 17: Stock Card Report Generation

**User Story:** As a Warehouse Admin, I want to generate stock card reports for products, so that I can trace all stock movements and verify final stock quantities.

#### Acceptance Criteria

1. THE WMS SHALL generate stock card reports showing all stock movements for a selected product
2. THE WMS SHALL display movement type (inbound, outbound, or adjustment) for each stock movement record
3. THE WMS SHALL display running balance after each movement in stock card report
4. THE WMS SHALL allow filtering stock card by date range
5. THE WMS SHALL calculate final stock quantity as sum of all movements in stock card

### Requirement 18: Low Stock Report Generation

**User Story:** As a Warehouse Admin, I want to generate low stock reports, so that I can identify products requiring replenishment.

#### Acceptance Criteria

1. THE WMS SHALL generate reports listing all products with current stock below minimum threshold
2. THE WMS SHALL display current stock quantity and minimum threshold for each product in low stock report
3. THE WMS SHALL allow export of low stock report to PDF or Excel format

### Requirement 19: Stock Valuation Report Generation

**User Story:** As an Accounting user, I want to generate stock valuation reports, so that I can determine total inventory value for financial reporting.

#### Acceptance Criteria

1. THE WMS SHALL calculate stock valuation by multiplying current stock quantity with purchase price for each product
2. THE WMS SHALL display total inventory value across all products
3. THE WMS SHALL allow filtering stock valuation report by product category
4. THE WMS SHALL allow export of stock valuation report to PDF or Excel format

### Requirement 20: Sales Report Generation

**User Story:** As a Warehouse Admin, I want to generate sales reports with flexible filtering, so that I can analyze sales performance across different dimensions.

#### Acceptance Criteria

1. THE WMS SHALL generate sales reports based on outbound operations
2. THE WMS SHALL allow filtering sales reports by date range (daily, monthly, yearly)
3. THE WMS SHALL allow filtering sales reports by specific product
4. THE WMS SHALL allow filtering sales reports by specific customer
5. THE WMS SHALL allow filtering sales reports by sales user
6. THE WMS SHALL display total sales quantity and value in sales reports
7. THE WMS SHALL allow export of sales reports to PDF or Excel format

### Requirement 21: Purchase Report Generation

**User Story:** As a Warehouse Admin, I want to generate purchase reports with flexible filtering, so that I can analyze purchasing patterns and supplier performance.

#### Acceptance Criteria

1. THE WMS SHALL generate purchase reports based on inbound operations
2. THE WMS SHALL allow filtering purchase reports by date range (daily, monthly, yearly)
3. THE WMS SHALL allow filtering purchase reports by specific product
4. THE WMS SHALL allow filtering purchase reports by specific supplier
5. THE WMS SHALL display total purchase quantity and value in purchase reports
6. THE WMS SHALL allow export of purchase reports to PDF or Excel format
