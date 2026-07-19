<?php

namespace App\Models\LiveClass;

use App\Models\SchoolModel;
use Illuminate\Support\Facades\Crypt;

class VideoProvider extends SchoolModel
{
    protected $table = 'video_providers';

    public const FORMATS = [
        'oauth_meeting_api', 'rest_room_api', 'self_hosted_jitsi', 'self_hosted_bbb', 'manual_link',
    ];

    protected $fillable = [
        'school_id','name','slug','api_format','base_url',
        'client_id_encrypted','client_secret_encrypted','access_token_encrypted',
        'extra_config','is_active',
    ];

    protected $casts = [
        'extra_config' => 'array',
        'is_active'    => 'boolean',
    ];

    protected $hidden = [
        'client_id_encrypted','client_secret_encrypted','access_token_encrypted',
    ];

    public function setClientIdAttribute(?string $v): void { $this->setEnc('client_id_encrypted', $v); }
    public function getClientIdAttribute(): ?string { return $this->getEnc('client_id_encrypted'); }
    public function setClientSecretAttribute(?string $v): void { $this->setEnc('client_secret_encrypted', $v); }
    public function getClientSecretAttribute(): ?string { return $this->getEnc('client_secret_encrypted'); }
    public function setAccessTokenAttribute(?string $v): void { $this->setEnc('access_token_encrypted', $v); }
    public function getAccessTokenAttribute(): ?string { return $this->getEnc('access_token_encrypted'); }

    protected function setEnc(string $field, ?string $value): void
    {
        $this->attributes[$field] = $value ? Crypt::encryptString($value) : null;
    }

    protected function getEnc(string $field): ?string
    {
        $v = $this->attributes[$field] ?? null;
        if (!$v) return null;
        try { return Crypt::decryptString($v); } catch (\Throwable) { return null; }
    }
}
