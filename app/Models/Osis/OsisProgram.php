<?php

namespace App\Models\Osis;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisProgram extends SchoolModel
{
    protected $fillable = [
        'school_id', 'osis_election_id', 'title', 'description',
        'budget', 'start_date', 'end_date', 'status', 'progress_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'osis_election_id');
    }
}
