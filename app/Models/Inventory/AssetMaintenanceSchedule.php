<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceSchedule extends SchoolModel
{
    protected $table = 'asset_maintenance_schedules';

    protected $fillable = [
        'school_id', 'asset_id', 'maintenance_type', 'scheduled_date',
        'completed_date', 'cost', 'performed_by', 'notes', 'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
