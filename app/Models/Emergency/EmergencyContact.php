<?php

namespace App\Models\Emergency;

use App\Models\SchoolModel;

class EmergencyContact extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'phone', 'email',
        'contact_type', 'is_active', 'priority_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
