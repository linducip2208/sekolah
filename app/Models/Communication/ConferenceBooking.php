<?php

namespace App\Models\Communication;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceBooking extends SchoolModel
{
    protected $fillable = [
        'school_id', 'conference_session_id', 'student_id',
        'parent_id', 'booking_time', 'status',
        'notes', 'teacher_notes', 'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function conferenceSession(): BelongsTo
    {
        return $this->belongsTo(ConferenceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
