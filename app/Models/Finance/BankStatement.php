<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatement extends SchoolModel
{
    protected $table = 'bank_statements';

    protected $fillable = [
        'school_id', 'bank_account', 'transaction_date', 'description', 'reference_no',
        'amount', 'status', 'fee_payment_id', 'matched_by', 'matched_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'integer',
        'matched_at'       => 'datetime',
    ];

    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }
}
