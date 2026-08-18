<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalSignature extends SchoolModel
{
    protected $fillable = [
        'school_id', 'user_id', 'signature_image_path', 'certificate_path',
        'pin_code', 'is_active',
    ];

    protected $hidden = ['pin_code'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signedDocuments(): HasMany
    {
        return $this->hasMany(SignedDocument::class);
    }

    public function verifyPin(string $pin): bool
    {
        return \Hash::check($pin, $this->pin_code);
    }
}
