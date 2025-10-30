<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_stock',
        'physical_stock',
        'variance',
    ];

    protected $casts = [
        'system_stock' => 'integer',
        'physical_stock' => 'integer',
        'variance' => 'integer',
    ];

    /**
     * Get the stock opname that this item belongs to.
     */
    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    /**
     * Get the product for this stock opname item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
