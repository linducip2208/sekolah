<?php

namespace App\Models\Finance;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionEnquiry extends SchoolModel
{
    protected $table = 'admission_enquiries';

    protected $fillable = [
        'school_id', 'student_name', 'father_name', 'mother_name',
        'phone', 'email', 'date_of_birth', 'class_applying',
        'status', 'converted_student_id', 'note',
    ];

    protected $casts = ['date_of_birth' => 'date'];

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }
}
