<?php

namespace App\Models\AI;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends SchoolModel
{
    protected $table = 'ai_providers';

    public const FORMAT_OPENAI_COMPATIBLE = 'openai_compatible';
    public const FORMAT_ANTHROPIC         = 'anthropic_format';
    public const FORMAT_GEMINI            = 'gemini_format';
    public const FORMAT_IMAGE_GENERIC     = 'image_generic';

    public const FORMATS = [
        self::FORMAT_OPENAI_COMPATIBLE,
        self::FORMAT_ANTHROPIC,
        self::FORMAT_GEMINI,
        self::FORMAT_IMAGE_GENERIC,
    ];

    protected $fillable = [
        'school_id','name','slug','api_format','base_url',
        'api_key_encrypted','extra_headers','extra_config',
        'is_active','priority',
    ];

    protected $casts = [
        'extra_headers' => 'array',
        'extra_config'  => 'array',
        'is_active'     => 'boolean',
    ];

    protected $hidden = ['api_key_encrypted'];

    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class);
    }

    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(): ?string
    {
        $value = $this->attributes['api_key_encrypted'] ?? null;
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }

    public function maskedApiKey(): ?string
    {
        $key = $this->api_key;
        return $key ? str_repeat('*', max(0, strlen($key) - 4)) . substr($key, -4) : null;
    }
}
