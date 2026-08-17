<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\TeachingJournal;
use App\Models\Curriculum\CurriculumCompetency;
use App\Services\Academic\TeachingJournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    public function __construct(private TeachingJournalService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $journals = $this->service->listForSchool($schoolId, $request->teacher_id ? (int) $request->teacher_id : null);

        return view('school-admin.academic.teaching-journal', [
            'journals'    => $journals,
            'classSections' => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
            'subjects'    => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'competencies'=> CurriculumCompetency::where('school_id', $schoolId)->orderBy('code')->get(['id', 'code', 'description', 'level_type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_section_id'     => 'required|exists:class_sections,id',
            'subject_id'           => 'required|exists:subjects,id',
            'journal_date'         => 'required|date',
            'material'             => 'nullable|string',
            'competency_ids'       => 'nullable|array',
            'competency_ids.*'     => 'exists:curriculum_competencies,id',
            'learning_activity'    => 'nullable|string',
            'attendance_summary'   => 'nullable|string',
            'student_participation'=> 'nullable|string',
            'homework'             => 'nullable|string',
            'notes'                => 'nullable|string',
            'reflection'           => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['competency_ids'] = $data['competency_ids'] ?? [];

        $this->service->create($data, auth()->id());

        return back()->with('success', 'Jurnal mengajar disimpan.');
    }

    public function update(Request $request, TeachingJournal $journal): RedirectResponse
    {
        abort_unless($journal->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'material'             => 'nullable|string',
            'competency_ids'       => 'nullable|array',
            'competency_ids.*'     => 'exists:curriculum_competencies,id',
            'learning_activity'    => 'nullable|string',
            'attendance_summary'   => 'nullable|string',
            'student_participation'=> 'nullable|string',
            'homework'             => 'nullable|string',
            'notes'                => 'nullable|string',
            'reflection'           => 'nullable|string',
        ]);

        $data['competency_ids'] = $data['competency_ids'] ?? [];

        $this->service->update($journal, $data);

        return back()->with('success', 'Jurnal mengajar diperbarui.');
    }

    public function destroy(TeachingJournal $journal): RedirectResponse
    {
        abort_unless($journal->school_id === $this->schoolId(), 403);
        $journal->delete();
        return back()->with('success', 'Jurnal mengajar dihapus.');
    }
}
