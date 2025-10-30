<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class InboundOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_number',
        'purchase_order_id',
        'received_date',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'received_date' => 'datetime',
    ];

    /**
     * Boot method to generate unique inbound number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->inbound_number)) {
                $model->inbound_number = static::generateUniqueNumber('IN', 'inbound_number');
            }
        });
    }

    /**
     * Generate unique document number with duplicate prevention.
     * Uses database locking to prevent race conditions.
     *
     * @param string $prefix Document prefix (e.g., 'IN', 'OUT')
     * @param string $column Column name for the document number
     * @return string
     */
    protected static function generateUniqueNumber(string $prefix, string $column): string
    {
        return \DB::transaction(function () use ($prefix, $column) {
            $date = now()->format('Ymd');
            $maxAttempts = 10;
            $attempt = 0;

            do {
                // Get the last number for today with row locking
                $lastRecord = static::whereDate('created_at', now())
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $sequence = $lastRecord && isset($lastRecord->$column)
                    ? intval(substr($lastRecord->$column, -4)) + 1
                    : 1;

                $documentNumber = sprintf('%s-%s-%04d', $prefix, $date, $sequence);

                // Check if this number already exists
                $exists = static::where($column, $documentNumber)->exists();

                if (!$exists) {
                    return $documentNumber;
                }

                $attempt++;
            } while ($attempt < $maxAttempts);

            // If we couldn't generate a unique number after max attempts, throw exception
            throw new \RuntimeException("Failed to generate unique {$prefix} number after {$maxAttempts} attempts");
        });
    }

    /**
     * Get the purchase order that owns the inbound operation.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the items for the inbound operation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InboundOperationItem::class);
    }

    /**
     * Get all stock movements for this inbound operation.
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * Get the user who received the inbound operation.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
