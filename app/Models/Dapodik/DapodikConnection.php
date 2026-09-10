<?php

namespace App\Models\Dapodik;

use App\Models\SchoolTenantModel;
use Illuminate\Support\Facades\Crypt;

class DapodikConnection extends SchoolTenantModel
{
    protected $table = 'dapodik_connections';

    protected $fillable = [
        'school_id', 'connection_type', 'host', 'npsn', 'timeout', 'verify_ssl',
        'field_mappings', 'status', 'last_tested_at', 'last_sync_at',
    ];

    protected $hidden = [
        'token_encrypted', 'username_encrypted', 'password_encrypted',
        'registration_code_encrypted',
    ];

    protected $casts = [
        'timeout' => 'integer',
        'verify_ssl' => 'boolean',
        'field_mappings' => 'array',
        'last_tested_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function setSecret(string $name, ?string $value): void
    {
        $field = $name.'_encrypted';
        abort_unless(in_array($field, [
            'token_encrypted', 'username_encrypted', 'password_encrypted', 'registration_code_encrypted',
        ], true), 422, 'Secret Dapodik tidak dikenal.');
        $this->setAttribute($field, filled($value) ? Crypt::encryptString($value) : null);
    }

    public function secret(string $name): ?string
    {
        $field = $name.'_encrypted';
        $value = $this->getAttribute($field);
        if (! $value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasSecret(string $name): bool
    {
        return filled($this->getAttribute($name.'_encrypted'));
    }
}
