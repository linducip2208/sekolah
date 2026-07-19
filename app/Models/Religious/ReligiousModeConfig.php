<?php

namespace App\Models\Religious;

use App\Models\SchoolModel;

class ReligiousModeConfig extends SchoolModel
{
    protected $table = 'religious_mode_config';

    protected $fillable = [
        'school_id','enabled','religion','institution_type',
        'hijri_holidays','use_hijri_calendar','prayer_times_config',
    ];

    protected $casts = [
        'enabled'             => 'boolean',
        'use_hijri_calendar'  => 'boolean',
        'hijri_holidays'      => 'array',
        'prayer_times_config' => 'array',
    ];
}
