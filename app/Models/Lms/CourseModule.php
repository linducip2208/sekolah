<?php

namespace App\Models\Lms;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends SchoolModel
{
    protected $table = 'course_modules';

    protected $fillable = ['school_id', 'course_id', 'title', 'description', 'order'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'course_module_id')->orderBy('order');
    }
}
