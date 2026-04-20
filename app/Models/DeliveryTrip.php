<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryTrip extends Model
{
    protected $fillable = [
        'trip_number',
        'trip_date',
        'warehouse_id',
        'driver_user_id',
        'vehicle_id',
        'status',
        'total_shipments',
        'total_estimated_distance_km',
        'notes',
        'created_by',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function tripShipments(): HasMany
    {
        return $this->hasMany(DeliveryTripShipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'delivery_trip_shipments')
            ->withPivot(['route_order', 'distance_from_previous_km'])
            ->withTimestamps()
            ->orderBy('delivery_trip_shipments.route_order');
    }
}