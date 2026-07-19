<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;

class CanteenTopup extends SchoolModel
{
    protected $table = 'canteen_topups';

    protected $fillable = [
        'school_id','canteen_wallet_id','initiated_by','payment_transaction_id',
        'amount','status',
    ];

    protected $casts = ['amount' => 'integer'];
}
