<?php

namespace App\Models;

class SignedDocument extends SchoolModel
{
    protected $fillable = [
        'school_id', 'digital_signature_id', 'document_type', 'document_id',
        'signed_at', 'ip_address', 'hash_value',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signature()
    {
        return $this->belongsTo(DigitalSignature::class);
    }

    public function document()
    {
        return $this->morphTo();
    }
}
