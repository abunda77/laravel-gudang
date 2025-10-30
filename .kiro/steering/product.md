# Product Overview

This is a Warehouse Management System (WMS) built for managing inventory operations, purchase orders, sales orders, and logistics.

## Core Functionality

-   **Inventory Management**: Track products, categories, variants, and stock levels
-   **Purchase Management**: Create and manage purchase orders from suppliers
-   **Sales Management**: Process sales orders and customer transactions
-   **Warehouse Operations**: Handle inbound/outbound operations with stock movements
-   **Logistics**: Manage delivery orders with driver and vehicle assignments
-   **Financial**: Generate invoices and track payments
-   **Reporting**: Stock cards, low stock alerts, sales reports, and stock valuation

## Key Business Rules

-   **Stock Movement Pattern**: All inventory changes are recorded as movements, not direct updates
-   **Event Sourcing**: Stock quantities are calculated from the sum of all movements
-   **Audit Trail**: Complete traceability of all transactions with user tracking
-   **Role-Based Access**: Strict permission system for different user roles

## Localization

-   Primary locale: Indonesian (id)
-   Fallback locale: Indonesian (id)
-   Currency: Indonesian Rupiah (Rp)
