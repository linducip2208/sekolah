<?php

namespace App\Models\Facilities;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelAttendance extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'hostel_room_id', 'date',
        'status', 'check_in_time', 'check_out_time', 'noted_by', 'note',
    ];

    protected $casts = [
        'date'           => 'date',
        'check_in_time'  => 'date:H:i:s',
        'check_out_time' => 'date:H:i:s',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function notedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'noted_by');
    }
}
