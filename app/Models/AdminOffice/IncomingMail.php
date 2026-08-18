<?php

namespace App\Models\AdminOffice;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingMail extends SchoolModel
{
    protected $table = 'incoming_mails';

    public const STATUSES = ['received', 'dispositioned', 'archived'];

    protected $fillable = [
        'school_id', 'mail_no', 'sender_name', 'sender_address', 'subject',
        'received_date', 'disposition_to', 'disposition_notes', 'status', 'document_path',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function dispositionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposition_to');
    }
}
