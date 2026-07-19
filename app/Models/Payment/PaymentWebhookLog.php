<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhookLog extends Model
{
    protected $table = 'payment_webhook_logs';

    public const SIGNATURE_VALID   = 'valid';
    public const SIGNATURE_INVALID = 'invalid';
    public const SIGNATURE_MISSING = 'missing';

    public const PROCESSING_RECEIVED  = 'received';
    public const PROCESSING_PROCESSED = 'processed';
    public const PROCESSING_FAILED    = 'failed';
    public const PROCESSING_DUPLICATE = 'duplicate';

    protected $fillable = [
        'payment_provider_id', 'payment_transaction_id', 'source_ip',
        'headers', 'payload', 'signature_status', 'processing_status', 'error_message',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
