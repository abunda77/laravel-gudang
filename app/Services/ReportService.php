<?php

namespace App\Services;

use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Generate stock card report for a product showing all movements with running balance.
     *
     * @param Product $product
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     * @throws \RuntimeException
     */
    public function getStockCard(
        Product $product,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        try {
            $query = StockMovement::where('product_id', $product->id)
                ->with(['reference', 'creator'])
                ->orderBy('created_at')
                ->orderBy('id');

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $movements = $query->get();

            // Calculate running balance
            $balance = 0;
            
            // If there's a start date, calculate the opening balance
            if ($startDate) {
                $balance = (int) StockMovement::where('product_id', $product->id)
                    ->where('created_at', '<', $startDate)
                    ->sum('quantity');
            }

            Log::info('Stock card report generated successfully', [
                'product_id' => $product->id,
                'movements_count' => $movements->count(),
            ]);

            return $movements->map(function ($movement) use (&$balance) {
                $balance += $movement->quantity;
                $movement->running_balance = $balance;
                return $movement;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to generate stock card report', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate stock card report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get all products with stock below minimum threshold.
     *
     * @return Collection
     * @throws \RuntimeException
     */
    public function getLowStockProducts(): Collection
    {
        try {
            $products = Product::with(['category'])
                ->get()
                ->filter(function ($product) {
                    return $product->isLowStock();
                })
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category' => $product->category?->name,
                        'current_stock' => $product->getCurrentStock(),
                        'minimum_stock' => $product->minimum_stock,
                        'shortage' => $product->minimum_stock - $product->getCurrentStock(),
                    ];
                })
                ->values();

            Log::info('Low stock report generated successfully', [
                'low_stock_count' => $products->count(),
            ]);

            return $products;
        } catch (\Throwable $e) {
            Log::error('Failed to generate low stock report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate low stock report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate stock valuation report calculating total inventory value.
     *
     * @param int|null $categoryId Optional category filter
     * @return array
     * @throws \RuntimeException
     */
    public function getStockValuationReport(?int $categoryId = null): array
    {
        try {
            $query = Product::with(['category']);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $products = $query->get();

            $items = $products->map(function ($product) {
                $currentStock = $product->getCurrentStock();
                $stockValue = $currentStock * $product->purchase_price;

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'current_stock' => $currentStock,
                    'purchase_price' => $product->purchase_price,
                    'stock_value' => $stockValue,
                ];
            });

            $totalValue = $items->sum('stock_value');

            Log::info('Stock valuation report generated successfully', [
                'category_id' => $categoryId,
                'total_products' => $items->count(),
                'total_value' => $totalValue,
            ]);

            return [
                'items' => $items,
                'total_value' => $totalValue,
                'total_products' => $items->count(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to generate stock valuation report', [
                'category_id' => $categoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate stock valuation report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate sales report based on outbound operations with flexible filtering.
     *
     * @param array $filters Available filters: start_date, end_date, product_id, customer_id, sales_user_id
     * @return array
     * @throws \RuntimeException
     */
    public function getSalesReport(array $filters = []): array
    {
        try {
        $query = OutboundOperation::with([
            'items.product',
            'salesOrder.customer',
            'salesOrder.salesUser',
            'preparer'
        ]);

        // Date range filter
        if (isset($filters['start_date'])) {
            $query->where('shipped_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('shipped_date', '<=', $filters['end_date']);
        }

        // Product filter
        if (isset($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        // Customer filter
        if (isset($filters['customer_id'])) {
            $query->whereHas('salesOrder', function ($q) use ($filters) {
                $q->where('customer_id', $filters['customer_id']);
            });
        }

        // Sales user filter
        if (isset($filters['sales_user_id'])) {
            $query->whereHas('salesOrder', function ($q) use ($filters) {
                $q->where('sales_user_id', $filters['sales_user_id']);
            });
        }

        $outboundOperations = $query->get();

        // Process the data
        $items = collect();
        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($outboundOperations as $outbound) {
            foreach ($outbound->items as $item) {
                // Apply product filter at item level if specified
                if (isset($filters['product_id']) && $item->product_id != $filters['product_id']) {
                    continue;
                }

                $itemValue = $item->shipped_quantity * $item->product->selling_price;
                $totalQuantity += $item->shipped_quantity;
                $totalValue += $itemValue;

                $items->push([
                    'date' => $outbound->shipped_date,
                    'outbound_number' => $outbound->outbound_number,
                    'so_number' => $outbound->salesOrder->so_number,
                    'customer_name' => $outbound->salesOrder->customer->name,
                    'sales_user' => $outbound->salesOrder->salesUser?->name,
                    'product_sku' => $item->product->sku,
                    'product_name' => $item->product->name,
                    'quantity' => $item->shipped_quantity,
                    'unit_price' => $item->product->selling_price,
                    'total_value' => $itemValue,
                ]);
            }
        }

            Log::info('Sales report generated successfully', [
                'filters' => $filters,
                'total_transactions' => $outboundOperations->count(),
                'total_value' => $totalValue,
            ]);

            return [
                'items' => $items,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'total_transactions' => $outboundOperations->count(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to generate sales report', [
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate sales report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate purchase report based on inbound operations with flexible filtering.
     *
     * @param array $filters Available filters: start_date, end_date, product_id, supplier_id
     * @return array
     * @throws \RuntimeException
     */
    public function getPurchaseReport(array $filters = []): array
    {
        try {
        $query = InboundOperation::with([
            'items.product',
            'purchaseOrder.supplier',
            'receiver'
        ]);

        // Date range filter
        if (isset($filters['start_date'])) {
            $query->where('received_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('received_date', '<=', $filters['end_date']);
        }

        // Product filter
        if (isset($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        // Supplier filter
        if (isset($filters['supplier_id'])) {
            $query->whereHas('purchaseOrder', function ($q) use ($filters) {
                $q->where('supplier_id', $filters['supplier_id']);
            });
        }

        $inboundOperations = $query->get();

        // Process the data
        $items = collect();
        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($inboundOperations as $inbound) {
            foreach ($inbound->items as $item) {
                // Apply product filter at item level if specified
                if (isset($filters['product_id']) && $item->product_id != $filters['product_id']) {
                    continue;
                }

                $itemValue = $item->received_quantity * $item->product->purchase_price;
                $totalQuantity += $item->received_quantity;
                $totalValue += $itemValue;

                $items->push([
                    'date' => $inbound->received_date,
                    'inbound_number' => $inbound->inbound_number,
                    'po_number' => $inbound->purchaseOrder->po_number,
                    'supplier_name' => $inbound->purchaseOrder->supplier->name,
                    'product_sku' => $item->product->sku,
                    'product_name' => $item->product->name,
                    'ordered_quantity' => $item->ordered_quantity,
                    'received_quantity' => $item->received_quantity,
                    'unit_price' => $item->product->purchase_price,
                    'total_value' => $itemValue,
                ]);
            }
        }

            Log::info('Purchase report generated successfully', [
                'filters' => $filters,
                'total_transactions' => $inboundOperations->count(),
                'total_value' => $totalValue,
            ]);

            return [
                'items' => $items,
                'total_quantity' => $totalQuantity,
                'total_value' => $totalValue,
                'total_transactions' => $inboundOperations->count(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to generate purchase report', [
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate purchase report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate product report with stock and valuation information.
     *
     * @param array $filters Available filters: category_id, low_stock_only
     * @return array
     * @throws \RuntimeException
     */
    public function getProductReport(array $filters = []): array
    {
        try {
            $query = Product::with(['category']);

            // Category filter
            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            $products = $query->get();

            // Filter for low stock if requested
            if (isset($filters['low_stock_only']) && $filters['low_stock_only']) {
                $products = $products->filter(function ($product) {
                    return $product->isLowStock();
                });
            }

            $items = $products->map(function ($product) {
                $currentStock = $product->getCurrentStock();
                $purchaseValue = $currentStock * $product->purchase_price;
                $sellingValue = $currentStock * $product->selling_price;
                $potentialProfit = $sellingValue - $purchaseValue;

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'unit' => $product->unit,
                    'current_stock' => $currentStock,
                    'minimum_stock' => $product->minimum_stock,
                    'purchase_price' => $product->purchase_price,
                    'selling_price' => $product->selling_price,
                    'purchase_value' => $purchaseValue,
                    'selling_value' => $sellingValue,
                    'potential_profit' => $potentialProfit,
                    'rack_location' => $product->rack_location,
                    'is_low_stock' => $product->isLowStock(),
                ];
            });

            $totalPurchaseValue = $items->sum('purchase_value');
            $totalSellingValue = $items->sum('selling_value');
            $totalPotentialProfit = $items->sum('potential_profit');
            $lowStockCount = $items->where('is_low_stock', true)->count();

            Log::info('Product report generated successfully', [
                'filters' => $filters,
                'total_products' => $items->count(),
                'low_stock_count' => $lowStockCount,
                'total_purchase_value' => $totalPurchaseValue,
            ]);

            return [
                'items' => $items,
                'total_products' => $items->count(),
                'low_stock_count' => $lowStockCount,
                'total_purchase_value' => $totalPurchaseValue,
                'total_selling_value' => $totalSellingValue,
                'total_potential_profit' => $totalPotentialProfit,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to generate product report', [
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate product report: ' . $e->getMessage(), 0, $e);
        }
    }
}
