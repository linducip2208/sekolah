<?php

namespace App\Models\Committee;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommitteeMeeting extends SchoolModel
{
    protected $fillable = [
        'school_id', 'title', 'meeting_date', 'location',
        'agenda', 'status', 'minutes', 'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(CommitteeAttendance::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(CommitteeDecision::class);
    }
}
