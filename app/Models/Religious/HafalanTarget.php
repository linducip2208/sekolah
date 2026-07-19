<?php

namespace App\Models\Religious;

use App\Models\SchoolModel;

class HafalanTarget extends SchoolModel
{
    protected $table = 'hafalan_targets';

    protected $fillable = [
        'school_id','class_section_id','name','target_ranges','start_date','deadline',
    ];

    protected $casts = [
        'target_ranges' => 'array',
        'start_date'    => 'date',
        'deadline'      => 'date',
    ];
}
