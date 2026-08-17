<?php

namespace App\Models\Lms;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLesson extends SchoolModel
{
    protected $table = 'course_lessons';

    protected $fillable = [
        'school_id', 'course_module_id', 'title', 'content_html', 'video_url', 'duration_minutes', 'order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
}
