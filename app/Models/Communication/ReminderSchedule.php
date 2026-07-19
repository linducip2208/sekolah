<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderSchedule extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'recipient_type', 'trigger_days_before',
        'reminder_type', 'message_template', 'is_active', 'last_triggered_at',
    ];

    protected $casts = [
        'trigger_days_before' => 'array',
        'is_active'           => 'boolean',
        'last_triggered_at'   => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }
}
