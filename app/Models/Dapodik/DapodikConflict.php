<?php

namespace App\Models\Dapodik;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapodikConflict extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'dapodik_sync_item_id', 'field_name', 'local_value', 'external_value',
        'resolution', 'resolved_by', 'resolved_at', 'resolution_note',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(DapodikSyncItem::class, 'dapodik_sync_item_id');
    }
}
