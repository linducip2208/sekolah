<?php

namespace App\Models\Hr;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiScore extends SchoolModel
{
    protected $table = 'kpi_scores';

    protected $fillable = [
        'school_id', 'appraisal_id', 'criteria_id', 'score', 'evidence',
    ];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(KpiAppraisal::class, 'appraisal_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(KpiCriteria::class, 'criteria_id');
    }
}
