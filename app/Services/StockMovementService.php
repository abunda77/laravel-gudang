<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\InboundOperation;
use App\Models\OutboundOperation;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class StockMovementService
{
    /**
     * Record inbound stock movement for received goods.
     *
     * @param InboundOperation $inbound
     * @param array $items Array of items with product_id and received_quantity
     * @return void
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function recordInbound(InboundOperation $inbound, array $items): void
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Items array cannot be empty');
        }

        try {
            DB::transaction(function () use ($inbound, $items) {
                foreach ($items as $item) {
                    $this->validateItem($item, ['product_id', 'received_quantity']);

                    if ($item['received_quantity'] <= 0) {
                        throw new InvalidArgumentException('Received quantity must be greater than zero');
                    }

                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['received_quantity'],
                        'type' => StockMovementType::INBOUND,
                        'reference_type' => InboundOperation::class,
                        'reference_id' => $inbound->id,
                        'notes' => $item['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    // Invalidate cache for this product
                    $this->invalidateStockCache($item['product_id']);
                }
            });

            Log::info('Inbound stock movement recorded successfully', [
                'inbound_operation_id' => $inbound->id,
                'items_count' => count($items),
            ]);
        } catch (InvalidArgumentException $e) {
            Log::error('Invalid argument in inbound operation', [
                'inbound_operation_id' => $inbound->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to record inbound stock movement', [
                'inbound_operation_id' => $inbound->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to record inbound stock movement: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Record outbound stock movement for shipped goods.
     *
     * @param OutboundOperation $outbound
     * @param array $items Array of items with product_id and shipped_quantity
     * @return void
     * @throws InsufficientStockException
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function recordOutbound(OutboundOperation $outbound, array $items): void
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Items array cannot be empty');
        }

        try {
            // First, check stock availability for all items
            $unavailableItems = [];
            
            foreach ($items as $item) {
                $this->validateItem($item, ['product_id', 'shipped_quantity']);

                if ($item['shipped_quantity'] <= 0) {
                    throw new InvalidArgumentException('Shipped quantity must be greater than zero');
                }

                $product = Product::findOrFail($item['product_id']);
                $currentStock = $this->getCurrentStock($product);

                if ($currentStock < $item['shipped_quantity']) {
                    $unavailableItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'required' => $item['shipped_quantity'],
                        'available' => $currentStock,
                        'shortage' => $item['shipped_quantity'] - $currentStock,
                    ];
                }
            }

            // If any items have insufficient stock, throw exception before transaction
            if (!empty($unavailableItems)) {
                throw new InsufficientStockException($unavailableItems);
            }

            // All items have sufficient stock, proceed with transaction
            DB::transaction(function () use ($outbound, $items) {
                foreach ($items as $item) {
                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'quantity' => -$item['shipped_quantity'], // Negative for outbound
                        'type' => StockMovementType::OUTBOUND,
                        'reference_type' => OutboundOperation::class,
                        'reference_id' => $outbound->id,
                        'notes' => $item['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    // Invalidate cache for this product
                    $this->invalidateStockCache($item['product_id']);
                }
            });

            Log::info('Outbound stock movement recorded successfully', [
                'outbound_operation_id' => $outbound->id,
                'items_count' => count($items),
            ]);
        } catch (InsufficientStockException $e) {
            Log::warning('Insufficient stock for outbound operation', [
                'outbound_operation_id' => $outbound->id,
                'unavailable_items' => $e->getUnavailableItems(),
            ]);
            throw $e;
        } catch (InvalidArgumentException $e) {
            Log::error('Invalid argument in outbound operation', [
                'outbound_operation_id' => $outbound->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to record outbound stock movement', [
                'outbound_operation_id' => $outbound->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to record outbound stock movement: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Record stock adjustment from stock opname (physical count).
     *
     * @param StockOpname $opname
     * @param Product $product
     * @param int $variance Positive for surplus, negative for shortage
     * @return void
     * @throws \Throwable
     */
    public function recordAdjustment(StockOpname $opname, Product $product, int $variance): void
    {
        if ($variance == 0) {
            return; // No adjustment needed
        }

        try {
            DB::transaction(function () use ($opname, $product, $variance) {
                $type = $variance > 0
                    ? StockMovementType::ADJUSTMENT_PLUS
                    : StockMovementType::ADJUSTMENT_MINUS;

                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => $variance,
                    'type' => $type,
                    'reference_type' => StockOpname::class,
                    'reference_id' => $opname->id,
                    'notes' => "Stock opname adjustment: " . ($variance > 0 ? "surplus" : "shortage") . " of " . abs($variance) . " units",
                    'created_by' => auth()->id(),
                ]);

                // Invalidate cache for this product
                $this->invalidateStockCache($product->id);
            });

            Log::info('Stock adjustment recorded successfully', [
                'stock_opname_id' => $opname->id,
                'product_id' => $product->id,
                'variance' => $variance,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to record stock adjustment', [
                'stock_opname_id' => $opname->id,
                'product_id' => $product->id,
                'variance' => $variance,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to record stock adjustment: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get current stock quantity for a product from sum of all stock movements.
     * Cached for 1 hour to improve performance.
     *
     * @param Product $product
     * @return int
     */
    public function getCurrentStock(Product $product): int
    {
        $cacheKey = "product_stock_{$product->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($product) {
            return (int) StockMovement::where('product_id', $product->id)
                ->sum('quantity');
        });
    }

    /**
     * Invalidate stock cache for a product.
     *
     * @param int $productId
     * @return void
     */
    private function invalidateStockCache(int $productId): void
    {
        Cache::forget("product_stock_{$productId}");
        
        // Also invalidate dashboard statistics cache
        Cache::forget('dashboard_total_stock_value');
        Cache::forget('dashboard_inbound_today_' . today()->format('Y-m-d'));
        Cache::forget('dashboard_outbound_today_' . today()->format('Y-m-d'));
    }

    /**
     * Check stock availability for multiple items (e.g., for sales order validation).
     *
     * @param array $items Array of items with product_id and quantity
     * @return array Array of unavailable items with details
     */
    public function checkAvailability(array $items): array
    {
        $unavailable = [];

        foreach ($items as $item) {
            $this->validateItem($item, ['product_id', 'quantity']);

            $product = Product::find($item['product_id']);

            if (!$product) {
                $unavailable[] = [
                    'product_id' => $item['product_id'],
                    'error' => 'Product not found',
                    'required' => $item['quantity'],
                    'available' => 0,
                ];
                continue;
            }

            $currentStock = $this->getCurrentStock($product);

            if ($currentStock < $item['quantity']) {
                $unavailable[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'required' => $item['quantity'],
                    'available' => $currentStock,
                    'shortage' => $item['quantity'] - $currentStock,
                ];
            }
        }

        return $unavailable;
    }

    /**
     * Validate that an item has required fields.
     *
     * @param array $item
     * @param array $requiredFields
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateItem(array $item, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($item[$field])) {
                throw new InvalidArgumentException("Item must have '{$field}' field");
            }
        }
    }
}
