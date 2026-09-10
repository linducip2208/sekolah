<?php

namespace App\Models\Facilities;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;

class Vehicle extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'school_id', 'registration_no', 'make_model', 'capacity',
        'driver_name', 'driver_phone',
    ];
}
