<?php

namespace App\Models\Hr;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiCriteria extends SchoolModel
{
    protected $table = 'kpi_criteria';

    protected $fillable = [
        'school_id', 'template_id', 'name', 'description', 'weight', 'max_score', 'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(KpiTemplate::class, 'template_id');
    }
}
