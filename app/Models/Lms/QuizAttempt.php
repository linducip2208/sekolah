<?php

namespace App\Models\Lms;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends SchoolModel
{
    protected $table = 'quiz_attempts';

    protected $fillable = [
        'school_id', 'quiz_id', 'student_id', 'attempt_no', 'score', 'total', 'passed', 'answers', 'submitted_at',
    ];

    protected $casts = [
        'attempt_no'   => 'integer',
        'score'        => 'integer',
        'total'        => 'integer',
        'passed'       => 'boolean',
        'answers'      => 'array',
        'submitted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
