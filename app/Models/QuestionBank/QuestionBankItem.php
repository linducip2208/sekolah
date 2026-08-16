<?php

namespace App\Models\QuestionBank;

use App\Models\Academic\ExamQuestion;
use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBankItem extends SchoolModel
{
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionBankCategory::class, 'question_bank_category_id');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'question_bank_item_id');
    }

    protected $table = 'question_bank_items';

    public const TYPES = ['multiple_choice', 'true_false', 'short_answer', 'essay'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public const COGNITIVE_LEVELS = ['remembering', 'understanding', 'applying', 'analyzing', 'evaluating', 'creating'];

    protected $fillable = [
        'school_id','subject_id','question_bank_category_id','author_id',
        'question_html','type','options','answer_key','explanation_html',
        'difficulty','cognitive_level','tags','used_count',
        'avg_score_pct','discrimination','is_published',
    ];

    protected $casts = [
        'options'        => 'array',
        'answer_key'     => 'array',
        'tags'           => 'array',
        'used_count'     => 'integer',
        'avg_score_pct'  => 'decimal:2',
        'discrimination' => 'decimal:3',
        'is_published'   => 'boolean',
    ];
}
