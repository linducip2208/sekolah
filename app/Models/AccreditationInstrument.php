<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationInstrument extends Model
{
    protected $fillable = [
        'accreditation_standard_id', 'number', 'description', 'max_score', 'evidence_hint',
    ];

    protected $casts = [
        'max_score' => 'integer',
    ];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(AccreditationStandard::class);
    }
}
