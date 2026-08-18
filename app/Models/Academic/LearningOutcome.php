<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningOutcome extends SchoolModel
{
    protected $table = 'learning_outcomes';

    protected $fillable = [
        'school_id', 'subject_id', 'stage', 'description', 'code', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class);
    }
}
