<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends SchoolModel
{
    protected $fillable = [
        'survey_response_id', 'survey_question_id',
        'answer_text', 'answer_rating',
    ];

    protected $casts = [
        'answer_rating' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
