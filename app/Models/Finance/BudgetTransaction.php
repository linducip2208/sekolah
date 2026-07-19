<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetTransaction extends SchoolModel
{
    protected $fillable = [
        'school_id', 'budget_item_id', 'transaction_date',
        'amount', 'description', 'reference_no', 'receipt_path', 'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'integer',
    ];

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getAmountRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->amount / 100, 0, ',', '.');
    }
}
