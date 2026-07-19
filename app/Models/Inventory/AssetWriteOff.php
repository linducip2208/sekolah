<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetWriteOff extends SchoolModel
{
    protected $table = 'asset_write_offs';

    protected $fillable = [
        'school_id', 'asset_id', 'request_date', 'reason',
        'condition_at_writeoff', 'estimated_value', 'approved_by',
        'approved_at', 'status', 'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'date',
        'estimated_value' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
