<?php

namespace App\Models\Canteen;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletRefund extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'canteen_wallet_id', 'canteen_order_id', 'wallet_transaction_id',
        'amount', 'reason', 'status', 'idempotency_key', 'requested_by', 'approved_by',
    ];

    protected $casts = ['amount' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CanteenOrder::class, 'canteen_order_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CanteenWallet::class, 'canteen_wallet_id');
    }
}
