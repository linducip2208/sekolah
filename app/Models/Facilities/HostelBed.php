<?php

namespace App\Models\Facilities;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelBed extends SchoolModel
{
    use AuditableModel;

    protected $fillable = ['school_id', 'hostel_room_id', 'bed_no', 'status', 'student_id'];

    protected $casts = [
        'student_id' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
