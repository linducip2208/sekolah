<?php

namespace App\Models\Medical;

use App\Models\SchoolModel;

class MedicalRecord extends SchoolModel
{
    protected $table = 'medical_records';

    protected $fillable = [
        'school_id','student_id','blood_type','allergies','chronic_conditions',
        'current_medications','emergency_contact_name','emergency_contact_phone',
        'insurance_provider','insurance_number',
    ];

    protected $casts = [
        'allergies'           => 'array',
        'chronic_conditions'  => 'array',
        'current_medications' => 'array',
    ];
}
