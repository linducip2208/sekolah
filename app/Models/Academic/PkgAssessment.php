<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PkgAssessment extends SchoolModel
{
    protected $table = 'pkg_assessments';

    protected $fillable = [
        'school_id', 'teacher_id', 'assessor_id', 'academic_year_id',
        'semester', 'assessment_date', 'type', 'status',
        'final_score', 'recommendation', 'notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'final_score'     => 'decimal:2',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(PkgScore::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(PkgObservation::class);
    }
}
