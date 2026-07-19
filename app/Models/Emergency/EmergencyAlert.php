<?php

namespace App\Models\Emergency;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyAlert extends SchoolModel
{
    protected $fillable = [
        'school_id', 'alert_type', 'title', 'message',
        'triggered_by', 'severity', 'status', 'sent_at',
        'recipient_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmergencyRecipient::class);
    }
}
