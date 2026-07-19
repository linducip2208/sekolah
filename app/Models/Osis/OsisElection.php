<?php

namespace App\Models\Osis;

use App\Models\Academic\AcademicYear;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsisElection extends SchoolModel
{
    protected $fillable = [
        'school_id', 'title', 'academic_year_id', 'nomination_start',
        'nomination_end', 'voting_start', 'voting_end', 'status',
        'positions', 'max_votes_per_student',
    ];

    protected $casts = [
        'nomination_start' => 'datetime',
        'nomination_end'   => 'datetime',
        'voting_start'     => 'datetime',
        'voting_end'       => 'datetime',
        'positions'        => 'array',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(OsisCandidate::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(OsisVote::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(OsisProgram::class);
    }
}
