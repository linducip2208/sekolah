<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLock extends SchoolModel
{
    protected $fillable = [
        'school_id', 'class_section_id', 'date', 'is_locked',
        'locked_by', 'locked_at', 'reopened_by', 'reopened_at', 'reopen_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}
