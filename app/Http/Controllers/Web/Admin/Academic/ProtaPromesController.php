<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ProtaProgram;
use App\Models\Academic\PromesProgram;
use App\Models\Academic\Semester;
use App\Models\Academic\Staff;
use App\Models\Academic\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProtaPromesController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // ─── PROTA ───────────────────────────────────────────────

    public function protaIndex(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = ProtaProgram::where('school_id', $schoolId)
            ->with(['staff.user:id,name', 'subject:id,name', 'academicYear:id,name']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        return view('school-admin.academic.prota', [
            'programs'      => $query->orderByDesc('created_at')->paginate(30)->withQueryString(),
            'staffs'        => Staff::where('school_id', $schoolId)->with('user:id,name')->get(),
            'subjects'      => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'academicYears' => AcademicYear::where('school_id', $schoolId)->orderByDesc('name')->get(),
        ]);
    }

    public function protaStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'          => 'required|exists:staffs,id',
            'subject_id'        => 'required|exists:subjects,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'competencies'      => 'nullable|string',
            'target_completion' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['competencies'] = $this->parseLines($data['competencies'] ?? '');
        $data['target_completion'] = $this->parseLines($data['target_completion'] ?? '');

        ProtaProgram::create($data);

        return back()->with('success', 'PROTA ditambahkan.');
    }

    public function protaUpdate(Request $request, ProtaProgram $prota): RedirectResponse
    {
        abort_unless($prota->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'staff_id'          => 'required|exists:staffs,id',
            'subject_id'        => 'required|exists:subjects,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'competencies'      => 'nullable|string',
            'target_completion' => 'nullable|string',
        ]);

        $data['competencies'] = $this->parseLines($data['competencies'] ?? '');
        $data['target_completion'] = $this->parseLines($data['target_completion'] ?? '');

        $prota->update($data);

        return back()->with('success', 'PROTA diperbarui.');
    }

    public function protaDestroy(ProtaProgram $prota): RedirectResponse
    {
        abort_unless($prota->school_id === $this->schoolId(), 403);
        $prota->delete();
        return back()->with('success', 'PROTA dihapus.');
    }

    // ─── PROMES ──────────────────────────────────────────────

    public function promesIndex(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = PromesProgram::where('school_id', $schoolId)
            ->with(['staff.user:id,name', 'subject:id,name', 'semester:id,name,academic_year_id']);

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        return view('school-admin.academic.promes', [
            'programs'  => $query->orderBy('week_number')->paginate(30)->withQueryString(),
            'staffs'    => Staff::where('school_id', $schoolId)->with('user:id,name')->get(),
            'subjects'  => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'semesters' => Semester::where('school_id', $schoolId)
                ->with('academicYear:id,name')
                ->orderByDesc('start_date')
                ->get(),
        ]);
    }

    public function promesStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'             => 'required|exists:staffs,id',
            'subject_id'           => 'required|exists:subjects,id',
            'semester_id'          => 'required|exists:semesters,id',
            'week_number'          => 'required|integer|min:1|max:20',
            'activity_description' => 'nullable|string',
            'allocation_hours'     => 'nullable|integer|min:0|max:100',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['status']    = 'draft';

        PromesProgram::create($data);

        return back()->with('success', 'PROMES ditambahkan.');
    }

    public function promesUpdate(Request $request, PromesProgram $promes): RedirectResponse
    {
        abort_unless($promes->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'staff_id'             => 'required|exists:staffs,id',
            'subject_id'           => 'required|exists:subjects,id',
            'semester_id'          => 'required|exists:semesters,id',
            'week_number'          => 'required|integer|min:1|max:20',
            'activity_description' => 'nullable|string',
            'allocation_hours'     => 'nullable|integer|min:0|max:100',
            'status'               => 'nullable|in:draft,approved',
        ]);

        $promes->update($data);

        return back()->with('success', 'PROMES diperbarui.');
    }

    public function promesDestroy(PromesProgram $promes): RedirectResponse
    {
        abort_unless($promes->school_id === $this->schoolId(), 403);
        $promes->delete();
        return back()->with('success', 'PROMES dihapus.');
    }

    private function parseLines(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($l) => trim($l))
            ->filter(fn ($l) => $l !== '')
            ->values()
            ->all();
    }
}
