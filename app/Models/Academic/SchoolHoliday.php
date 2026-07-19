<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class SchoolHoliday extends SchoolModel
{
    public $timestamps = true;
    public $dates      = [];

    protected $fillable = [
        'school_id', 'title', 'date', 'end_date', 'type',
    ];

    protected $casts = [
        'date'     => 'date',
        'end_date' => 'date',
    ];
}
