<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends SchoolModel
{
    protected $fillable = [
        'school_id', 'survey_template_id', 'question_text',
        'question_type', 'options', 'sort_order',
    ];

    protected $casts = [
        'options'  => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
