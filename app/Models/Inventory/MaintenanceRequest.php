<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;

class MaintenanceRequest extends SchoolModel
{
    protected $table = 'maintenance_requests';

    protected $fillable = [
        'school_id','asset_id','location_text','reported_by','issue_description',
        'photos','priority','status','assigned_to','resolution_note','resolved_at','cost',
    ];

    protected $casts = [
        'photos'      => 'array',
        'resolved_at' => 'datetime',
        'cost'        => 'integer',
    ];
}
