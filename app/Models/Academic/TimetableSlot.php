<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSlot extends SchoolModel
{
    protected $table = 'timetable_slots';

    protected $fillable = [
        'school_id', 'class_section_id', 'subject_id', 'teacher_id',
        'day_of_week', 'start_time', 'end_time', 'room',
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
}
