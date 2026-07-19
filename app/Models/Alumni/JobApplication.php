<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends SchoolModel
{
    protected $table = 'job_applications';

    protected $fillable = [
        'job_listing_id', 'applicant_type', 'applicant_id',
        'full_name', 'email', 'phone', 'cover_letter',
        'resume_path', 'status', 'notes',
    ];

    protected $casts = [];

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
