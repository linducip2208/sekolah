<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends SchoolModel
{
    protected $fillable = [
        'school_id', 'name', 'code', 'parent_id', 'type', 'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BudgetCategory::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
