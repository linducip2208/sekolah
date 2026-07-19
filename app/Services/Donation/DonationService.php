<?php

namespace App\Services\Donation;

use App\Models\Donation\Donation;
use App\Models\Donation\DonationCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonationService
{
    public function createCampaign(int $schoolId, array $data): DonationCampaign
    {
        $slug = Str::slug($data['title']) . '-' . Str::lower(Str::random(4));

        return DonationCampaign::create(array_merge($data, [
            'school_id' => $schoolId,
            'slug'      => $slug,
            'status'    => $data['status'] ?? 'draft',
        ]));
    }

    public function recordDonation(int $schoolId, ?int $userId, int $campaignId, array $data, ?int $paymentTxId = null): Donation
    {
        return DB::transaction(function () use ($schoolId, $userId, $campaignId, $data, $paymentTxId) {
            $donation = Donation::create([
                'school_id'              => $schoolId,
                'donation_campaign_id'   => $campaignId,
                'user_id'                => $userId,
                'donor_name'             => $data['donor_name'] ?? null,
                'donor_email'            => $data['donor_email'] ?? null,
                'donor_phone'            => $data['donor_phone'] ?? null,
                'npwp'                   => $data['npwp'] ?? null,
                'is_anonymous'           => (bool) ($data['is_anonymous'] ?? false),
                'show_amount'            => (bool) ($data['show_amount'] ?? true),
                'amount'                 => (int) $data['amount'],
                'message'                => $data['message'] ?? null,
                'payment_transaction_id' => $paymentTxId,
                'status'                 => $paymentTxId ? 'pending' : 'completed',
                'donated_at'             => $paymentTxId ? null : now(),
            ]);

            if ($donation->status === 'completed') {
                $this->markCompleted($donation);
            }

            return $donation;
        });
    }

    public function markCompleted(Donation $donation): Donation
    {
        return DB::transaction(function () use ($donation) {
            if ($donation->status === 'completed' && $donation->donated_at) {
                return $donation;
            }

            $donation->update([
                'status'      => 'completed',
                'donated_at'  => now(),
                'receipt_no'  => $donation->receipt_no ?? 'DONATE-' . strtoupper(Str::random(10)),
            ]);

            if ($donation->donation_campaign_id) {
                DonationCampaign::where('id', $donation->donation_campaign_id)
                    ->increment('raised_amount', $donation->amount);
            }

            return $donation->fresh();
        });
    }
}
