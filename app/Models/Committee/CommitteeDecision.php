<?php

namespace App\Models\Committee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeDecision extends Model
{
    protected $fillable = [
        'committee_meeting_id', 'title', 'description',
        'decision_type', 'voting_result', 'voting_detail', 'status',
    ];

    protected $casts = [
        'voting_detail' => 'array',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(CommitteeMeeting::class, 'committee_meeting_id');
    }
}
