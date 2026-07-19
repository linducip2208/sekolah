<?php

namespace App\Models\Osis;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisVote extends Model
{
    protected $fillable = [
        'osis_election_id', 'voter_id', 'candidate_id', 'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'osis_election_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(OsisCandidate::class);
    }
}
