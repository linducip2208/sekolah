<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeRefund extends SchoolModel
{
    protected $table = 'fee_refunds';

    protected $fillable = [
        'school_id', 'fee_invoice_id', 'fee_payment_id', 'amount', 'reason',
        'refunded_by', 'refunded_at',
    ];

    protected $casts = [
        'amount'      => 'integer',
        'refunded_at' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
