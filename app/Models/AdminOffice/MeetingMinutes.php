<?php

namespace App\Models\AdminOffice;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingMinutes extends SchoolModel
{
    protected $table = 'meeting_minutes';

    protected $fillable = [
        'school_id', 'agenda_id', 'content', 'attendees', 'decisions',
        'follow_up_items', 'created_by',
    ];

    protected $casts = [
        'attendees'       => 'array',
        'decisions'       => 'array',
        'follow_up_items' => 'array',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(MeetingAgenda::class, 'agenda_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
