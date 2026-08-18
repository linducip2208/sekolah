<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeroomTeacher extends SchoolModel
{
    protected $table = 'homeroom_teachers';

    protected $fillable = [
        'school_id', 'staff_id', 'class_room_id', 'academic_year',
        'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
