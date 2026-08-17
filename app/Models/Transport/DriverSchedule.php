<?php

namespace App\Models\Transport;

use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverSchedule extends SchoolModel
{
    protected $table = 'driver_schedules';

    protected $fillable = [
        'school_id', 'transport_route_id', 'vehicle_id', 'driver_name', 'date', 'shift', 'note',
    ];

    protected $casts = ['date' => 'date'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
