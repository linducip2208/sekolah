<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAvailability extends SchoolModel
{
    protected $table = 'timetable_teacher_availability';

    protected $fillable = [
        'school_id', 'staff_id', 'day_of_week', 'is_available',
    ];

    protected $casts = [
        'day_of_week'  => 'integer',
        'is_available' => 'boolean',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
