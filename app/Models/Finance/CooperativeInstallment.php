<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CooperativeInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cooperative_installments';

    protected $fillable = [
        'cooperative_loan_id', 'installment_number', 'due_date',
        'amount', 'paid_amount', 'paid_date', 'status',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'due_date' => 'date',
        'amount' => 'integer',
        'paid_amount' => 'integer',
        'paid_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(CooperativeLoan::class, 'cooperative_loan_id');
    }
}
