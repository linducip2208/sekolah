<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementRequest extends SchoolModel
{
    protected $table = 'procurement_requests';

    protected $fillable = [
        'school_id', 'request_number', 'requester_id', 'department',
        'title', 'description', 'estimated_budget', 'urgency', 'budget_category_id', 'status',
        'approved_by', 'approved_at', 'rejected_reason', 'notes',
    ];

    protected $casts = [
        'estimated_budget' => 'integer',
        'approved_at'      => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProcurementApproval::class);
    }

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function totalEstimated(): int
    {
        return (int) $this->items->sum(fn ($item) => $item->estimated_unit_price * $item->quantity);
    }

    public function totalActual(): ?int
    {
        if ($this->items->isEmpty()) return null;
        $total = 0;
        $hasAll = true;
        foreach ($this->items as $item) {
            if ($item->actual_unit_price === null) {
                $hasAll = false;
                break;
            }
            $total += $item->actual_unit_price * $item->quantity;
        }
        return $hasAll ? (int) $total : null;
    }
}
