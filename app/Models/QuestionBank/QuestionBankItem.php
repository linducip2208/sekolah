<?php

namespace App\Models\QuestionBank;

use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $table = 'question_bank_items';

    public const TYPES = ['mcq','multi_select','true_false','essay','fill_blank','matching','numeric'];

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
