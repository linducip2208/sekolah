<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\LeaderboardConfig;
use App\Models\Academic\Student;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request, LeaderboardService $service): View
    {
        $schoolId = $this->schoolId();
        $configType = $request->query('period', 'monthly');
        $classSectionId = $request->query('class_section_id');

        $config = $service->getConfig($schoolId, $configType);
        $rankings = $service->calculateRankings($schoolId, $configType, $classSectionId ? (int) $classSectionId : null);
        $classSections = ClassSection::where('school_id', $schoolId)->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get();

        $periods = [
            'weekly'   => 'Mingguan',
            'monthly'  => 'Bulanan',
            'semester' => 'Semester',
        ];

        $top3 = array_slice($rankings, 0, 3);
        $remaining = array_slice($rankings, 3);

        return view('school-admin.academic.leaderboard.index', [
            'rankings'       => $rankings,
            'top3'           => $top3,
            'remaining'      => $remaining,
            'configType'     => $configType,
            'classSectionId' => $classSectionId,
            'config'         => $config,
            'classSections'  => $classSections,
            'students'       => $students,
            'periods'        => $periods,
            'periodLabel'    => $periods[$configType] ?? 'Bulanan',
        ]);
    }

    public function saveConfig(Request $request, LeaderboardService $service): RedirectResponse
    {
        $data = $request->validate([
            'config_type'            => 'required|in:weekly,monthly,semester',
            'is_active'              => 'nullable|boolean',
            'weight_academic'        => 'required|integer|min:0|max:100',
            'weight_attendance'      => 'required|integer|min:0|max:100',
            'weight_extracurricular' => 'required|integer|min:0|max:100',
            'weight_discipline'      => 'required|integer|min:0|max:100',
        ]);

        $total = (int) $data['weight_academic']
            + (int) $data['weight_attendance']
            + (int) $data['weight_extracurricular']
            + (int) $data['weight_discipline'];

        if ($total !== 100) {
            return back()->withErrors(['weight_academic' => 'Total bobot harus 100%. Saat ini: ' . $total . '%'])->withInput();
        }

        $service->saveConfig($this->schoolId(), $data['config_type'], $data);

        return back()->with('success', 'Konfigurasi leaderboard disimpan.');
    }

    public function awardPoints(Request $request, LeaderboardService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'points'         => 'required|integer|min:1|max:1000',
            'point_type'     => 'required|in:academic,attendance,extracurricular,discipline,other',
            'reason'         => 'required|string|max:500',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
        ]);

        $student = Student::findOrFail($data['student_id']);
        abort_unless($student->school_id === $this->schoolId(), 403);

        $service->awardPoints(
            $this->schoolId(),
            $data['student_id'],
            (int) $data['points'],
            $data['point_type'],
            $data['reason'],
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Poin diberikan kepada ' . ($student->user?->name ?? 'siswa') . '.');
    }

    public function awardPointsBatch(Request $request, LeaderboardService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'exists:students,id',
            'points'         => 'required|integer|min:1|max:1000',
            'point_type'     => 'required|in:academic,attendance,extracurricular,discipline,other',
            'reason'         => 'required|string|max:500',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
        ]);

        $schoolId = $this->schoolId();
        $count = 0;
        foreach ($data['student_ids'] as $stuId) {
            $student = Student::find($stuId);
            if (!$student || $student->school_id !== $schoolId) {
                continue;
            }
            $service->awardPoints(
                $schoolId, $student->id, (int) $data['points'],
                $data['point_type'], $data['reason'],
                $data['reference_type'] ?? null, $data['reference_id'] ?? null, auth()->id()
            );
            $count++;
        }

        return back()->with('success', "Poin diberikan kepada {$count} siswa.");
    }

    public function deductPoints(Request $request, LeaderboardService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'points'         => 'required|integer|min:1|max:1000',
            'point_type'     => 'required|in:academic,attendance,extracurricular,discipline,other',
            'reason'         => 'required|string|max:500',
            'reference_type' => 'nullable|string|max:100',
            'reference_id'   => 'nullable|integer',
        ]);

        $student = Student::findOrFail($data['student_id']);
        abort_unless($student->school_id === $this->schoolId(), 403);

        $service->deductPoints(
            $this->schoolId(),
            $data['student_id'],
            (int) $data['points'],
            $data['point_type'],
            $data['reason'],
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Poin dikurangi dari ' . ($student->user?->name ?? 'siswa') . '.');
    }

    public function history(Request $request): View
    {
        $schoolId = $this->schoolId();
        $studentId = $request->query('student_id');
        $pointType = $request->query('point_type');
        $period = $request->query('period', 'monthly');

        $service = app(LeaderboardService::class);
        [$periodStart, $periodEnd] = $service->getPeriodRange($period);

        $query = \App\Models\Academic\StudentPoint::where('school_id', $schoolId)
            ->with(['student.user:id,name', 'awardedBy:id,name'])
            ->whereBetween('awarded_at', [$periodStart, $periodEnd])
            ->orderByDesc('awarded_at');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        if ($pointType) {
            $query->where('point_type', $pointType);
        }

        $history = $query->paginate(30)->appends($request->query());
        $students = Student::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get();

        return view('school-admin.academic.leaderboard.history', [
            'history'  => $history,
            'students' => $students,
            'period'   => $period,
            'pointType' => $pointType,
            'studentId' => $studentId,
        ]);
    }

    public function signage(int $schoolId, Request $request, LeaderboardService $service): View
    {
        $configType = $request->query('period', 'monthly');
        $classSectionId = $request->query('class_section_id');

        $rankings = $service->calculateRankings($schoolId, $configType, $classSectionId ? (int) $classSectionId : null);
        $school = \App\Models\School::find($schoolId);

        if (!$school || !$school->is_active) {
            abort(404);
        }

        $top10 = array_slice($rankings, 0, 10);

        return view('leaderboard.signage', [
            'rankings'  => $top10,
            'school'    => $school,
            'configType' => $configType,
            'periodLabel' => match ($configType) {
                'weekly'   => 'Minggu Ini',
                'monthly'  => 'Bulan Ini',
                'semester' => 'Semester Ini',
                default    => 'Bulan Ini',
            },
        ]);
    }

    public function syncFromSources(LeaderboardService $service): RedirectResponse
    {
        $result = $service->syncPointsFromSources($this->schoolId());

        if (! empty($result['errors'])) {
            return back()->with('error', 'Gagal sinkronisasi: ' . implode(', ', $result['errors']));
        }

        return back()->with('success', "Sinkronisasi berhasil. {$result['synced']} poin dibuat dari data akademik.");
    }
}
