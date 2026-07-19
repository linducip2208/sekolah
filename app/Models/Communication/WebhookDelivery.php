<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'webhook_id', 'school_id', 'event', 'event_id', 'payload',
        'http_status', 'response_body', 'status', 'attempts',
        'next_retry_at', 'delivered_at',
    ];

    protected $casts = [
        'next_retry_at' => 'datetime',
        'delivered_at'  => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
