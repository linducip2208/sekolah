<?php

namespace App\Models\Scholarship;

use App\Models\SchoolModel;

class ScholarshipGrant extends SchoolModel
{
    protected $table = 'scholarship_grants';

    protected $fillable = [
        'school_id','scholarship_application_id','student_id','fee_invoice_id',
        'discount_applied','applied_at',
    ];

    protected $casts = [
        'discount_applied' => 'integer',
        'applied_at'       => 'date',
    ];
}
