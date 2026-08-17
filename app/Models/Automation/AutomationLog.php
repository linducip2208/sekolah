<?php

namespace App\Models\Automation;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends SchoolModel
{
    protected $table = 'automation_logs';

    protected $fillable = [
        'school_id', 'automation_rule_id', 'trigger_type', 'target_user_id',
        'payload', 'status', 'error', 'executed_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'executed_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
