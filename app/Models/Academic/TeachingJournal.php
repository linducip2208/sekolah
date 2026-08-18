<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends SchoolModel
{
    protected $table = 'teaching_journals';

    protected $fillable = [
        'school_id', 'teacher_id', 'staff_id', 'class_section_id', 'class_room_id',
        'subject_id', 'timetable_id', 'journal_date', 'date', 'class_number',
        'material', 'topic', 'competency_ids', 'learning_activity', 'activity',
        'attendance_summary', 'student_participation', 'homework', 'notes',
        'reflection', 'attachments', 'status',
    ];

    protected $casts = [
        'journal_date'    => 'date',
        'date'            => 'date',
        'class_number'    => 'integer',
        'competency_ids'  => 'array',
        'attachments'     => 'array',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(TimetableSlot::class, 'timetable_id');
    }
}
