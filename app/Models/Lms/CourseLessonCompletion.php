<?php

namespace App\Models\Lms;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLessonCompletion extends SchoolModel
{
    protected $table = 'course_lesson_completions';

    protected $fillable = [
        'school_id', 'enrollment_id', 'course_lesson_id', 'student_id', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
