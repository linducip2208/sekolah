<?php

namespace App\Models\Committee;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeProposal extends SchoolModel
{
    protected $fillable = [
        'school_id', 'proposer_id', 'title', 'description',
        'estimated_budget', 'status', 'reviewed_by', 'review_notes',
    ];

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
