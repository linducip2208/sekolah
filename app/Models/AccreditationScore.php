<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationScore extends SchoolModel
{
    protected $fillable = [
        'school_id', 'accreditation_instrument_id', 'self_score',
        'actual_score', 'notes', 'scored_by', 'scored_at',
    ];

    protected $casts = [
        'self_score'   => 'integer',
        'actual_score' => 'integer',
        'scored_at'    => 'datetime',
    ];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AccreditationInstrument::class);
    }

    public function scorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scored_by');
    }
}
