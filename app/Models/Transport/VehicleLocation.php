<?php

namespace App\Models\Transport;

use App\Models\Facilities\Vehicle;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends SchoolModel
{
    protected $table = 'vehicle_locations';
    public $timestamps = false;

    protected $fillable = [
        'school_id','vehicle_id','lat','lng','speed_kmh','heading_deg','recorded_at',
    ];

    protected $casts = [
        'lat'         => 'decimal:7',
        'lng'         => 'decimal:7',
        'speed_kmh'   => 'decimal:2',
        'heading_deg' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
