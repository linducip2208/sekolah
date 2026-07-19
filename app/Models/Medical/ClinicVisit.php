<?php

namespace App\Models\Medical;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicVisit extends SchoolModel
{
    protected $table = 'clinic_visits';

    protected $fillable = [
        'school_id','student_id','attended_by','visit_at','symptoms','diagnosis',
        'treatment','medications_given','temperature_c','blood_pressure',
        'parent_notified','returned_to_class','sent_home','referred_external','referred_to',
    ];

    protected $casts = [
        'visit_at'           => 'datetime',
        'medications_given'  => 'array',
        'temperature_c'      => 'decimal:1',
        'parent_notified'    => 'boolean',
        'returned_to_class'  => 'boolean',
        'sent_home'          => 'boolean',
        'referred_external'  => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}
