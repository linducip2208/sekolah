<?php

namespace App\Models\Donation;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationCampaign extends SchoolModel
{
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    protected $table = 'donation_campaigns';

    protected $fillable = [
        'school_id','title','slug','description','target_amount','raised_amount',
        'start_date','end_date','cover_image_path','updates','category','status','is_public',
    ];

    protected $casts = [
        'target_amount' => 'integer',
        'raised_amount' => 'integer',
        'start_date'    => 'date',
        'end_date'      => 'date',
        'updates'       => 'array',
        'is_public'     => 'boolean',
    ];

    public function progressPercent(): int
    {
        if ($this->target_amount <= 0) return 0;
        return (int) round(($this->raised_amount / $this->target_amount) * 100);
    }
}
