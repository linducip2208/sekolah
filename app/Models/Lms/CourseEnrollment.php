<?php

namespace App\Models\Lms;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseEnrollment extends SchoolModel
{
    protected $table = 'course_enrollments';

    protected $fillable = [
        'school_id', 'course_id', 'student_id', 'status', 'progress_pct', 'completed_at',
    ];

    protected $casts = [
        'progress_pct'  => 'integer',
        'completed_at'  => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(CourseLessonCompletion::class, 'enrollment_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(CourseCertificate::class, 'course_enrollment_id');
    }
}
