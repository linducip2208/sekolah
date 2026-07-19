<?php

namespace App\Models\Facilities;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelAllocation extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'hostel_room_id', 'from_date', 'to_date', 'is_active',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }
}
