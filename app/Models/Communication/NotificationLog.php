<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends SchoolModel
{
    protected $table = 'notifications_log';

    protected $fillable = [
        'school_id', 'user_id', 'type', 'title', 'body', 'data', 'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
