<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends SchoolModel
{
    protected $table = 'teaching_journals';

    protected $fillable = [
        'school_id', 'teacher_id', 'class_section_id', 'subject_id', 'journal_date',
        'material', 'competency_ids', 'learning_activity', 'attendance_summary',
        'student_participation', 'homework', 'notes', 'reflection', 'attachments',
    ];

    protected $casts = [
        'journal_date'    => 'date',
        'competency_ids'  => 'array',
        'attachments'     => 'array',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
