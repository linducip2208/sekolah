<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class BillingAccount extends Model
{
    protected $table = 'platform_billing_accounts';

    protected $fillable = [
        'bank_name', 'account_number', 'account_holder', 'logo_path',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];
}
