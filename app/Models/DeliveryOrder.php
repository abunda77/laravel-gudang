<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'do_number',
        'outbound_operation_id',
        'driver_id',
        'vehicle_id',
        'delivery_date',
        'recipient_name',
        'notes',
        'barcode',
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
    ];

    /**
     * Get the outbound operation for this delivery order.
     */
    public function outboundOperation(): BelongsTo
    {
        return $this->belongsTo(OutboundOperation::class);
    }

    /**
     * Get the driver assigned to this delivery order.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the vehicle assigned to this delivery order.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryOrder) {
            if (empty($deliveryOrder->do_number)) {
                $deliveryOrder->do_number = static::generateUniqueNumber('DO', 'do_number');
            }
            
            if (empty($deliveryOrder->barcode)) {
                $documentService = app(\App\Services\DocumentGenerationService::class);
                $deliveryOrder->barcode = $documentService->generateBarcode($deliveryOrder->do_number);
            }
        });
    }

    /**
     * Generate unique document number with duplicate prevention.
     * Uses database locking to prevent race conditions.
     *
     * @param string $prefix Document prefix (e.g., 'DO', 'INV')
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
}
