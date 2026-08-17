<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInstallment extends SchoolModel
{
    protected $table = 'fee_installments';

    protected $fillable = [
        'school_id', 'fee_invoice_id', 'installment_no', 'amount', 'paid_amount',
        'late_fee', 'due_date', 'status', 'paid_at',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'paid_amount'  => 'integer',
        'late_fee'     => 'integer',
        'installment_no' => 'integer',
        'due_date'     => 'date',
        'paid_at'      => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function getRemainingAttribute(): int
    {
        return $this->amount - $this->paid_amount;
    }
}
