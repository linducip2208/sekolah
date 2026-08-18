<?php

namespace App\Models\Curriculum;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumVersion extends SchoolModel
{
    protected $table = 'curriculum_versions';

    protected $fillable = [
        'school_id', 'curriculum_framework_id', 'version_name',
        'academic_year', 'is_active', 'effective_date', 'notes',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'effective_date' => 'date',
    ];

    public function framework(): BelongsTo
    {
        return $this->belongsTo(CurriculumFramework::class, 'curriculum_framework_id');
    }
}
