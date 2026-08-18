<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends SchoolModel
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'school_id', 'user_id', 'event_type',
        'email_enabled', 'push_enabled', 'sms_enabled', 'whatsapp_enabled',
    ];

    protected $casts = [
        'email_enabled'    => 'boolean',
        'push_enabled'     => 'boolean',
        'sms_enabled'      => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
