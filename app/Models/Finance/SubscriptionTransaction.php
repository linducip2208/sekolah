<?php

namespace App\Models\Finance;

use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionTransaction extends SchoolModel
{
    protected $table = 'subscription_transactions';

    protected $fillable = [
        'school_id', 'plan_id', 'amount', 'payment_method',
        'reference', 'status', 'period_from', 'period_to',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to'   => 'date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
