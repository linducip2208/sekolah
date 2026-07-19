<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Letter extends SchoolModel
{
    protected $table = 'letters';

    protected $fillable = [
        'school_id', 'letter_template_id', 'letter_number',
        'recipient_type', 'recipient_id', 'recipient_name',
        'recipient_address', 'subject', 'content', 'status',
        'issued_by', 'issued_at', 'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
