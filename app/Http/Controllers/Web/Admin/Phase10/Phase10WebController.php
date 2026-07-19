<?php

namespace App\Http\Controllers\Web\Admin\Phase10;

use App\Http\Controllers\Controller;
use App\Models\Achievement\StudentAchievement;
use App\Models\Donation\Donation;
use App\Models\Donation\DonationCampaign;
use App\Models\Event\EventRsvp;
use App\Models\Event\SchoolEvent;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipProgram;
use Illuminate\View\View;

class Phase10WebController extends Controller
{
    public function donations(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.donations.dashboard', [
            'campaigns' => DonationCampaign::where('school_id', $schoolId)->orderByDesc('created_at')->get(),
            'totalRaised' => Donation::where('school_id', $schoolId)->where('status', 'completed')->sum('amount'),
            'donorCount' => Donation::where('school_id', $schoolId)->where('status', 'completed')->distinct('donor_email')->count('donor_email'),
            'recentDonations' => Donation::where('school_id', $schoolId)->where('status', 'completed')
                ->orderByDesc('donated_at')->limit(20)->get(),
        ]);
    }

    public function achievements(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.achievements.dashboard', [
            'recent' => StudentAchievement::where('school_id', $schoolId)
                ->orderByDesc('achieved_at')->limit(50)->get(),
            'unverified' => StudentAchievement::where('school_id', $schoolId)
                ->where('verified', false)->count(),
            'totalThisYear' => StudentAchievement::where('school_id', $schoolId)
                ->whereYear('achieved_at', now()->year)->count(),
        ]);
    }

    public function scholarship(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.scholarship.dashboard', [
            'programs' => ScholarshipProgram::where('school_id', $schoolId)->where('is_active', true)->get(),
            'applications' => ScholarshipApplication::where('school_id', $schoolId)
                ->orderByDesc('created_at')->limit(50)->get(),
            'pendingReview' => ScholarshipApplication::where('school_id', $schoolId)
                ->whereIn('status', ['submitted', 'review'])->count(),
        ]);
    }

    public function events(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.events.dashboard', [
            'upcoming' => SchoolEvent::where('school_id', $schoolId)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')->get(),
            'totalRsvps' => EventRsvp::where('school_id', $schoolId)
                ->whereHas('schoolEvent', fn ($q) => $q->where('starts_at', '>=', now()))
                ->where('status', 'going')->count(),
        ]);
    }
}
