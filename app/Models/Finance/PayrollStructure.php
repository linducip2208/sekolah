<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;

class PayrollStructure extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'type', 'calculation', 'value', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
