<?php

namespace App\Models\Payment;

use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends SchoolModel
{
    protected $table = 'payment_transactions';

    public const STATUS_PENDING          = 'pending';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PAID             = 'paid';
    public const STATUS_EXPIRED          = 'expired';
    public const STATUS_FAILED           = 'failed';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_REFUNDED         = 'refunded';
    public const STATUS_DISPUTED         = 'disputed';

    public const TERMINAL_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_EXPIRED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    protected $fillable = [
        'school_id', 'fee_invoice_id', 'payment_method_id', 'payment_provider_id',
        'initiated_by', 'fee_payment_id',
        'reference_no', 'external_id', 'gateway_transaction_id',
        'amount', 'fee_amount', 'net_amount', 'currency', 'status',
        'redirect_url', 'va_number', 'va_bank_code', 'qr_string', 'deeplink_url',
        'raw_request', 'raw_response',
        'expired_at', 'paid_at',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'fee_amount'   => 'integer',
        'net_amount'   => 'integer',
        'raw_request'  => 'array',
        'raw_response' => 'array',
        'expired_at'   => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(PaymentWebhookLog::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast()
            && !in_array($this->status, [self::STATUS_PAID, self::STATUS_REFUNDED], true);
    }
}
