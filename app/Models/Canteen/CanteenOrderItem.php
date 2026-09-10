<?php

namespace App\Models\Canteen;

use App\Models\SchoolTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenOrderItem extends SchoolTenantModel
{
    protected $fillable = [
        'school_id', 'canteen_order_id', 'canteen_menu_item_id', 'name',
        'unit_price', 'quantity', 'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CanteenOrder::class, 'canteen_order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(CanteenMenuItem::class, 'canteen_menu_item_id');
    }
}
