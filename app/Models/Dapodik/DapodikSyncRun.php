<?php

namespace App\Models\Dapodik;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DapodikSyncRun extends SchoolTenantModel
{
    protected $fillable = [
        'run_uuid', 'school_id', 'entity_type', 'direction', 'status', 'started_at',
        'completed_at', 'total', 'inserted', 'updated', 'unchanged', 'conflicted',
        'failed', 'initiated_by', 'error_summary',
    ];

    protected $casts = [
        'started_at' => 'datetime', 'completed_at' => 'datetime',
        'total' => 'integer', 'inserted' => 'integer', 'updated' => 'integer',
        'unchanged' => 'integer', 'conflicted' => 'integer', 'failed' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DapodikSyncItem::class);
    }
}
