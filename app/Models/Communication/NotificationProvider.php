<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use Illuminate\Support\Facades\Crypt;

class NotificationProvider extends SchoolModel
{
    protected $table = 'notification_providers';

    protected $fillable = [
        'school_id', 'name', 'transport', 'api_format', 'base_url',
        'api_key_encrypted', 'secret_encrypted', 'sender_id_encrypted',
        'extra_headers', 'extra_config', 'is_active', 'is_default',
    ];

    protected $casts = [
        'extra_headers' => 'array',
        'extra_config'  => 'array',
        'is_active'     => 'boolean',
        'is_default'    => 'boolean',
    ];

    public function getApiKeyAttribute(): ?string
    {
        return $this->api_key_encrypted ? $this->decrypt($this->api_key_encrypted) : null;
    }

    public function setApiKeyAttribute(?string $value): void
    {
        $this->api_key_encrypted = $value ? Crypt::encryptString($value) : null;
    }

    public function getSecretAttribute(): ?string
    {
        return $this->secret_encrypted ? $this->decrypt($this->secret_encrypted) : null;
    }

    public function setSecretAttribute(?string $value): void
    {
        $this->secret_encrypted = $value ? Crypt::encryptString($value) : null;
    }

    public function getSenderIdAttribute(): ?string
    {
        return $this->sender_id_encrypted ? $this->decrypt($this->sender_id_encrypted) : null;
    }

    public function setSenderIdAttribute(?string $value): void
    {
        $this->sender_id_encrypted = $value ? Crypt::encryptString($value) : null;
    }

    private function decrypt(?string $val): ?string
    {
        if (!$val) return null;
        try { return Crypt::decryptString($val); } catch (\Throwable) { return null; }
    }
}
