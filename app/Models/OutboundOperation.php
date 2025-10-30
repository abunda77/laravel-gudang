<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OutboundOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_number',
        'sales_order_id',
        'shipped_date',
        'notes',
        'prepared_by',
    ];

    protected $casts = [
        'shipped_date' => 'datetime',
    ];

    /**
     * Boot method to generate unique outbound number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->outbound_number)) {
                $model->outbound_number = static::generateUniqueNumber('OUT', 'outbound_number');
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
     * Get the sales order that owns the outbound operation.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the items for the outbound operation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OutboundOperationItem::class);
    }

    /**
     * Get all stock movements for this outbound operation.
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * Get the delivery order for this outbound operation.
     */
    public function deliveryOrder(): HasOne
    {
        return $this->hasOne(DeliveryOrder::class);
    }

    /**
     * Get the user who prepared the outbound operation.
     */
    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}
