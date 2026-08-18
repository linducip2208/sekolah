<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricCriterion extends SchoolModel
{
    protected $table = 'rubric_criteria';

    protected $fillable = [
        'school_id', 'rubric_id', 'name', 'description', 'weight', 'sort_order',
    ];

    protected $casts = [
        'weight'      => 'integer',
        'sort_order'  => 'integer',
    ];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(RubricLevel::class, 'criteria_id');
    }
}
