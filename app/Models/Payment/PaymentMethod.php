<?php

namespace App\Models\Payment;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends SchoolModel
{
    protected $table = 'payment_methods';

    public const FEE_BORNE_PARENT = 0;
    public const FEE_BORNE_SCHOOL = 1;

    protected $fillable = [
        'school_id', 'payment_provider_id', 'code', 'display_name',
        'logo_url', 'instruction_template',
        'fee_flat', 'fee_percent_bp', 'fee_borne_by',
        'min_amount', 'max_amount', 'expiry_minutes',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'fee_flat'        => 'integer',
        'fee_percent_bp'  => 'integer',
        'fee_borne_by'    => 'integer',
        'min_amount'      => 'integer',
        'max_amount'      => 'integer',
        'expiry_minutes'  => 'integer',
        'is_active'       => 'boolean',
        'sort_order'      => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function calculateFee(int $amountCents): int
    {
        $percentFee = intdiv($amountCents * $this->fee_percent_bp, 10_000);
        return $this->fee_flat + $percentFee;
    }

    public function feeBorneByParent(): bool
    {
        return $this->fee_borne_by === self::FEE_BORNE_PARENT;
    }
}
