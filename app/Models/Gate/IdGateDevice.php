<?php

namespace App\Models\Gate;

use App\Models\SchoolModel;
use Illuminate\Support\Facades\Crypt;

class IdGateDevice extends SchoolModel
{
    protected $table = 'id_gate_devices';

    protected $fillable = [
        'school_id','name','location','device_token_encrypted','type','is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = ['device_token_encrypted'];

    public function setDeviceTokenAttribute(?string $value): void
    {
        $this->attributes['device_token_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDeviceTokenAttribute(): ?string
    {
        $value = $this->attributes['device_token_encrypted'] ?? null;
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (\Throwable) { return null; }
    }
}
