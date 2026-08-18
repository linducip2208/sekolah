<?php

namespace App\Models\Automation;

use App\Models\SchoolModel;

class AutomationRule extends SchoolModel
{
    protected $table = 'automation_rules';

    public const TRIGGERS = ['fee_due_soon', 'fee_overdue', 'student_absent_streak', 'birthday', 'contract_expiry', 'certification_expiry', 'ptm_reminder'];

    public const ACTIONS = ['notify', 'email'];

    protected $fillable = [
        'school_id', 'name', 'trigger_type', 'config', 'action_type', 'action_config', 'is_active',
    ];

    protected $casts = [
        'config'        => 'array',
        'action_config' => 'array',
        'is_active'     => 'boolean',
    ];
}
