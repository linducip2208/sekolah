<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;

class CanteenWallet extends SchoolModel
{
    protected $table = 'canteen_wallets';

    protected $fillable = [
        'school_id','student_id','balance','daily_limit','blocked_categories','is_locked',
    ];

    protected $casts = [
        'balance'             => 'integer',
        'daily_limit'         => 'integer',
        'blocked_categories'  => 'array',
        'is_locked'           => 'boolean',
    ];
}
