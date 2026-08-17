<?php

namespace App\Models\Analytics;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyAlert extends SchoolModel
{
    protected $table = 'anomaly_alerts';

    protected $fillable = [
        'school_id', 'type', 'severity', 'title', 'description',
        'metric_value', 'reference_value', 'context', 'detected_at', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'metric_value'    => 'decimal:2',
        'reference_value' => 'decimal:2',
        'context'         => 'array',
        'detected_at'     => 'datetime',
        'resolved_at'     => 'datetime',
    ];

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
