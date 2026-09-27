<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'school_id', 'period', 'status',
        'closed_by', 'closed_at', 'reopened_by', 'reopened_at', 'notes',
    ];

    protected $casts = [
        'closed_at'   => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
