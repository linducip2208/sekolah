<?php

namespace App\Models\AdminOffice;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTask extends SchoolModel
{
    protected $table = 'staff_tasks';

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    public const STATUSES   = ['todo', 'in_progress', 'done', 'overdue'];

    protected $fillable = [
        'school_id', 'title', 'description', 'assigned_to', 'assigned_by',
        'due_date', 'priority', 'status', 'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
