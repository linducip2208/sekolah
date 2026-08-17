<?php

namespace App\Models\Audit;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditFinding extends SchoolModel
{
    protected $table = 'audit_findings';

    protected $fillable = [
        'school_id', 'internal_audit_id', 'area', 'description', 'severity',
        'status', 'action', 'due_date', 'resolved_at',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'resolved_at' => 'datetime',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class, 'internal_audit_id');
    }
}
