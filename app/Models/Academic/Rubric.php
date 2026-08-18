<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends SchoolModel
{
    protected $table = 'rubrics';

    protected $fillable = [
        'school_id', 'name', 'description', 'subject_id', 'max_score',
    ];

    protected $casts = [
        'max_score' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class, 'rubric_id');
    }
}
