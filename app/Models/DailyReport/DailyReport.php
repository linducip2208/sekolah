<?php

namespace App\Models\DailyReport;

use App\Models\SchoolModel;

class DailyReport extends SchoolModel
{
    protected $table = 'daily_reports';

    protected $fillable = [
        'school_id','student_id','report_date',
        'attendance','subjects_today','homework_due','canteen_summary',
        'clinic_visit','discipline_events','wellness_checkin','teacher_notes','sent_at',
    ];

    protected $casts = [
        'report_date'       => 'date',
        'attendance'        => 'array',
        'subjects_today'    => 'array',
        'homework_due'      => 'array',
        'canteen_summary'   => 'array',
        'clinic_visit'      => 'array',
        'discipline_events' => 'array',
        'wellness_checkin'  => 'array',
        'teacher_notes'     => 'array',
        'sent_at'           => 'datetime',
    ];
}
