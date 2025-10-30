<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundOperationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_operation_id',
        'product_id',
        'shipped_quantity',
    ];

    protected $casts = [
        'shipped_quantity' => 'integer',
    ];

    /**
     * Get the outbound operation that owns the item.
     */
    public function outboundOperation(): BelongsTo
    {
        return $this->belongsTo(OutboundOperation::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
