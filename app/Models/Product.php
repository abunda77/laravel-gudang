<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'category_id',
        'minimum_stock',
        'rack_location',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'minimum_stock' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the purchase order items for the product.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the sales order items for the product.
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get current stock quantity from sum of all stock movements.
     * Uses cached value from StockMovementService for better performance.
     * 
     * For products with variants, this returns the sum of all variant stocks.
     * For products without variants, this returns the product's own stock.
     */
    public function getCurrentStock(): int
    {
        $stockService = app(\App\Services\StockMovementService::class);
        
        // If product has variants, sum all variant stocks
        if ($this->variants()->exists()) {
            return $this->variants->sum(function ($variant) use ($stockService) {
                return $stockService->getCurrentStockForVariant($variant);
            });
        }
        
        // Otherwise, return product's own stock
        return $stockService->getCurrentStock($this);
    }

    /**
     * Get total stock value (current stock * purchase price).
     */
    public function getStockValue(): float
    {
        return $this->getCurrentStock() * $this->purchase_price;
    }

    /**
     * Check if product stock is below minimum threshold.
     */
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() < $this->minimum_stock;
    }
}
