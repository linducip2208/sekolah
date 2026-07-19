<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;

class TimetableBreak extends SchoolModel
{
    protected $table = 'timetable_breaks';

    protected $fillable = [
        'school_id', 'name', 'day_of_week', 'start_time', 'end_time',
    ];
}
