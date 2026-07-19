<?php

namespace App\Models\Payment;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class PaymentProvider extends SchoolModel
{
    protected $table = 'payment_providers';

    public const FORMAT_REDIRECT_CHECKOUT      = 'redirect_checkout';
    public const FORMAT_VIRTUAL_ACCOUNT        = 'virtual_account';
    public const FORMAT_EWALLET_DEEPLINK       = 'ewallet_deeplink';
    public const FORMAT_QRIS_DYNAMIC           = 'qris_dynamic';
    public const FORMAT_QRIS_STATIC            = 'qris_static';
    public const FORMAT_BANK_TRANSFER_MANUAL   = 'bank_transfer_manual';
    public const FORMAT_CASH                   = 'cash';

    public const FORMATS = [
        self::FORMAT_REDIRECT_CHECKOUT,
        self::FORMAT_VIRTUAL_ACCOUNT,
        self::FORMAT_EWALLET_DEEPLINK,
        self::FORMAT_QRIS_DYNAMIC,
        self::FORMAT_QRIS_STATIC,
        self::FORMAT_BANK_TRANSFER_MANUAL,
        self::FORMAT_CASH,
    ];

    protected $fillable = [
        'school_id', 'name', 'slug', 'api_format', 'base_url',
        'api_key_encrypted', 'secret_key_encrypted', 'merchant_id_encrypted',
        'webhook_secret_encrypted', 'callback_url',
        'extra_config', 'extra_headers',
        'is_sandbox', 'is_active', 'priority',
    ];

    protected $casts = [
        'extra_config'  => 'array',
        'extra_headers' => 'array',
        'is_sandbox'    => 'boolean',
        'is_active'     => 'boolean',
        'priority'      => 'integer',
    ];

    protected $hidden = [
        'api_key_encrypted',
        'secret_key_encrypted',
        'merchant_id_encrypted',
        'webhook_secret_encrypted',
    ];

    public function methods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(): ?string
    {
        return $this->decrypt('api_key_encrypted');
    }

    public function setSecretKeyAttribute(?string $value): void
    {
        $this->attributes['secret_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSecretKeyAttribute(): ?string
    {
        return $this->decrypt('secret_key_encrypted');
    }

    public function setMerchantIdAttribute(?string $value): void
    {
        $this->attributes['merchant_id_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getMerchantIdAttribute(): ?string
    {
        return $this->decrypt('merchant_id_encrypted');
    }

    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getWebhookSecretAttribute(): ?string
    {
        return $this->decrypt('webhook_secret_encrypted');
    }

    public function maskedApiKey(): ?string
    {
        $key = $this->api_key;
        return $key ? str_repeat('*', max(0, strlen($key) - 4)) . substr($key, -4) : null;
    }

    protected function decrypt(string $field): ?string
    {
        $value = $this->attributes[$field] ?? null;
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
