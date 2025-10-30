<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'vehicle_type',
        'ownership_status',
    ];

    /**
     * Get the delivery orders for the vehicle.
     */
    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }
}
