<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundOperationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_operation_id',
        'product_id',
        'ordered_quantity',
        'received_quantity',
    ];

    protected $casts = [
        'ordered_quantity' => 'integer',
        'received_quantity' => 'integer',
    ];

    /**
     * Get the inbound operation that owns the item.
     */
    public function inboundOperation(): BelongsTo
    {
        return $this->belongsTo(InboundOperation::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
