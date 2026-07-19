<?php

namespace App\Models\Curriculum;

use App\Models\SchoolModel;

class CurriculumCompetency extends SchoolModel
{
    protected $table = 'curriculum_competencies';

    protected $fillable = [
        'school_id','curriculum_framework_id','subject_id','class_room_id',
        'code','description','level_type','parent_id','indicators',
    ];

    protected $casts = ['indicators' => 'array'];
}
