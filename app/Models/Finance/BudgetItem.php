<?php

namespace App\Models\Finance;

use App\Models\Academic\AcademicYear;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItem extends SchoolModel
{
    protected $fillable = [
        'school_id', 'budget_category_id', 'academic_year_id',
        'name', 'description', 'planned_amount', 'actual_amount', 'status',
    ];

    protected $casts = [
        'planned_amount' => 'integer',
        'actual_amount'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }

    public function getPlannedAmountRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->planned_amount / 100, 0, ',', '.');
    }

    public function getActualAmountRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->actual_amount / 100, 0, ',', '.');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->planned_amount <= 0) {
            return 0;
        }
        return min(100, round(($this->actual_amount / $this->planned_amount) * 100, 1));
    }
}
