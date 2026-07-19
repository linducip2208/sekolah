<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CooperativeLoan extends SchoolModel
{
    protected $table = 'cooperative_loans';

    protected $fillable = [
        'school_id', 'cooperative_member_id', 'loan_amount',
        'interest_rate', 'term_months', 'monthly_installment',
        'start_date', 'end_date', 'purpose', 'status',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'loan_amount' => 'integer',
        'interest_rate' => 'decimal:2',
        'term_months' => 'integer',
        'monthly_installment' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(CooperativeMember::class, 'cooperative_member_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(CooperativeInstallment::class);
    }
}
