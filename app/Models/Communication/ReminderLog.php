<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends SchoolModel
{
    protected $fillable = [
        'school_id', 'reminder_schedule_id', 'target_id', 'target_phone',
        'target_email', 'message_sent', 'channel', 'sent_at', 'status', 'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ReminderSchedule::class, 'reminder_schedule_id');
    }
}
