<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Models\Donation\Donation;
use App\Models\Donation\DonationCampaign;
use App\Models\School;
use App\Services\Donation\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(private DonationService $service) {}

    public function publicCampaigns(string $subdomain): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $campaigns = DonationCampaign::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_public', true)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $campaigns]);
    }

    public function publicShowCampaign(string $subdomain, string $slug): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $campaign = DonationCampaign::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $donations = Donation::withoutGlobalScopes()
            ->where('donation_campaign_id', $campaign->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($d) => [
                'donor_name'  => $d->is_anonymous ? 'Anonim' : $d->donor_name,
                'amount'      => $d->show_amount ? $d->amount : null,
                'message'     => $d->message,
                'donated_at'  => $d->donated_at,
            ]);

        return response()->json([
            'campaign'  => $campaign,
            'donations' => $donations,
        ]);
    }

    public function publicDonate(Request $request, string $subdomain, string $slug): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $campaign = DonationCampaign::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $data = $request->validate([
            'donor_name'   => 'required|string|max:200',
            'donor_email'  => 'required|email|max:200',
            'donor_phone'  => 'nullable|string|max:30',
            'npwp'         => 'nullable|string|max:30',
            'is_anonymous' => 'nullable|boolean',
            'show_amount'  => 'nullable|boolean',
            'amount'       => 'required|integer|min:100',
            'message'      => 'nullable|string|max:1000',
        ]);

        $donation = $this->service->recordDonation(
            $school->id, null, $campaign->id, $data,
        );

        return response()->json($donation, 201);
    }

    public function adminCampaigns(Request $request): JsonResponse
    {
        return response()->json([
            'data' => DonationCampaign::where('school_id', $request->user()->school_id)
                ->orderByDesc('created_at')->paginate(50),
        ]);
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'required|string',
            'target_amount'     => 'required|integer|min:0',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'cover_image_path'  => 'nullable|string|max:500',
            'category'          => 'nullable|in:scholarship,building,equipment,emergency,general',
            'status'            => 'nullable|in:draft,active,completed,cancelled',
            'is_public'         => 'nullable|boolean',
        ]);

        return response()->json(
            $this->service->createCampaign($request->user()->school_id, $data),
            201,
        );
    }

    public function donations(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Donation::where('school_id', $request->user()->school_id)
                ->when($request->input('campaign_id'), fn ($q, $cid) => $q->where('donation_campaign_id', $cid))
                ->orderByDesc('created_at')->paginate(50),
        ]);
    }
}
