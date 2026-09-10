<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenOrder extends SchoolModel
{
    protected $table = 'canteen_orders';

    protected $fillable = [
        'school_id', 'student_id', 'canteen_wallet_id', 'order_no',
        'pickup_at', 'items', 'total', 'source', 'status', 'canteen_merchant_id', 'idempotency_key',
    ];

    protected $casts = [
        'pickup_at' => 'datetime',
        'items' => 'array',
        'total' => 'integer',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(CanteenOrderItem::class, 'canteen_order_id');
    }
}
