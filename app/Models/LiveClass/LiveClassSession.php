<?php

namespace App\Models\LiveClass;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClassSession extends SchoolModel
{
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

    protected $table = 'live_class_sessions';

    protected $fillable = [
        'school_id','class_section_id','subject_id','teacher_id','video_provider_id',
        'topic','scheduled_start','duration_minutes',
        'meeting_id','join_url','host_url','passcode',
        'status','recording_url',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
    ];
}
