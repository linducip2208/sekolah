<?php

namespace App\Models\Hr;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiAppraisal extends SchoolModel
{
    protected $table = 'kpi_appraisals';

    protected $fillable = [
        'school_id', 'staff_id', 'template_id', 'reviewer_id', 'period',
        'status', 'total_score', 'reviewer_notes', 'staff_notes',
    ];

    protected $casts = ['total_score' => 'integer'];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Academic\Staff::class, 'staff_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(KpiTemplate::class, 'template_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(KpiScore::class, 'appraisal_id');
    }

    public function getGradeAttribute(): string
    {
        $score = $this->total_score ?? 0;
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}
