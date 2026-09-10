<?php

namespace App\Models\Dapodik;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DapodikSyncItem extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'dapodik_sync_run_id', 'entity_type', 'external_id', 'local_type',
        'local_id', 'status', 'source_payload', 'normalized_payload', 'local_snapshot',
        'error_message',
    ];

    protected $casts = [
        'source_payload' => 'array', 'normalized_payload' => 'array', 'local_snapshot' => 'array',
        'local_id' => 'integer',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DapodikSyncRun::class, 'dapodik_sync_run_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(DapodikConflict::class);
    }
}
