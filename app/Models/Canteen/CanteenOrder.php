<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;

class CanteenOrder extends SchoolModel
{
    protected $table = 'canteen_orders';

    protected $fillable = [
        'school_id','student_id','canteen_wallet_id','order_no',
        'pickup_at','items','total','source','status',
    ];

    protected $casts = [
        'pickup_at' => 'datetime',
        'items'     => 'array',
        'total'     => 'integer',
    ];
}
