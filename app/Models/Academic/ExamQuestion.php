<?php

namespace App\Models\Academic;

use App\Models\QuestionBank\QuestionBankItem;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends SchoolModel
{
    protected $fillable = [
        'school_id', 'exam_id', 'question_bank_item_id', 'question', 'type', 'options', 'correct_answer', 'marks', 'order',
        'difficulty_index', 'discrimination_index', 'distractor_analysis',
    ];

    protected $casts = [
        'options'               => 'array',
        'distractor_analysis'   => 'array',
        'difficulty_index'      => 'decimal:3',
        'discrimination_index'  => 'decimal:3',
        'marks'                 => 'integer',
        'order'                 => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function questionBankItem(): BelongsTo
    {
        return $this->belongsTo(QuestionBankItem::class, 'question_bank_item_id');
    }
}
