<?php

namespace App\Models\Facilities;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelGatePass extends SchoolModel
{
    protected $fillable = [
        'school_id', 'student_id', 'pass_type', 'purpose',
        'visitor_name', 'visitor_phone', 'requested_by', 'approved_by',
        'status', 'out_time', 'expected_return', 'actual_return', 'note',
    ];

    protected $casts = [
        'out_time'        => 'datetime',
        'expected_return' => 'datetime',
        'actual_return'   => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
