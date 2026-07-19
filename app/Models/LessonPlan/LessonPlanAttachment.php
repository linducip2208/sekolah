<?php

namespace App\Models\LessonPlan;

use App\Models\SchoolModel;

class LessonPlanAttachment extends SchoolModel
{
    protected $table = 'lesson_plan_attachments';

    protected $fillable = [
        'school_id','lesson_plan_id','file_path','file_name','mime','size_bytes',
    ];

    protected $casts = ['size_bytes' => 'integer'];
}
