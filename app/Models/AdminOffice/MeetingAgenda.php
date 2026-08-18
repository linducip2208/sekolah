<?php

namespace App\Models\AdminOffice;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingAgenda extends SchoolModel
{
    protected $table = 'meeting_agendas';

    public const STATUSES = ['planned', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'school_id', 'title', 'description', 'meeting_date', 'start_time',
        'end_time', 'location', 'organizer_id', 'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'start_time'   => 'datetime:H:i',
        'end_time'     => 'datetime:H:i',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function minutes(): HasMany
    {
        return $this->hasMany(MeetingMinutes::class, 'agenda_id');
    }
}
