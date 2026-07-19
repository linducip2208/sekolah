<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Osis\OsisCandidate;
use App\Models\Osis\OsisElection;
use App\Models\Osis\OsisProgram;
use App\Models\Osis\OsisVote;
use App\Services\OsisElectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OsisStudentController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function __construct(
        private OsisElectionService $electionService
    ) {}

    public function election(): View
    {
        $activeElection = OsisElection::with(['candidates.student.user'])
            ->where('status', 'voting')
            ->latest()
            ->first();

        $userId = auth()->id();

        $hasVoted = false;
        if ($activeElection) {
            $hasVoted = $this->electionService->hasAlreadyVoted($activeElection, $userId);
        }

        $recentElections = OsisElection::with(['candidates.student.user'])
            ->whereIn('status', ['completed', 'voting'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('student-portal.osis.election', compact('activeElection', 'hasVoted', 'recentElections'));
    }

    public function castVote(Request $request, OsisElection $election): \Illuminate\Http\RedirectResponse
    {
        $userId = auth()->id();

        $candidateIds = $request->input('candidate_ids', []);
        if (empty($candidateIds)) {
            return back()->withErrors(['candidate' => 'Mohon pilih kandidat.']);
        }

        $results = [];
        foreach ($candidateIds as $candidateId) {
            $result = $this->electionService->castVote($election, $userId, (int) $candidateId);
            $results[] = $result;
        }

        $successes = count(array_filter($results, fn($r) => $r['success'] ?? false));

        if ($successes > 0) {
            return redirect()->route('student.osis.results', $election->id)
                ->with('success', 'Suara Anda berhasil tercatat!');
        }

        return back()->withErrors(['candidate' => $results[0]['error'] ?? 'Gagal mencatat suara.']);
    }

    public function results(int $electionId): View
    {
        $election = OsisElection::with(['candidates.student.user' => function ($q) {
            $q->orderByDesc('vote_count');
        }])->findOrFail($electionId);

        $winners = $this->electionService->generateWinnerList($election);
        $totalVoters = $this->electionService->getTotalVoters($election);
        $hasVoted = $this->electionService->hasAlreadyVoted($election, auth()->id());

        return view('student-portal.osis.results', compact('election', 'winners', 'totalVoters', 'hasVoted'));
    }

    public function programs(): View
    {
        $programs = OsisProgram::with('election')
            ->orderByDesc('created_at')
            ->get();

        return view('student-portal.osis.programs', compact('programs'));
    }

    public function proposeProgram(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget'      => 'nullable|integer|min:0',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['status'] = 'planned';

        OsisProgram::create($data);

        return back()->with('success', 'Program berhasil diusulkan!');
    }
}
