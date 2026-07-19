<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Committee\CommitteeAttendance;
use App\Models\Committee\CommitteeDecision;
use App\Models\Committee\CommitteeMeeting;
use App\Models\Committee\CommitteeMember;
use App\Models\Committee\CommitteeProposal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // ───── Members ─────

    public function members(): View
    {
        $members = CommitteeMember::with('user')->orderBy('role')->get();
        $parents = User::where('school_id', $this->schoolId())
            ->whereHas('roles', fn($q) => $q->where('name', 'parent'))
            ->get();
        $staff = User::where('school_id', $this->schoolId())
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'admin']))
            ->get();

        return view('school-admin.committee.members', compact('members', 'parents', 'staff'));
    }

    public function storeMember(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'user_id'      => 'required|integer|exists:users,id',
            'role'         => 'required|string|in:ketua,wakil,sekretaris,bendahara,anggota',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after:period_start',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;

        CommitteeMember::create($data);

        return back()->with('success', 'Anggota komite berhasil ditambahkan.');
    }

    public function updateMember(Request $request, CommitteeMember $member): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'role'         => 'required|string|in:ketua,wakil,sekretaris,bendahara,anggota',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after:period_start',
            'is_active'    => 'boolean',
        ]);

        $member->update($data);

        return back()->with('success', 'Anggota komite diperbarui.');
    }

    public function deleteMember(CommitteeMember $member): \Illuminate\Http\RedirectResponse
    {
        $member->delete();

        return back()->with('success', 'Anggota komite dihapus.');
    }

    // ───── Meetings ─────

    public function meetings(): View
    {
        $meetings = CommitteeMeeting::with(['creator', 'attendances.member.user', 'decisions'])
            ->orderByDesc('meeting_date')
            ->get();

        return view('school-admin.committee.meetings', compact('meetings'));
    }

    public function storeMeeting(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location'     => 'nullable|string|max:255',
            'agenda'       => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['created_by'] = auth()->id();
        $data['status'] = 'scheduled';

        $meeting = CommitteeMeeting::create($data);

        $activeMembers = CommitteeMember::where('is_active', true)->get();
        foreach ($activeMembers as $member) {
            CommitteeAttendance::create([
                'committee_meeting_id' => $meeting->id,
                'committee_member_id'  => $member->id,
                'status'               => 'izin',
            ]);
        }

        return back()->with('success', 'Rapat baru dijadwalkan.');
    }

    public function updateMeeting(Request $request, CommitteeMeeting $meeting): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location'     => 'nullable|string|max:255',
            'agenda'       => 'nullable|string',
            'status'       => 'required|string|in:scheduled,ongoing,completed,cancelled',
            'minutes'      => 'nullable|string',
        ]);

        $meeting->update($data);

        return back()->with('success', 'Rapat diperbarui.');
    }

    public function deleteMeeting(CommitteeMeeting $meeting): \Illuminate\Http\RedirectResponse
    {
        $meeting->delete();

        return back()->with('success', 'Rapat dihapus.');
    }

    public function updateAttendance(Request $request, CommitteeMeeting $meeting): \Illuminate\Http\RedirectResponse
    {
        $attendances = $request->input('attendance', []);
        foreach ($attendances as $memberId => $status) {
            CommitteeAttendance::where('committee_meeting_id', $meeting->id)
                ->where('committee_member_id', $memberId)
                ->update(['status' => $status]);
        }

        return back()->with('success', 'Absensi rapat disimpan.');
    }

    // ───── Decisions ─────

    public function decisions(): View
    {
        $decisions = CommitteeDecision::with('meeting')
            ->orderByDesc('created_at')
            ->get();

        $meetings = CommitteeMeeting::whereIn('status', ['ongoing', 'completed'])
            ->orderByDesc('meeting_date')
            ->get();

        return view('school-admin.committee.decisions', compact('decisions', 'meetings'));
    }

    public function storeDecision(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'committee_meeting_id' => 'required|integer|exists:committee_meetings,id',
            'title'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'decision_type'        => 'required|string|in:kebijakan,anggaran,program,lainnya',
            'voting_result'        => 'nullable|string|in:approved,rejected,deferred',
            'voting_detail'        => 'nullable|json',
            'status'               => 'required|string|in:draft,finalized',
        ]);

        CommitteeDecision::create($data);

        return back()->with('success', 'Keputusan rapat berhasil dicatat.');
    }

    public function updateDecision(Request $request, CommitteeDecision $decision): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'decision_type' => 'required|string|in:kebijakan,anggaran,program,lainnya',
            'voting_result' => 'nullable|string|in:approved,rejected,deferred',
            'voting_detail' => 'nullable|json',
            'status'        => 'required|string|in:draft,finalized',
        ]);

        $decision->update($data);

        return back()->with('success', 'Keputusan rapat diperbarui.');
    }

    public function deleteDecision(CommitteeDecision $decision): \Illuminate\Http\RedirectResponse
    {
        $decision->delete();

        return back()->with('success', 'Keputusan rapat dihapus.');
    }

    // ───── Proposals ─────

    public function proposals(): View
    {
        $proposals = CommitteeProposal::with(['proposer', 'reviewer'])
            ->orderByDesc('created_at')
            ->get();

        return view('school-admin.committee.proposals', compact('proposals'));
    }

    public function storeProposal(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'estimated_budget' => 'nullable|integer|min:0',
        ]);

        $data['school_id']   = $this->schoolId();
        $data['proposer_id'] = auth()->id();
        $data['status']      = 'submitted';

        CommitteeProposal::create($data);

        return back()->with('success', 'Proposal berhasil diajukan.');
    }

    public function reviewProposal(Request $request, CommitteeProposal $proposal): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'status'       => 'required|string|in:reviewed,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        $data['reviewed_by'] = auth()->id();
        $proposal->update($data);

        return back()->with('success', 'Proposal berhasil direview.');
    }

    public function deleteProposal(CommitteeProposal $proposal): \Illuminate\Http\RedirectResponse
    {
        $proposal->delete();

        return back()->with('success', 'Proposal dihapus.');
    }
}
