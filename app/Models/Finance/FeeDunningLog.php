<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;

class FeeDunningLog extends SchoolModel
{
    protected $fillable = [
        'school_id', 'fee_invoice_id', 'stage', 'channel', 'status', 'message',
    ];
}
