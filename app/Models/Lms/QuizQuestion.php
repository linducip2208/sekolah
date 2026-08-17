<?php

namespace App\Models\Lms;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends SchoolModel
{
    protected $table = 'quiz_questions';

    protected $fillable = [
        'school_id', 'quiz_id', 'question_bank_item_id', 'question', 'type', 'options', 'correct_answer', 'order',
    ];

    protected $casts = [
        'options' => 'array',
        'order'   => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
