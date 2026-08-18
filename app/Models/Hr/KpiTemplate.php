<?php

namespace App\Models\Hr;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTemplate extends SchoolModel
{
    protected $table = 'kpi_templates';

    protected $fillable = [
        'school_id', 'name', 'description', 'max_score', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function criteria(): HasMany
    {
        return $this->hasMany(KpiCriteria::class, 'template_id');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(KpiAppraisal::class, 'template_id');
    }
}
