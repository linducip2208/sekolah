<?php

namespace App\Models\AdminOffice;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutgoingMail extends SchoolModel
{
    protected $table = 'outgoing_mails';

    public const STATUSES = ['draft', 'sent', 'archived'];

    protected $fillable = [
        'school_id', 'mail_no', 'recipient_name', 'recipient_address', 'subject',
        'sent_date', 'letter_template_id', 'status', 'document_path',
    ];

    protected $casts = [
        'sent_date' => 'date',
    ];
}
