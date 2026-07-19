<?php

namespace App\Http\Resources\Donation;

use App\Models\Donation\DonationCampaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DonationCampaign */
class DonationCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'cover_image_url'   => $this->cover_image_path,
            'target_amount'     => $this->target_amount,
            'target_formatted'  => 'Rp ' . number_format($this->target_amount / 100, 0, ',', '.'),
            'raised_amount'     => $this->raised_amount,
            'raised_formatted'  => 'Rp ' . number_format($this->raised_amount / 100, 0, ',', '.'),
            'progress_percent'  => $this->progressPercent(),
            'category'          => $this->category,
            'status'            => $this->status,
            'is_public'         => $this->is_public,
            'start_date'        => $this->start_date?->toDateString(),
            'end_date'          => $this->end_date?->toDateString(),
            'days_remaining'    => $this->end_date ? max(0, now()->diffInDays($this->end_date, false)) : null,
            'updates'           => $this->updates,
            'donate_url'        => url("/donate/{$this->school?->subdomain}/{$this->slug}"),
        ];
    }
}
