<?php

namespace App\Models\QuestionBank;

use App\Models\Academic\ExamQuestion;
use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBankItem extends SchoolModel
{
    protected $table = 'question_bank_items';

    public const TYPES = ['multiple_choice', 'true_false', 'essay', 'matching', 'fill_blank'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public const COGNITIVE_LEVELS = ['c4', 'c5', 'c6', 'c7', 'c8'];

    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected'];

    protected $fillable = [
        'school_id', 'subject_id', 'question_bank_category_id', 'author_id',
        'question_html', 'type', 'question_type', 'options', 'answer_key', 'explanation_html',
        'difficulty', 'cognitive_level', 'tags', 'used_count',
        'avg_score_pct', 'discrimination', 'is_published',
        'version', 'parent_id', 'status', 'reviewed_by', 'reviewed_at',
        'total_attempts', 'correct_attempts',
    ];

    protected $casts = [
        'options'           => 'array',
        'answer_key'        => 'array',
        'tags'              => 'array',
        'used_count'        => 'integer',
        'avg_score_pct'     => 'decimal:2',
        'discrimination'    => 'decimal:3',
        'is_published'      => 'boolean',
        'version'           => 'integer',
        'reviewed_at'       => 'datetime',
        'total_attempts'    => 'integer',
        'correct_attempts'  => 'integer',
    ];

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tagModels(): BelongsToMany
    {
        return $this->belongsToMany(
            QuestionBankTag::class,
            'question_tag_pivot',
            'question_bank_item_id',
            'question_tag_id',
        );
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForReview($query)
    {
        return $query->where('status', 'submitted');
    }
}
