<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdiwiyataEvidence extends SchoolModel
{
    protected $table = 'adiwiyata_evidences';

    protected $fillable = [
        'school_id', 'adiwiyata_indicator_id', 'title', 'description',
        'file_path', 'score', 'status', 'verified_by', 'verified_at', 'notes',
    ];

    protected $casts = [
        'file_path' => 'array',
        'score' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataIndicator::class, 'adiwiyata_indicator_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
