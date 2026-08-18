<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MakeupClass extends SchoolModel
{
    protected $table = 'makeup_classes';

    protected $fillable = [
        'school_id', 'original_timetable_id', 'subject_id', 'teacher_id',
        'class_room_id', 'new_date', 'new_period_number', 'new_room',
        'reason', 'status', 'created_by',
    ];

    protected $casts = [
        'new_date'          => 'date',
        'new_period_number' => 'integer',
    ];

    const STATUSES = ['scheduled', 'completed', 'cancelled'];

    public function originalTimetable(): BelongsTo
    {
        return $this->belongsTo(TimetableSlot::class, 'original_timetable_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
