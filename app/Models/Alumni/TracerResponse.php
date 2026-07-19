<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerResponse extends SchoolModel
{
    protected $table = 'tracer_responses';

    protected $fillable = [
        'school_id', 'alumni_profile_id', 'graduation_year',
        'status', 'company_name', 'position', 'salary_range',
        'is_relevant', 'feedback', 'answers', 'submitted_at',
    ];

    protected $casts = [
        'is_relevant'  => 'boolean',
        'answers'      => 'array',
        'submitted_at' => 'datetime',
    ];

    public function alumniProfile(): BelongsTo
    {
        return $this->belongsTo(AlumniProfile::class);
    }
}
