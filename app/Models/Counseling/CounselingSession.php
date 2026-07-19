<?php

namespace App\Models\Counseling;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends SchoolModel
{
    protected $table = 'counseling_sessions';

    protected $fillable = [
        'school_id','student_id','counselor_id','scheduled_at','duration_minutes',
        'type','status','notes','refer_external','referred_to',
    ];

    protected $casts = [
        'scheduled_at'   => 'datetime',
        'refer_external' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
