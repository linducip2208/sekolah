<?php

namespace App\Models\Audit;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalAudit extends SchoolModel
{
    protected $table = 'internal_audits';

    protected $fillable = [
        'school_id', 'title', 'period', 'auditor', 'status', 'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'started_at'   => 'date',
        'completed_at' => 'date',
    ];

    public function findings(): HasMany
    {
        return $this->hasMany(AuditFinding::class, 'internal_audit_id');
    }
}
