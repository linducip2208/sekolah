<?php

namespace App\Models\Emergency;

use App\Models\SchoolModel;

class EmergencyTemplate extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'alert_type',
        'title_template', 'message_template', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
