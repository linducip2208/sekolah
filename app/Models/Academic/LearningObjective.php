<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningObjective extends SchoolModel
{
    protected $table = 'learning_objectives';

    protected $fillable = [
        'school_id', 'learning_outcome_id', 'description', 'code', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class);
    }
}
