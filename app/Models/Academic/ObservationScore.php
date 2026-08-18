<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationScore extends SchoolModel
{
    protected $table = 'observation_scores';

    protected $fillable = [
        'school_id', 'observation_id', 'rubric_criteria_id', 'score', 'notes',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(StudentObservation::class, 'observation_id');
    }

    public function rubricCriterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'rubric_criteria_id');
    }
}
