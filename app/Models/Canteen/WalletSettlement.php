<?php

namespace App\Models\Canteen;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletSettlement extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'canteen_merchant_id', 'period_start', 'period_end',
        'gross_sales', 'refunds', 'net_amount', 'status', 'settled_at',
        'journal_entry_id', 'idempotency_key', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_sales' => 'integer',
        'refunds' => 'integer',
        'net_amount' => 'integer',
        'settled_at' => 'datetime',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(CanteenMerchant::class, 'canteen_merchant_id');
    }
}
