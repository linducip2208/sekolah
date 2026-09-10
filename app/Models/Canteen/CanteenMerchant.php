<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenMerchant extends SchoolModel
{
    protected $fillable = ['school_id', 'name', 'phone', 'email', 'is_active', 'settings'];

    protected $casts = ['is_active' => 'boolean', 'settings' => 'array'];

    public function menuItems(): HasMany
    {
        return $this->hasMany(CanteenMenuItem::class, 'canteen_merchant_id');
    }
}
