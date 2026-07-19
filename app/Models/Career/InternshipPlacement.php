<?php

namespace App\Models\Career;

use App\Models\SchoolModel;

class InternshipPlacement extends SchoolModel
{
    protected $table = 'internship_placements';

    protected $fillable = [
        'school_id','student_id','company_name','position','mentor_name','mentor_phone',
        'start_date','end_date','status','daily_logs','evaluation','certificate_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'daily_logs' => 'array',
        'evaluation' => 'array',
    ];
}
