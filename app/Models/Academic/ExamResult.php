<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends SchoolModel
{
    protected $fillable = [
        'school_id', 'exam_id', 'student_id', 'obtained_marks', 'status',
        'started_at', 'submitted_at', 'answers',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
        'answers'      => 'array',
        'obtained_marks' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
