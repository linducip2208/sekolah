<?php

namespace App\Models\Wellness;

use App\Models\SchoolModel;

class WellnessCheckin extends SchoolModel
{
    protected $table = 'wellness_checkins';

    protected $fillable = [
        'school_id','student_id','checkin_date','mood_score',
        'feeling_tags','note','flagged_for_review',
    ];

    protected $casts = [
        'checkin_date'       => 'date',
        'mood_score'         => 'integer',
        'feeling_tags'       => 'array',
        'flagged_for_review' => 'boolean',
    ];
}
