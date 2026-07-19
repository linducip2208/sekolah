<?php

namespace App\Models\Finance;

use App\Models\Traits\AuditableModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use AuditableModel;

    protected $fillable = [
        'fee_invoice_id', 'collected_by', 'amount', 'payment_method',
        'reference', 'note', 'payment_date',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
