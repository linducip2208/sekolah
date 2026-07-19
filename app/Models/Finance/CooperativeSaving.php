<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CooperativeSaving extends SchoolModel
{
    protected $table = 'cooperative_savings';

    protected $fillable = [
        'school_id', 'cooperative_member_id', 'transaction_date',
        'amount', 'savings_type', 'transaction_type', 'reference_no',
        'notes', 'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(CooperativeMember::class, 'cooperative_member_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
