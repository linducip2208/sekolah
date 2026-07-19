<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyTemplate extends SchoolModel
{
    protected $fillable = [
        'school_id', 'title', 'description', 'survey_type',
        'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
