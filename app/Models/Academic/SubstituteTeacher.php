<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubstituteTeacher extends SchoolModel
{
    protected $table = 'substitute_teachers';

    protected $fillable = [
        'school_id', 'original_teacher_id', 'substitute_teacher_id',
        'timetable_entry_id', 'date', 'period_number', 'reason',
        'status', 'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    const STATUSES = ['pending', 'approved', 'cancelled'];

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_teacher_id');
    }

    public function substituteUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    public function timetableEntry(): BelongsTo
    {
        return $this->belongsTo(TimetableSlot::class, 'timetable_entry_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
