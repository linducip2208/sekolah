<?php

namespace App\Models\Curriculum;

use App\Models\SchoolModel;

class CompetencyAssessment extends SchoolModel
{
    protected $table = 'competency_assessments';

    protected $fillable = [
        'school_id','student_id','curriculum_competency_id',
        'mastery_level','assessed_by','assessed_at','evidence',
    ];

    protected $casts = ['assessed_at' => 'date'];
}
