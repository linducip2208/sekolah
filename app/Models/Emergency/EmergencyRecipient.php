<?php

namespace App\Models\Emergency;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyRecipient extends Model
{
    protected $fillable = [
        'emergency_alert_id', 'recipient_type', 'recipient_id',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class, 'emergency_alert_id');
    }
}
