<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonStudy extends SchoolModel
{
    protected $fillable = [
        'school_id', 'title', 'subject_id', 'class_section_id',
        'topic', 'phase', 'status', 'plan_date', 'teach_date',
        'reflect_date', 'created_by', 'lead_teacher_id',
        'description', 'plan_notes',
    ];

    protected $casts = [
        'plan_date'    => 'date',
        'teach_date'   => 'date',
        'reflect_date' => 'date',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leadTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_teacher_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(LessonStudyMember::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(LessonStudyObservation::class);
    }

    public function reflections(): HasMany
    {
        return $this->hasMany(LessonStudyReflection::class);
    }
}
