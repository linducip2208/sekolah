<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenTopup extends SchoolModel
{
    protected $table = 'canteen_topups';

    protected $fillable = [
        'school_id', 'canteen_wallet_id', 'initiated_by', 'payment_transaction_id',
        'amount', 'status', 'idempotency_key', 'completed_at', 'metadata',
    ];

    protected $casts = ['amount' => 'integer', 'completed_at' => 'datetime', 'metadata' => 'array'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CanteenWallet::class, 'canteen_wallet_id');
    }
}
