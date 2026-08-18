<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Staff;
use App\Models\Academic\Subject;
use App\Models\Academic\TeachingJournal;
use App\Models\Curriculum\CurriculumCompetency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = TeachingJournal::where('school_id', $schoolId)
            ->with(['teacher:id,name', 'staff.user:id,name', 'classSection.classRoom', 'classSection.section', 'classRoom', 'subject']);

        if ($request->filled('date_from')) {
            $dateCol = TeachingJournal::getColumnDateName();
            $query->whereDate($dateCol, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $dateCol = TeachingJournal::getColumnDateName();
            $query->whereDate($dateCol, '<=', $request->date_to);
        }
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        } elseif ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $journals = $query->orderByDesc(TeachingJournal::getColumnDateName())->paginate(30)->withQueryString();

        return view('school-admin.academic.teaching-journal', [
            'journals'       => $journals,
            'classSections'  => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
            'classRooms'     => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
            'subjects'       => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'staffs'         => Staff::where('school_id', $schoolId)->with('user:id,name')->get(),
            'competencies'   => CurriculumCompetency::where('school_id', $schoolId)->orderBy('code')->get(['id', 'code', 'description', 'level_type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_section_id'     => 'nullable|exists:class_sections,id',
            'class_room_id'        => 'nullable|exists:class_rooms,id',
            'subject_id'           => 'required|exists:subjects,id',
            'staff_id'             => 'nullable|exists:staffs,id',
            'journal_date'         => 'nullable|date',
            'date'                 => 'nullable|date',
            'class_number'         => 'nullable|integer|min:1|max:8',
            'topic'                => 'nullable|string|max:255',
            'material'             => 'nullable|string',
            'activity'             => 'nullable|string',
            'competency_ids'       => 'nullable|array',
            'competency_ids.*'     => 'exists:curriculum_competencies,id',
            'learning_activity'    => 'nullable|string',
            'attendance_summary'   => 'nullable|string',
            'student_participation'=> 'nullable|string',
            'homework'             => 'nullable|string',
            'notes'                => 'nullable|string',
            'reflection'           => 'nullable|string',
        ]);

        $data['school_id']  = $this->schoolId();
        $data['teacher_id'] = auth()->id();
        $data['staff_id']   = $data['staff_id'] ?? null;
        $data['date']       = $data['date'] ?? $data['journal_date'] ?? now()->toDateString();
        $data['competency_ids'] = $data['competency_ids'] ?? [];
        $data['status']     = 'draft';

        unset($data['journal_date']);

        TeachingJournal::create($data);

        return back()->with('success', 'Jurnal mengajar disimpan.');
    }

    public function update(Request $request, TeachingJournal $journal): RedirectResponse
    {
        abort_unless($journal->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'material'             => 'nullable|string',
            'topic'                => 'nullable|string|max:255',
            'class_number'         => 'nullable|integer|min:1|max:8',
            'activity'             => 'nullable|string',
            'competency_ids'       => 'nullable|array',
            'competency_ids.*'     => 'exists:curriculum_competencies,id',
            'learning_activity'    => 'nullable|string',
            'attendance_summary'   => 'nullable|string',
            'student_participation'=> 'nullable|string',
            'homework'             => 'nullable|string',
            'notes'                => 'nullable|string',
            'reflection'           => 'nullable|string',
        ]);

        $data['competency_ids'] = $data['compatency_ids'] ?? $journal->competency_ids;

        $journal->update($data);

        return back()->with('success', 'Jurnal mengajar diperbarui.');
    }

    public function publish(TeachingJournal $journal): RedirectResponse
    {
        abort_unless($journal->school_id === $this->schoolId(), 403);

        $journal->update(['status' => 'published']);

        return back()->with('success', 'Jurnal mengajar dipublikasikan.');
    }

    public function destroy(TeachingJournal $journal): RedirectResponse
    {
        abort_unless($journal->school_id === $this->schoolId(), 403);
        $journal->delete();
        return back()->with('success', 'Jurnal mengajar dihapus.');
    }
}
