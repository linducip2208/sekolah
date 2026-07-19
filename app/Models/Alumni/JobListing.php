<?php

namespace App\Models\Alumni;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends SchoolModel
{
    protected $table = 'job_listings';

    protected $fillable = [
        'school_id', 'alumni_profile_id', 'company_name', 'position_title',
        'job_type', 'location', 'salary_range', 'description', 'requirements',
        'application_url', 'application_email', 'is_verified', 'is_active',
        'posted_at', 'expires_at', 'view_count',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active'   => 'boolean',
        'posted_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'view_count'  => 'integer',
    ];

    public function alumniProfile(): BelongsTo
    {
        return $this->belongsTo(AlumniProfile::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getSlugAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->company_name . ' ' . $this->position_title) . '-' . $this->id;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }
}
