<?php

namespace App\Models\Visitor;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorVisit extends SchoolModel
{
    protected $fillable = [
        'school_id', 'visitor_id', 'visit_date', 'purpose', 'host_user_id',
        'destination', 'expected_arrival', 'check_in_at', 'check_out_at',
        'badge_number', 'qr_token', 'invitation_token', 'status', 'approved_by',
        'pre_registered', 'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'expected_arrival' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'pre_registered' => 'boolean',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function badges(): HasMany
    {
        return $this->hasMany(VisitorBadge::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(VisitorAuditLog::class);
    }
}
