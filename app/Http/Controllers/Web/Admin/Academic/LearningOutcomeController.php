<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\LearningOutcome;
use App\Models\Academic\LearningObjective;
use App\Models\Academic\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningOutcomeController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = LearningOutcome::where('school_id', $schoolId)
            ->with(['subject:id,name', 'objectives']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        return view('school-admin.academic.learning-outcomes', [
            'outcomes'  => $query->orderBy('sort_order')->orderBy('code')->paginate(40)->withQueryString(),
            'subjects'  => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'stages'    => ['SD', 'SMP', 'SMA'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'stage'      => 'required|in:SD,SMP,SMA',
            'description'=> 'required|string',
            'code'       => 'required|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['school_id']  = $this->schoolId();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        LearningOutcome::create($data);

        return back()->with('success', 'Capaian Pembelajaran (CP) ditambahkan.');
    }

    public function update(Request $request, LearningOutcome $outcome): RedirectResponse
    {
        abort_unless($outcome->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'stage'      => 'required|in:SD,SMP,SMA',
            'description'=> 'required|string',
            'code'       => 'required|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $outcome->update($data);

        return back()->with('success', 'CP diperbarui.');
    }

    public function destroy(LearningOutcome $outcome): RedirectResponse
    {
        abort_unless($outcome->school_id === $this->schoolId(), 403);
        $outcome->delete();
        return back()->with('success', 'CP dihapus.');
    }

    // ─── TP (Learning Objectives) ────────────────────────────

    public function storeObjective(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'description'         => 'required|string',
            'code'                => 'required|string|max:30',
            'sort_order'          => 'nullable|integer|min:0',
        ]);

        $data['school_id']  = $this->schoolId();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        LearningObjective::create($data);

        return back()->with('success', 'Tujuan Pembelajaran (TP) ditambahkan.');
    }

    public function updateObjective(Request $request, LearningObjective $objective): RedirectResponse
    {
        abort_unless($objective->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'description' => 'required|string',
            'code'        => 'required|string|max:30',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $objective->update($data);

        return back()->with('success', 'TP diperbarui.');
    }

    public function destroyObjective(LearningObjective $objective): RedirectResponse
    {
        abort_unless($objective->school_id === $this->schoolId(), 403);
        $objective->delete();
        return back()->with('success', 'TP dihapus.');
    }
}
