<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricLevel extends SchoolModel
{
    protected $table = 'rubric_levels';

    protected $fillable = [
        'school_id', 'criteria_id', 'level_name', 'score', 'description',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'criteria_id');
    }
}
