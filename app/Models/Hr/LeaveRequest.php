<?php

namespace App\Models\Hr;

use App\Models\Academic\Staff;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends SchoolModel
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'school_id', 'staff_id', 'type', 'start_date', 'end_date', 'days',
        'reason', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'days'        => 'integer',
        'approved_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
