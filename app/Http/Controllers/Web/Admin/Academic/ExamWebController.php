<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\Mark;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Services\Academic\ItemAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamWebController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $exams = Exam::where('school_id', $schoolId)
            ->with(['classSection.classRoom', 'classSection.section', 'subject'])
            ->when($request->class_section_id, fn ($q) => $q->where('class_section_id', $request->class_section_id))
            ->orderByDesc('start_at')
            ->paginate(20)
            ->withQueryString();

        return view('school-admin.exams.index', [
            'exams'         => $exams,
            'classSections' => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
            'subjects'      => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'class_section_id'  => 'required|exists:class_sections,id',
            'subject_id'        => 'required|exists:subjects,id',
            'type'              => 'required|in:online,offline',
            'start_at'          => 'nullable|date',
            'end_at'            => 'nullable|date|after_or_equal:start_at',
            'duration_minutes'  => 'nullable|integer|min:1|max:600',
            'total_marks'       => 'required|integer|min:1|max:1000',
            'pass_marks'        => 'required|integer|min:0',
        ]);

        $data['school_id'] = $this->schoolId();
        Exam::create($data);
        return back()->with('success', 'Ujian dibuat.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorizeOwn($exam);
        $exam->delete();
        return back()->with('success', 'Ujian dihapus.');
    }

    public function inputMarks(Exam $exam): View
    {
        $this->authorizeOwn($exam);

        $students = Student::where('school_id', $this->schoolId())
            ->where('class_section_id', $exam->class_section_id)
            ->with('user:id,name')
            ->orderBy('admission_no')->get();

        $existing = Mark::where('school_id', $this->schoolId())
            ->where('exam_id', $exam->id)
            ->get()->keyBy('student_id');

        return view('school-admin.exams.marks', compact('exam', 'students', 'existing'));
    }

    public function saveMarks(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeOwn($exam);

        $data = $request->validate([
            'marks'   => 'required|array',
            'marks.*' => 'nullable|integer|min:0|max:'.$exam->total_marks,
        ]);

        $semester = Semester::where('school_id', $this->schoolId())
            ->orderByDesc('id')->first();

        if (!$semester) {
            return back()->withErrors('Belum ada semester. Buat semester dulu di Tahun Ajaran.');
        }

        $schoolId = $this->schoolId();

        DB::transaction(function () use ($data, $exam, $semester, $schoolId) {
            foreach ($data['marks'] as $studentId => $obtainedMarks) {
                if ($obtainedMarks === null || $obtainedMarks === '') continue;

                Mark::updateOrCreate(
                    [
                        'school_id'   => $schoolId,
                        'student_id'  => $studentId,
                        'exam_id'     => $exam->id,
                        'subject_id'  => $exam->subject_id,
                    ],
                    [
                        'semester_id'    => $semester->id,
                        'obtained_marks' => $obtainedMarks,
                        'total_marks'    => $exam->total_marks,
                        'grade'          => $this->grade($obtainedMarks, $exam->total_marks),
                    ]
                );
            }
        });

        return back()->with('success', 'Nilai tersimpan.');
    }

    public function analysis(Exam $exam, ItemAnalysisService $service): View
    {
        $this->authorizeOwn($exam);

        $analysis = $service->analyze($exam);

        return view('school-admin.exams.analysis', [
            'exam'     => $exam,
            'analysis' => $analysis,
        ]);
    }

    private function grade(int $obtained, int $total): string
    {
        $pct = $total > 0 ? ($obtained / $total) * 100 : 0;
        return match (true) {
            $pct >= 90 => 'A',
            $pct >= 80 => 'B',
            $pct >= 70 => 'C',
            $pct >= 60 => 'D',
            default    => 'E',
        };
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
