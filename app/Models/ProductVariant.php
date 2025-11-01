<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock movements for the variant.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get current stock quantity for this variant from sum of all stock movements.
     * Uses cached value from StockMovementService for better performance.
     */
    public function getCurrentStock(): int
    {
        $stockService = app(\App\Services\StockMovementService::class);
        return $stockService->getCurrentStockForVariant($this);
    }

    /**
     * Get total stock value for this variant (current stock * parent product purchase price).
     */
    public function getStockValue(): float
    {
        return $this->getCurrentStock() * $this->product->purchase_price;
    }

    /**
     * Check if variant stock is below parent product's minimum threshold.
     */
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() < $this->product->minimum_stock;
    }
}
