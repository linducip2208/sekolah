<?php

namespace App\Models\PPDB;

use App\Models\School;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbPeriod extends SchoolModel
{
    protected $table = 'ppdb_periods';

    protected $fillable = [
        'school_id', 'academic_year_id', 'name',
        'open_date', 'close_date', 'announcement_date', 'reregistration_deadline',
        'form_fee', 'jalur_config', 'document_requirements', 'is_published',
    ];

    protected $casts = [
        'open_date'                => 'date',
        'close_date'               => 'date',
        'announcement_date'        => 'date',
        'reregistration_deadline'  => 'date',
        'form_fee'                 => 'integer',
        'jalur_config'             => 'array',
        'document_requirements'    => 'array',
        'is_published'             => 'boolean',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(PpdbApplication::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
