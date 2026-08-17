<?php

namespace App\Models\Lms;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends SchoolModel
{
    protected $table = 'quizzes';

    protected $fillable = [
        'school_id', 'course_id', 'title', 'description', 'pass_score', 'is_published',
    ];

    protected $casts = [
        'pass_score'   => 'integer',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
