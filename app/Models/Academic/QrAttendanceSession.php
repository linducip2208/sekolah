<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrAttendanceSession extends SchoolModel
{
    protected $fillable = [
        'school_id', 'class_section_id', 'subject_id',
        'teacher_id', 'session_date', 'qr_code',
        'qr_expires_at', 'is_active',
    ];

    protected $casts = [
        'session_date'   => 'date',
        'qr_expires_at'  => 'datetime',
        'is_active'      => 'boolean',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(QrAttendanceRecord::class);
    }
}
