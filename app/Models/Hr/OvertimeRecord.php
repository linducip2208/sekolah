<?php

namespace App\Models\Hr;

use App\Models\Academic\Staff;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRecord extends SchoolModel
{
    protected $table = 'overtime_records';

    protected $fillable = [
        'school_id', 'staff_id', 'date', 'hours', 'rate_per_hour', 'amount',
        'note', 'status', 'approved_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'hours'         => 'decimal:2',
        'rate_per_hour' => 'integer',
        'amount'        => 'integer',
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
