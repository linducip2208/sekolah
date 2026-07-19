<?php

namespace App\Models\Transport;

use App\Models\SchoolModel;

class VehicleTrip extends SchoolModel
{
    protected $table = 'vehicle_trips';

    protected $fillable = [
        'school_id','vehicle_id','transport_route_id','direction',
        'started_at','ended_at','stops_completed','status',
    ];

    protected $casts = [
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
        'stops_completed' => 'array',
    ];
}
