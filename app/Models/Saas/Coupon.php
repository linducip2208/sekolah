<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'max_uses', 'used_count', 'valid_from', 'valid_until', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
        'is_active'      => 'boolean',
        'valid_from'     => 'date',
        'valid_until'    => 'date',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && now()->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function applyDiscount(int $amountCents): int
    {
        if ($this->discount_type === 'percentage') {
            return (int) round($amountCents * ($this->discount_value / 10000));
        }

        $discount = min($this->discount_value, $amountCents);
        return $amountCents - $discount;
    }

    public function recordUse(): void
    {
        $this->increment('used_count');
    }

    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return number_format($this->discount_value / 100, 0, ',', '.') . '%';
        }

        return 'Rp ' . number_format($this->discount_value / 100, 0, ',', '.');
    }
}
