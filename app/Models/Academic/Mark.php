<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'subject_id', 'exam_id',
        'semester_id', 'obtained_marks', 'total_marks', 'grade',
    ];

    protected $casts = ['percentage' => 'float'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
