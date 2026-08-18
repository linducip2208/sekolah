<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtmSchedule extends SchoolModel
{
    protected $fillable = [
        'school_id',
        'student_id',
        'parent_user_id',
        'teacher_id',
        'class_room_id',
        'meeting_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'follow_up',
        'reminder_sent',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', 'scheduled')
            ->whereBetween('meeting_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }
}
