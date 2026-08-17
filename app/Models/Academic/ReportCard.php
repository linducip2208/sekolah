<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'semester_id',
        'total_percentage', 'overall_grade', 'gpa', 'rank', 'remarks', 'is_published',
        'verification_token', 'competency_scores', 'extracurricular_notes', 'attendance_summary', 'teacher_notes',
    ];

    protected $casts = [
        'is_published'        => 'boolean',
        'competency_scores'   => 'array',
        'attendance_summary'  => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
