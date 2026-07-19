<?php

namespace App\Models\LessonPlan;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonPlan extends SchoolModel
{
    protected $table = 'lesson_plans';

    public const STATUSES = ['draft','submitted','approved','rejected','completed'];

    protected $fillable = [
        'school_id','class_section_id','subject_id','teacher_id','semester_id',
        'title','lesson_date','duration_minutes','learning_objectives','material_summary',
        'activities','assessment_methods','resources','curriculum_type',
        'status','reviewer_id','reviewed_at','reviewer_feedback',
        'actually_executed','execution_note',
    ];

    protected $casts = [
        'lesson_date'         => 'date',
        'learning_objectives' => 'array',
        'activities'          => 'array',
        'assessment_methods'  => 'array',
        'resources'           => 'array',
        'reviewed_at'         => 'datetime',
        'actually_executed'   => 'boolean',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(LessonPlanAttachment::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
