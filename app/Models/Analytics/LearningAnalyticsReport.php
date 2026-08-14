<?php

namespace App\Models\Analytics;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAnalyticsReport extends SchoolModel
{
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    protected $table = 'learning_analytics_reports';

    protected $fillable = [
        'school_id',
        'scope',
        'class_section_id',
        'subject_id',
        'student_id',
        'period_start',
        'period_end',
        'metrics',
        'narrative',
        'generated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'metrics'      => 'array',
    ];
}
