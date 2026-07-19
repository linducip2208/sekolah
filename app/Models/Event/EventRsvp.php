<?php

namespace App\Models\Event;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends SchoolModel
{
    protected $table = 'event_rsvps';

    protected $fillable = [
        'school_id','school_event_id','user_id','guests_count',
        'status','payment_transaction_id','ticket_qr_token','checked_in_at',
    ];

    protected $casts = [
        'guests_count'  => 'integer',
        'checked_in_at' => 'datetime',
    ];

    public function schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'school_event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
