<?php

namespace App\Models\Career;

use App\Models\SchoolModel;

class CareerAssessment extends SchoolModel
{
    protected $table = 'career_assessments';

    protected $fillable = ['school_id','student_id','test_type','responses','result','taken_at'];

    protected $casts = [
        'responses' => 'array',
        'result'    => 'array',
        'taken_at'  => 'date',
    ];
}
