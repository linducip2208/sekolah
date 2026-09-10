<?php

namespace App\Models\Canteen;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenWallet extends SchoolModel
{
    protected $table = 'canteen_wallets';

    protected $fillable = [
        'school_id', 'student_id', 'balance', 'daily_limit', 'blocked_categories', 'is_locked',
        'monthly_limit', 'low_balance_threshold', 'allow_negative', 'transfer_enabled',
    ];

    protected $casts = [
        'balance' => 'integer',
        'daily_limit' => 'integer',
        'blocked_categories' => 'array',
        'is_locked' => 'boolean',
        'monthly_limit' => 'integer',
        'low_balance_threshold' => 'integer',
        'allow_negative' => 'boolean',
        'transfer_enabled' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'canteen_wallet_id');
    }

    public function topups(): HasMany
    {
        return $this->hasMany(CanteenTopup::class, 'canteen_wallet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(CanteenOrder::class, 'canteen_wallet_id');
    }
}
