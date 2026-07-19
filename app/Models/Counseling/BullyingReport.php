<?php

namespace App\Models\Counseling;

use App\Models\SchoolModel;

class BullyingReport extends SchoolModel
{
    protected $table = 'bullying_reports';

    protected $fillable = [
        'school_id','reporter_id','is_anonymous','victims_described',
        'perpetrators_described','type','incident_date','location','description',
        'evidence_files','status','assigned_to','investigation_notes','action_summary',
    ];

    protected $casts = [
        'is_anonymous'           => 'boolean',
        'incident_date'          => 'date',
        'victims_described'      => 'array',
        'perpetrators_described' => 'array',
        'evidence_files'         => 'array',
    ];
}
