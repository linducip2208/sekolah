<?php

namespace App\Models\Dapodik;

use App\Models\SchoolTenantModel;

class DapodikEntityMapping extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'entity_type', 'external_id', 'local_type', 'local_id', 'last_synced_at',
    ];

    protected $casts = ['local_id' => 'integer', 'last_synced_at' => 'datetime'];
}
