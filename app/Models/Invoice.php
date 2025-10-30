<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'payment_status',
        'total_amount',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_status' => InvoiceStatus::class,
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the sales order for this invoice.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateUniqueNumber('INV', 'invoice_number');
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
