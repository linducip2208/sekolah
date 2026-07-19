<?php

namespace App\Models\Scholarship;

use App\Models\SchoolModel;

class ScholarshipProgram extends SchoolModel
{
    protected $table = 'scholarship_programs';

    protected $fillable = [
        'school_id','name','source','discount_type','discount_value',
        'eligibility_criteria','open_date','close_date','quota','required_documents','is_active',
    ];

    protected $casts = [
        'eligibility_criteria' => 'array',
        'required_documents'   => 'array',
        'open_date'            => 'date',
        'close_date'           => 'date',
        'discount_value'       => 'integer',
        'is_active'            => 'boolean',
    ];
}
