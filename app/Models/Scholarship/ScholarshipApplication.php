<?php

namespace App\Models\Scholarship;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipApplication extends SchoolModel
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    public function program(): BelongsTo
    {
        return $this->belongsTo(ScholarshipProgram::class, 'scholarship_program_id');
    }

    protected $table = 'scholarship_applications';

    protected $fillable = [
        'school_id','scholarship_program_id','student_id','documents','motivation',
        'status','reviewer_id','reviewer_note','granted_from','granted_until',
    ];

    protected $casts = [
        'documents'     => 'array',
        'granted_from'  => 'date',
        'granted_until' => 'date',
    ];
}
