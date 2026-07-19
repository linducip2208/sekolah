<?php

namespace App\Models\Visitor;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorQrSession extends SchoolModel
{
    protected $fillable = [
        'visitor_log_id', 'qr_token', 'issued_at', 'expires_at',
        'scanned_at', 'scanned_by',
    ];

    protected $casts = [
        'issued_at'  => 'datetime',
        'expires_at' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    public function visitorLog(): BelongsTo
    {
        return $this->belongsTo(VisitorLog::class);
    }
}
