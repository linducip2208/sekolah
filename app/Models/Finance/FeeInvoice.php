<?php

namespace App\Models\Finance;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'school_id', 'student_id', 'fee_structure_id', 'invoice_no',
        'due_date', 'amount', 'paid_amount', 'discount', 'status', 'period',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'amount'      => 'integer',
        'paid_amount' => 'integer',
        'discount'    => 'integer',
    ];

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class, 'fee_invoice_id')->orderBy('installment_no');
    }

    public function getAmountRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->amount / 100, 0, ',', '.');
    }
}
