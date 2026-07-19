<?php

namespace App\Models\LiveClass;

use App\Models\SchoolModel;

class LiveClassAttendance extends SchoolModel
{
    protected $table = 'live_class_attendances';

    protected $fillable = [
        'school_id','live_class_session_id','student_id',
        'joined_at','left_at','total_minutes',
    ];

    protected $casts = [
        'joined_at'     => 'datetime',
        'left_at'       => 'datetime',
        'total_minutes' => 'integer',
    ];
}
