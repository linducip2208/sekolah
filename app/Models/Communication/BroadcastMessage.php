<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BroadcastMessage extends SchoolModel
{
    use SoftDeletes;

    protected $table = 'broadcast_messages';

    protected $fillable = [
        'school_id', 'title', 'message', 'channel', 'segment',
        'custom_recipients', 'status', 'scheduled_at', 'sent_at',
        'recipient_count', 'created_by',
    ];

    protected $casts = [
        'custom_recipients' => 'array',
        'scheduled_at'      => 'datetime',
        'sent_at'           => 'datetime',
        'recipient_count'   => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
