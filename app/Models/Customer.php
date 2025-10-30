<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'address',
        'email',
        'phone',
        'type',
    ];

    /**
     * Get the sales orders for the customer.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
