<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableConfig extends SchoolModel
{
    protected $table = 'timetable_configs';

    protected $fillable = [
        'school_id', 'academic_year_id', 'class_section_id',
        'days_per_week', 'periods_per_day', 'period_duration_minutes',
        'break_after_periods', 'start_time',
    ];

    protected $casts = [
        'days_per_week'            => 'integer',
        'periods_per_day'          => 'integer',
        'period_duration_minutes'  => 'integer',
        'break_after_periods'      => 'array',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }
}
