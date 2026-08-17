<?php

namespace App\Models\Lms;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends SchoolModel
{
    protected $table = 'courses';

    protected $fillable = [
        'school_id', 'subject_id', 'teacher_id', 'title', 'description', 'icon', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasManyThrough(CourseLesson::class, CourseModule::class, 'course_id', 'course_module_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
