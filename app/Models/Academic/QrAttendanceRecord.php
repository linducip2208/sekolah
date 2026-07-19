<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrAttendanceRecord extends SchoolModel
{
    protected $fillable = [
        'school_id', 'qr_attendance_session_id', 'student_id',
        'scanned_at', 'ip_address', 'device_info',
        'status', 'late_minutes',
    ];

    protected $casts = [
        'scanned_at'   => 'datetime',
        'late_minutes' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(QrAttendanceSession::class, 'qr_attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
