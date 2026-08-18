<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Rubric;
use App\Models\Academic\RubricCriterion;
use App\Models\Academic\RubricLevel;
use App\Models\Academic\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RubricController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = Rubric::where('school_id', $schoolId)
            ->with(['subject:id,name', 'criteria.levels']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        return view('school-admin.academic.rubrics', [
            'rubrics'  => $query->orderBy('name')->paginate(30)->withQueryString(),
            'subjects' => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id'  => 'nullable|exists:subjects,id',
            'max_score'   => 'nullable|integer|min:1|max:100',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['max_score'] = $data['max_score'] ?? 4;

        Rubric::create($data);

        return back()->with('success', 'Rubrik ditambahkan.');
    }

    public function show(Rubric $rubric): View
    {
        abort_unless($rubric->school_id === $this->schoolId(), 403);

        $rubric->load(['subject:id,name', 'criteria.levels']);

        return view('school-admin.academic.rubric-detail', [
            'rubric'   => $rubric,
            'subjects' => Subject::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Rubric $rubric): RedirectResponse
    {
        abort_unless($rubric->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id'  => 'nullable|exists:subjects,id',
            'max_score'   => 'nullable|integer|min:1|max:100',
        ]);

        $rubric->update($data);

        return back()->with('success', 'Rubrik diperbarui.');
    }

    public function destroy(Rubric $rubric): RedirectResponse
    {
        abort_unless($rubric->school_id === $this->schoolId(), 403);
        $rubric->delete();
        return back()->with('success', 'Rubrik dihapus.');
    }

    // ─── CRITERIA ────────────────────────────────────────────

    public function storeCriterion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rubric_id'    => 'required|exists:rubrics,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'weight'       => 'nullable|integer|min:1|max:10',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $data['school_id']  = $this->schoolId();
        $data['weight']     = $data['weight'] ?? 1;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        RubricCriterion::create($data);

        return back()->with('success', 'Kriteria rubrik ditambahkan.');
    }

    public function updateCriterion(Request $request, RubricCriterion $criterion): RedirectResponse
    {
        abort_unless($criterion->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight'      => 'nullable|integer|min:1|max:10',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $criterion->update($data);

        return back()->with('success', 'Kriteria rubrik diperbarui.');
    }

    public function destroyCriterion(RubricCriterion $criterion): RedirectResponse
    {
        abort_unless($criterion->school_id === $this->schoolId(), 403);
        $criterion->delete();
        return back()->with('success', 'Kriteria rubrik dihapus.');
    }

    // ─── LEVELS ──────────────────────────────────────────────

    public function storeLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'criteria_id' => 'required|exists:rubric_criteria,id',
            'level_name'  => 'required|string|max:255',
            'score'       => 'required|integer|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();

        RubricLevel::create($data);

        return back()->with('success', 'Level rubrik ditambahkan.');
    }

    public function updateLevel(Request $request, RubricLevel $level): RedirectResponse
    {
        abort_unless($level->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'level_name'  => 'required|string|max:255',
            'score'       => 'required|integer|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $level->update($data);

        return back()->with('success', 'Level rubrik diperbarui.');
    }

    public function destroyLevel(RubricLevel $level): RedirectResponse
    {
        abort_unless($level->school_id === $this->schoolId(), 403);
        $level->delete();
        return back()->with('success', 'Level rubrik dihapus.');
    }
}
