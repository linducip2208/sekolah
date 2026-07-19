<?php

namespace App\Models\Dapodik;

use App\Models\SchoolModel;

class DapodikSyncLog extends SchoolModel
{
    protected $table = 'dapodik_sync_logs';

    protected $fillable = [
        'school_id','direction','entity','records_total','records_success','records_failed',
        'errors','status','triggered_by',
    ];

    protected $casts = [
        'records_total'   => 'integer',
        'records_success' => 'integer',
        'records_failed'  => 'integer',
        'errors'          => 'array',
    ];
}
