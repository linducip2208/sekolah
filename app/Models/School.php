<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'subdomain', 'logo', 'address', 'phone',
        'email', 'timezone', 'locale', 'is_active',
        'plan_id', 'plan_expires_at', 'settings',
        'currency_code', 'currency_symbol', 'currency_decimals',
        'currency_thousands_sep', 'currency_decimal_sep',
    ];

    protected $casts = [
        'settings'        => 'array',
        'is_active'       => 'boolean',
        'plan_expires_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function isSubscriptionActive(): bool
    {
        if (!$this->plan_expires_at) {
            return false;
        }
        $gracePeriod = config('multitenancy.grace_period_days', 7);
        return now()->lt($this->plan_expires_at->addDays($gracePeriod));
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
