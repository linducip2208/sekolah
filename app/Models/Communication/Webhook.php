<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Webhook extends SchoolModel
{
    protected $table = 'webhooks';

    protected $fillable = [
        'school_id', 'name', 'url', 'secret_encrypted',
        'events', 'extra_headers', 'max_retries', 'is_active',
        'last_delivered_at', 'last_failed_at',
    ];

    protected $casts = [
        'events'            => 'array',
        'extra_headers'     => 'array',
        'is_active'         => 'boolean',
        'last_delivered_at' => 'datetime',
        'last_failed_at'    => 'datetime',
    ];

    public function getSecretAttribute(): ?string
    {
        if (!$this->secret_encrypted) return null;
        try { return Crypt::decryptString($this->secret_encrypted); } catch (\Throwable) { return null; }
    }

    public function setSecretAttribute(?string $value): void
    {
        $this->secret_encrypted = $value ? Crypt::encryptString($value) : null;
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
