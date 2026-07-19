<?php

namespace App\Models\Analytics;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRiskScore extends SchoolModel
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    protected $table = 'student_risk_scores';

    protected $fillable = [
        'school_id','student_id','snapshot_date',
        'attendance_score','academic_score','behavior_score','engagement_score',
        'overall_risk','risk_level','top_risk_factors','recommendations',
    ];

    protected $casts = [
        'snapshot_date'    => 'date',
        'attendance_score' => 'decimal:2',
        'academic_score'   => 'decimal:2',
        'behavior_score'   => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'overall_risk'     => 'decimal:2',
        'top_risk_factors' => 'array',
        'recommendations'  => 'array',
    ];
}
