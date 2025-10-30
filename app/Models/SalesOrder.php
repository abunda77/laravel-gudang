<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'customer_id',
        'order_date',
        'status',
        'notes',
        'total_amount',
        'sales_user_id',
    ];

    protected $casts = [
        'status' => SalesOrderStatus::class,
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot method to generate unique SO number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->so_number)) {
                $model->so_number = static::generateUniqueNumber('SO', 'so_number');
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
     * Get the customer that owns the sales order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items for the sales order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get the outbound operations for the sales order.
     */
    public function outboundOperations(): HasMany
    {
        return $this->hasMany(OutboundOperation::class);
    }

    /**
     * Get the invoices for the sales order.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the sales user who created the sales order.
     */
    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    /**
     * Check if the sales order can be approved.
     * Sales order can be approved if it's in draft status and has sufficient stock.
     */
    public function canBeApproved(): bool
    {
        // Check if status is draft
        if ($this->status !== SalesOrderStatus::DRAFT) {
            return false;
        }

        // Check if there are items
        if ($this->items()->count() === 0) {
            return false;
        }

        // Check stock availability
        $unavailable = $this->checkStockAvailability();

        return empty($unavailable);
    }

    /**
     * Check stock availability for all items in the sales order.
     * Returns array of items with insufficient stock.
     */
    public function checkStockAvailability(): array
    {
        $unavailable = [];

        foreach ($this->items as $item) {
            $currentStock = $item->product->getCurrentStock();

            if ($currentStock < $item->quantity) {
                $unavailable[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'required' => $item->quantity,
                    'available' => $currentStock,
                    'shortage' => $item->quantity - $currentStock,
                ];
            }
        }

        return $unavailable;
    }

    /**
     * Validate stock availability before approval.
     * Throws exception if stock is insufficient.
     *
     * @return void
     * @throws InsufficientStockException
     */
    public function validateStockAvailability(): void
    {
        $unavailable = $this->checkStockAvailability();

        if (!empty($unavailable)) {
            throw new InsufficientStockException($unavailable);
        }
    }

    /**
     * Approve the sales order.
     * Validates stock availability and status before approval.
     *
     * @return void
     * @throws InsufficientStockException
     * @throws InvalidStatusTransitionException
     */
    public function approve(): void
    {
        // Check if current status allows approval
        if ($this->status !== SalesOrderStatus::DRAFT) {
            throw new InvalidStatusTransitionException(
                $this->status->value,
                SalesOrderStatus::APPROVED->value,
                'SalesOrder'
            );
        }

        // Validate stock availability
        $this->validateStockAvailability();

        // Update status to approved
        $this->status = SalesOrderStatus::APPROVED;
        $this->save();
    }
}
