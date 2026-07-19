<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends SchoolModel
{
    protected $fillable = [
        'school_id', 'lesson_id', 'title', 'instructions', 'due_date', 'total_marks',
        'question_type', 'answer_key', 'auto_grade',
        'allow_late_submission', 'max_file_size_mb',
    ];

    protected $casts = [
        'due_date'              => 'datetime',
        'answer_key'            => 'array',
        'auto_grade'            => 'boolean',
        'allow_late_submission' => 'boolean',
        'max_file_size_mb'      => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssignmentQuestion::class)->orderBy('question_number');
    }
}
