<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    use SoftDeletes;

    protected $table = 'platform_payment_gateways';

    public const FORMAT_REDIRECT_CHECKOUT     = 'redirect_checkout';
    public const FORMAT_VIRTUAL_ACCOUNT       = 'virtual_account';
    public const FORMAT_EWALLET_DEEPLINK      = 'ewallet_deeplink';
    public const FORMAT_QRIS_DYNAMIC          = 'qris_dynamic';
    public const FORMAT_QRIS_STATIC           = 'qris_static';
    public const FORMAT_BANK_TRANSFER_MANUAL  = 'bank_transfer_manual';

    public const FORMATS = [
        self::FORMAT_REDIRECT_CHECKOUT,
        self::FORMAT_VIRTUAL_ACCOUNT,
        self::FORMAT_EWALLET_DEEPLINK,
        self::FORMAT_QRIS_DYNAMIC,
        self::FORMAT_QRIS_STATIC,
        self::FORMAT_BANK_TRANSFER_MANUAL,
    ];

    protected $fillable = [
        'name', 'slug', 'api_format', 'base_url',
        'api_key_encrypted', 'secret_key_encrypted', 'merchant_id_encrypted', 'webhook_secret_encrypted',
        'callback_url', 'extra_config', 'extra_headers',
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
        'api_key_encrypted', 'secret_key_encrypted',
        'merchant_id_encrypted', 'webhook_secret_encrypted',
    ];

    /** Auto-encrypt secret fields. */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->decrypt($this->api_key_encrypted),
            set: fn ($value) => ['api_key_encrypted' => $this->encrypt($value)],
        );
    }

    protected function secretKey(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->decrypt($this->secret_key_encrypted),
            set: fn ($value) => ['secret_key_encrypted' => $this->encrypt($value)],
        );
    }

    protected function merchantId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->decrypt($this->merchant_id_encrypted),
            set: fn ($value) => ['merchant_id_encrypted' => $this->encrypt($value)],
        );
    }

    protected function webhookSecret(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->decrypt($this->webhook_secret_encrypted),
            set: fn ($value) => ['webhook_secret_encrypted' => $this->encrypt($value)],
        );
    }

    private function encrypt(?string $value): ?string
    {
        return $value ? Crypt::encryptString($value) : null;
    }

    private function decrypt(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function formatLabel(): string
    {
        return match ($this->api_format) {
            self::FORMAT_REDIRECT_CHECKOUT    => 'Redirect Checkout (Snap, Hosted Page)',
            self::FORMAT_VIRTUAL_ACCOUNT      => 'Virtual Account',
            self::FORMAT_EWALLET_DEEPLINK     => 'E-Wallet (deeplink)',
            self::FORMAT_QRIS_DYNAMIC         => 'QRIS Dynamic',
            self::FORMAT_QRIS_STATIC          => 'QRIS Static',
            self::FORMAT_BANK_TRANSFER_MANUAL => 'Manual Bank Transfer',
            default                           => $this->api_format,
        };
    }
}
