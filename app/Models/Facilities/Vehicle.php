<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;

class Vehicle extends SchoolModel
{
    protected $fillable = [
        'school_id', 'registration_no', 'make_model', 'capacity',
        'driver_name', 'driver_phone',
    ];
}
