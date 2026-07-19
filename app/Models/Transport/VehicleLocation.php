<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;

class VehicleLocation extends Model
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
}
