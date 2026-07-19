<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CooperativeMember extends SchoolModel
{
    protected $table = 'cooperative_members';

    protected $fillable = [
        'school_id', 'memberable_type', 'memberable_id', 'member_number',
        'join_date', 'total_savings', 'total_loans', 'status',
    ];

    protected $casts = [
        'join_date' => 'date',
        'total_savings' => 'integer',
        'total_loans' => 'integer',
    ];

    public function memberable(): MorphTo
    {
        return $this->morphTo();
    }

    public function savings(): HasMany
    {
        return $this->hasMany(CooperativeSaving::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(CooperativeLoan::class);
    }
}
