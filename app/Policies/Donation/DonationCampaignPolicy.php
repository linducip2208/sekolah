<?php

namespace App\Policies\Donation;

use App\Models\Donation\DonationCampaign;
use App\Models\User;

class DonationCampaignPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'accountant']);
    }

    public function update(User $user, DonationCampaign $campaign): bool
    {
        return $user->school_id === $campaign->school_id
            && $user->hasAnyRole(['admin', 'accountant']);
    }
}
