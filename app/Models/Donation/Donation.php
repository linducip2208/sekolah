<?php

namespace App\Models\Donation;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends SchoolModel
{
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DonationCampaign::class, 'donation_campaign_id');
    }

    protected $table = 'donations';

    protected $fillable = [
        'school_id','donation_campaign_id','user_id',
        'donor_name','donor_email','donor_phone','npwp',
        'is_anonymous','show_amount','amount','message',
        'payment_transaction_id','status','receipt_no','donated_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'show_amount'  => 'boolean',
        'amount'       => 'integer',
        'donated_at'   => 'datetime',
    ];
}
