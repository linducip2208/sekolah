<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\GradeRule;
use App\Models\Academic\GradeSystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradingScaleController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $systems = GradeSystem::where('school_id', $this->schoolId())
            ->with('rules')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('school-admin.grades.index', ['systems' => $systems]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
        ]);

        $system = GradeSystem::create([
            'school_id' => $this->schoolId(),
            'name'      => $data['name'],
            'is_active' => !GradeSystem::where('school_id', $this->schoolId())->exists(),
        ]);

        return back()->with('success', 'Sistem penilaian dibuat: ' . $system->name);
    }

    public function storeRule(Request $request, GradeSystem $system): RedirectResponse
    {
        abort_unless($system->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'grade'       => 'required|string|max:10',
            'min_percent' => 'required|numeric|min:0|max:100',
            'max_percent' => 'required|numeric|min:0|max:100|gte:min_percent',
            'gpa_point'   => 'nullable|numeric|min:0|max:4',
        ]);

        GradeRule::create([
            'grade_system_id' => $system->id,
            'grade'           => $data['grade'],
            'min_percent'     => $data['min_percent'],
            'max_percent'     => $data['max_percent'],
            'gpa_point'       => $data['gpa_point'] ?? 0,
        ]);

        return back()->with('success', 'Rentang nilai ditambahkan.');
    }

    public function deleteRule(GradeRule $rule): RedirectResponse
    {
        abort_unless($rule->gradeSystem?->school_id === $this->schoolId(), 403);
        $rule->delete();
        return back()->with('success', 'Rentang nilai dihapus.');
    }

    public function activate(GradeSystem $system): RedirectResponse
    {
        abort_unless($system->school_id === $this->schoolId(), 403);

        GradeSystem::where('school_id', $this->schoolId())->update(['is_active' => false]);
        $system->update(['is_active' => true]);

        return back()->with('success', 'Sistem penilaian aktif: ' . $system->name);
    }

    public function destroy(GradeSystem $system): RedirectResponse
    {
        abort_unless($system->school_id === $this->schoolId(), 403);
        $system->delete();
        return back()->with('success', 'Sistem penilaian dihapus.');
    }
}
