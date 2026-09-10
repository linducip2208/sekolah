<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;

class PayrollStructure extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'school_id', 'name', 'type', 'calculation', 'value', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
