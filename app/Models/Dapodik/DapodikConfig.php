<?php

namespace App\Models\Dapodik;

use App\Models\SchoolModel;
use Illuminate\Support\Facades\Crypt;

class DapodikConfig extends SchoolModel
{
    protected $table = 'dapodik_config';

    protected $fillable = [
        'school_id','npsn','username_encrypted','password_encrypted',
        'endpoint_url','field_mappings','last_sync_at',
    ];

    protected $casts = [
        'field_mappings' => 'array',
        'last_sync_at'   => 'datetime',
    ];

    protected $hidden = ['username_encrypted','password_encrypted'];

    public function setUsernameAttribute(?string $v): void { $this->setEnc('username_encrypted', $v); }
    public function getUsernameAttribute(): ?string { return $this->getEnc('username_encrypted'); }
    public function setPasswordAttribute(?string $v): void { $this->setEnc('password_encrypted', $v); }
    public function getPasswordAttribute(): ?string { return $this->getEnc('password_encrypted'); }

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
