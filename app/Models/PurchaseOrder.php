<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'notes',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot method to generate unique PO number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->po_number)) {
                $model->po_number = static::generateUniqueNumber('PO', 'po_number');
            }
        });
    }

    /**
     * Generate unique document number with duplicate prevention.
     * Uses database locking to prevent race conditions.
     *
     * @param string $prefix Document prefix (e.g., 'SO', 'PO')
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
     * Get the supplier that owns the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the inbound operations for the purchase order.
     */
    public function inboundOperations(): HasMany
    {
        return $this->hasMany(InboundOperation::class);
    }

    /**
     * Get the user who created the purchase order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
