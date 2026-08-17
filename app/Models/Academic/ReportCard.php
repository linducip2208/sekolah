<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'semester_id',
        'total_percentage', 'overall_grade', 'gpa', 'rank', 'remarks', 'is_published',
        'status', 'verification_token', 'approved_by', 'approved_at', 'locked_at',
        'competency_scores', 'extracurricular_notes', 'attendance_summary', 'teacher_notes',
    ];

    protected $casts = [
        'is_published'        => 'boolean',
        'approved_at'         => 'datetime',
        'locked_at'           => 'datetime',
        'competency_scores'   => 'array',
        'attendance_summary'  => 'array',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
