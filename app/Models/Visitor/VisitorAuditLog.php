<?php

namespace App\Models\Visitor;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorAuditLog extends SchoolTenantModel
{
    public $timestamps = false;

    protected $table = 'visitor_audit_logs';

    protected $fillable = [
        'school_id', 'visitor_id', 'visitor_visit_id', 'actor_user_id', 'event',
        'ip_address', 'user_agent', 'metadata', 'created_at',
    ];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitorVisit::class, 'visitor_visit_id');
    }
}
