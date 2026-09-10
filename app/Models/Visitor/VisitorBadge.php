<?php

namespace App\Models\Visitor;

use App\Models\SchoolTenantModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorBadge extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'visitor_visit_id', 'badge_number', 'qr_token', 'issued_by',
        'issued_at', 'returned_at', 'status', 'metadata',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitorVisit::class, 'visitor_visit_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
