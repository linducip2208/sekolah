<?php

namespace App\Models\Committee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeAttendance extends Model
{
    protected $fillable = [
        'committee_meeting_id', 'committee_member_id', 'status', 'notes',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(CommitteeMeeting::class, 'committee_meeting_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class, 'committee_member_id');
    }
}
