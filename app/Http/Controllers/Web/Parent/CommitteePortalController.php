<?php

namespace App\Http\Controllers\Web\Parent;

use App\Http\Controllers\Controller;
use App\Models\Committee\CommitteeAttendance;
use App\Models\Committee\CommitteeDecision;
use App\Models\Committee\CommitteeMeeting;
use App\Models\Committee\CommitteeProposal;
use Illuminate\View\View;

class CommitteePortalController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $upcomingMeetings = CommitteeMeeting::where('status', '!=', 'cancelled')
            ->where('meeting_date', '>=', now()->startOfDay())
            ->orderBy('meeting_date')
            ->limit(5)
            ->get();

        $recentMeetings = CommitteeMeeting::with(['attendances.member.user', 'decisions'])
            ->where('status', 'completed')
            ->orderByDesc('meeting_date')
            ->limit(10)
            ->get();

        $proposals = CommitteeProposal::with('reviewer')
            ->orderByDesc('created_at')
            ->get();

        return view('parent-portal.committee.index', compact('upcomingMeetings', 'recentMeetings', 'proposals'));
    }

    public function showMeeting(int $id): View
    {
        $meeting = CommitteeMeeting::with([
            'attendances.member.user',
            'decisions',
        ])->findOrFail($id);

        return view('parent-portal.committee.meeting', compact('meeting'));
    }
}
