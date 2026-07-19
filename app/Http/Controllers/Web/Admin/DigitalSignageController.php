<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement\StudentAchievement;
use App\Models\Communication\Notice;
use App\Models\Event\SchoolEvent;
use App\Models\Academic\TimetableSlot;
use App\Models\Osis\OsisCandidate;
use App\Models\Osis\OsisElection;
use App\Services\OsisElectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigitalSignageController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function config(): View
    {
        return view('school-admin.signage.config');
    }

    public function saveConfig(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'show_announcements' => 'boolean',
            'show_schedule'      => 'boolean',
            'show_achievements'  => 'boolean',
            'show_events'        => 'boolean',
            'show_prayer_times'  => 'boolean',
            'show_clock'         => 'boolean',
            'show_weather'       => 'boolean',
            'show_ticker'        => 'boolean',
            'ticker_text'        => 'nullable|string|max:500',
            'school_motto'       => 'nullable|string|max:200',
            'refresh_interval'   => 'nullable|integer|min:10|max:600',
        ]);

        $school = \App\Models\School::find($this->schoolId());
        $currentSettings = $school->settings ?? [];
        $currentSettings['signage'] = $data;
        $school->update(['settings' => $currentSettings]);

        return back()->with('success', 'Konfigurasi signage disimpan.');
    }

    public function display(Request $request, int $schoolId): View
    {
        $school = \App\Models\School::findOrFail($schoolId);

        $signage = $school->getSetting('signage', []);

        $announcements = collect();
        $todaysSchedule = collect();
        $achievements   = collect();
        $upcomingEvents = collect();

        if (!empty($signage['show_announcements'])) {
            $announcements = Notice::where('school_id', $schoolId)
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('expire_at')->orWhere('expire_at', '>=', now());
                })
                ->orderByDesc('publish_at')
                ->limit(10)
                ->get();
        }

        if (!empty($signage['show_schedule'])) {
            $today = now()->dayOfWeek;
            $dayMap = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
            $dayName = $dayMap[$today] ?? 'senin';

            $todaysSchedule = TimetableSlot::where('school_id', $schoolId)
                ->where('day_of_week', $dayName)
                ->with(['classSection.classRoom', 'subject', 'teacher'])
                ->orderBy('start_time')
                ->get();
        }

        if (!empty($signage['show_achievements'])) {
            $achievements = StudentAchievement::where('school_id', $schoolId)
                ->with(['student.user', 'category'])
                ->orderByDesc('achieved_at')
                ->limit(10)
                ->get();
        }

        if (!empty($signage['show_events'])) {
            $upcomingEvents = SchoolEvent::where('school_id', $schoolId)
                ->where('is_published', true)
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(8)
                ->get();
        }

        $refreshInterval = $signage['refresh_interval'] ?? 60;
        $schoolMotto     = $signage['school_motto'] ?? $school->name;
        $tickerText      = $signage['ticker_text'] ?? '';

        return view('signage.display', compact(
            'school', 'signage', 'announcements', 'todaysSchedule',
            'achievements', 'upcomingEvents', 'refreshInterval',
            'schoolMotto', 'tickerText'
        ));
    }

    public function osisResults(Request $request, int $schoolId): View
    {
        $school = \App\Models\School::findOrFail($schoolId);

        $election = OsisElection::where('school_id', $schoolId)
            ->whereIn('status', ['voting', 'completed'])
            ->latest()
            ->first();

        if (!$election) {
            return view('signage.osis-empty', compact('school'));
        }

        $candidates = OsisCandidate::where('osis_election_id', $election->id)
            ->with('student.user')
            ->orderByDesc('vote_count')
            ->get();

        $electionService = app(OsisElectionService::class);
        $winners = $electionService->generateWinnerList($election);
        $totalVoters = $electionService->getTotalVoters($election);
        $refreshInterval = 15;

        return view('signage.osis-results', compact(
            'school', 'election', 'candidates', 'winners',
            'totalVoters', 'refreshInterval'
        ));
    }
}
