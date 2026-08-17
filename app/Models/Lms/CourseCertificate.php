<?php

namespace App\Models\Lms;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCertificate extends SchoolModel
{
    protected $table = 'course_certificates';

    protected $fillable = [
        'school_id', 'course_enrollment_id', 'certificate_no', 'issued_at', 'issued_by',
    ];

    protected $casts = ['issued_at' => 'date'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
