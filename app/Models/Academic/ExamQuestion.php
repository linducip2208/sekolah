<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'question', 'type', 'options', 'correct_answer', 'marks', 'order',
    ];

    protected $casts = ['options' => 'array'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
