<?php

namespace App\Models\Discipline;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;

class DisciplineCategory extends SchoolModel
{
    use AuditableModel;

    protected $table = 'discipline_categories';

    protected $fillable = [
        'school_id', 'name', 'type', 'point_value', 'description',
        'auto_sanction', 'sanction_thresholds',
    ];

    protected $casts = [
        'point_value' => 'integer',
        'auto_sanction' => 'boolean',
        'sanction_thresholds' => 'array',
    ];
}
