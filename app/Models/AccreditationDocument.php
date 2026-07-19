<?php

namespace App\Models;

use App\Models\Communication\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationDocument extends SchoolModel
{
    protected $fillable = [
        'school_id', 'accreditation_instrument_id', 'file_path', 'document_id',
        'description', 'uploaded_by', 'status', 'reviewer_notes',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AccreditationInstrument::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
