<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends SchoolModel
{
    protected $table = 'academic_events';

    protected $fillable = [
        'school_id', 'title', 'description', 'event_type',
        'start_date', 'end_date', 'all_day', 'color',
        'class_section_id', 'created_by', 'is_published',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'all_day'    => 'boolean',
        'is_published' => 'boolean',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
