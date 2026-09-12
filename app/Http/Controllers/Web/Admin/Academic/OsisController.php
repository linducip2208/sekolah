<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Student;
use App\Models\Osis\OsisCandidate;
use App\Models\Osis\OsisElection;
use App\Models\Osis\OsisProgram;
use App\Models\Osis\OsisVote;
use App\Services\OsisElectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OsisController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function __construct(
        private OsisElectionService $electionService
    ) {}

    public function index(): View
    {
        $schoolId = $this->schoolId();
        $elections = OsisElection::where('school_id', $schoolId)
            ->with(['academicYear', 'candidates.student.user'])
            ->orderByDesc('created_at')
            ->get();

        $activeElection = OsisElection::where('school_id', $schoolId)
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();
        $academicYears = AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get();

        return view('school-admin.osis.index', compact('elections', 'activeElection', 'academicYears'));
    }

    public function storeElection(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title'                 => 'required|string|max:255',
            'academic_year_id'      => 'nullable|integer|exists:academic_years,id',
            'nomination_start'      => 'nullable|date',
            'nomination_end'        => 'nullable|date|after:nomination_start',
            'voting_start'          => 'nullable|date|after:nomination_end',
            'voting_end'            => 'nullable|date|after:voting_start',
            'positions'             => 'required|array|min:1',
            'positions.*'           => 'string|max:100',
            'max_votes_per_student' => 'nullable|integer|min:1',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['status'] = 'setup';
        $data['positions'] = $this->normalizePositions($data['positions']);

        OsisElection::create($data);

        return back()->with('success', 'Pemilihan OSIS berhasil dibuat.');
    }

    public function updateElection(Request $request, OsisElection $election): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwn($election);
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'nomination_start' => 'nullable|date',
            'nomination_end'   => 'nullable|date|after:nomination_start',
            'voting_start'     => 'nullable|date|after:nomination_end',
            'voting_end'       => 'nullable|date|after:voting_start',
            'status'           => 'required|string|in:setup,nomination,voting,completed',
            'positions'        => 'required|array|min:1',
            'positions.*'      => 'string|max:100',
        ]);
        $data['positions'] = $this->normalizePositions($data['positions']);

        $election->update($data);

        return back()->with('success', 'Pemilihan OSIS diperbarui.');
    }

    public function deleteElection(OsisElection $election): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwn($election);
        $election->delete();

        return back()->with('success', 'Pemilihan OSIS dihapus.');
    }

    // ───── Candidates ─────

    public function candidates(OsisElection $election): View
    {
        $this->authorizeOwn($election);
        $election->load('candidates.student.user');
        $students = Student::where('school_id', $this->schoolId())->with('user')->get();

        return view('school-admin.osis.candidates', compact('election', 'students'));
    }

    public function storeCandidate(Request $request, OsisElection $election): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwn($election);
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'position'   => 'required|string|max:100',
            'vision'     => 'nullable|string',
            'mission'    => 'nullable|string',
        ]);

        $data['osis_election_id'] = $election->id;
        $data['status'] = 'registered';
        abort_unless(Student::where('school_id', $this->schoolId())->whereKey($data['student_id'])->exists(), 422, 'Siswa bukan bagian dari sekolah ini.');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('osis-photos', 'public');
        }

        OsisCandidate::create($data);

        return back()->with('success', 'Kandidat berhasil didaftarkan.');
    }

    public function approveCandidate(OsisCandidate $candidate): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeCandidate($candidate);
        $candidate->update(['status' => 'approved']);

        return back()->with('success', 'Kandidat disetujui.');
    }

    public function disqualifyCandidate(Request $request, OsisCandidate $candidate): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeCandidate($candidate);
        $candidate->update(['status' => 'disqualified']);

        return back()->with('success', 'Kandidat didiskualifikasi.');
    }

    public function deleteCandidate(OsisCandidate $candidate): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeCandidate($candidate);
        $candidate->delete();

        return back()->with('success', 'Kandidat dihapus.');
    }

    // ───── Results (Live Counting) ─────

    public function results(OsisElection $election): View
    {
        $this->authorizeOwn($election);
        $election->load(['candidates.student.user', 'candidates' => function ($q) {
            $q->orderByDesc('vote_count');
        }]);

        $winners = $this->electionService->generateWinnerList($election);
        $totalVoters = $this->electionService->getTotalVoters($election);

        return view('school-admin.osis.results', compact('election', 'winners', 'totalVoters'));
    }

    public function liveVotes(OsisElection $election): \Illuminate\Http\JsonResponse
    {
        $this->authorizeOwn($election);
        $candidates = OsisCandidate::where('osis_election_id', $election->id)
            ->with('student.user')
            ->orderByDesc('vote_count')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->student->user->name ?? 'Unknown',
                'position'   => $c->position,
                'vote_count' => $c->vote_count,
                'status'     => $c->status,
            ]);

        $totalVoters = $this->electionService->getTotalVoters($election);

        return response()->json([
            'candidates'   => $candidates,
            'total_voters' => $totalVoters,
        ]);
    }

    public function finalizeResults(OsisElection $election): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwn($election);
        $election->update(['status' => 'completed']);

        return back()->with('success', 'Hasil pemilihan sudah difinalisasi.');
    }

    // ───── Programs ─────

    public function programs(): View
    {
        $schoolId = $this->schoolId();
        $programs = OsisProgram::where('school_id', $schoolId)->with('election')
            ->orderByDesc('created_at')
            ->get();

        $elections = OsisElection::where('school_id', $schoolId)->where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();

        return view('school-admin.osis.programs', compact('programs', 'elections'));
    }

    public function storeProgram(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'osis_election_id' => 'nullable|integer|exists:osis_elections,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'budget'           => 'nullable|integer|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after:start_date',
            'progress_notes'   => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['status'] = 'planned';
        if (! empty($data['osis_election_id'])) {
            OsisElection::where('school_id', $this->schoolId())->findOrFail($data['osis_election_id']);
        }

        OsisProgram::create($data);

        return back()->with('success', 'Program OSIS berhasil ditambahkan.');
    }

    public function updateProgram(Request $request, OsisProgram $program): \Illuminate\Http\RedirectResponse
    {
        abort_unless($program->school_id === $this->schoolId(), 403);
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'budget'         => 'nullable|integer|min:0',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after:start_date',
            'status'         => 'required|string|in:planned,ongoing,completed,cancelled',
            'progress_notes' => 'nullable|string',
        ]);

        $program->update($data);

        return back()->with('success', 'Program OSIS diperbarui.');
    }

    public function deleteProgram(OsisProgram $program): \Illuminate\Http\RedirectResponse
    {
        abort_unless($program->school_id === $this->schoolId(), 403);
        $program->delete();

        return back()->with('success', 'Program OSIS dihapus.');
    }

    private function authorizeOwn(OsisElection $election): void
    {
        abort_unless($election->school_id === $this->schoolId(), 403);
    }

    private function authorizeCandidate(OsisCandidate $candidate): void
    {
        abort_unless($candidate->election?->school_id === $this->schoolId(), 403);
    }

    private function normalizePositions(array $positions): array
    {
        return collect($positions)
            ->flatMap(fn ($position) => preg_split('/[,\r\n]+/', (string) $position))
            ->map(fn ($position) => trim($position))
            ->filter()
            ->values()
            ->all();
    }
}
