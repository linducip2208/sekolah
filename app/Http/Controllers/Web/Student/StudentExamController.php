<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamResult;
use App\Models\Academic\Student;
use App\Services\Academic\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentExamController extends Controller
{
    public function __construct(private ExamService $service) {}

    private function student(): Student
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404, 'Profil siswa tidak ditemukan.');
        return $student;
    }

    public function index(): View
    {
        $student = $this->student();

        $exams = Exam::where('school_id', $student->school_id)
            ->where('class_section_id', $student->class_section_id)
            ->where('type', 'online')
            ->with('subject:id,name')
            ->orderByDesc('start_at')
            ->get();

        $results = ExamResult::where('student_id', $student->id)
            ->get()
            ->keyBy('exam_id');

        return view('student-portal.exams.index', compact('student', 'exams', 'results'));
    }

    public function take(Exam $exam): View|RedirectResponse
    {
        $student = $this->student();
        abort_if($exam->school_id !== $student->school_id || $exam->class_section_id !== $student->class_section_id, 403);
        abort_if($exam->type !== 'online', 404, 'Ujian ini bukan ujian online.');

        $result = $this->service->startExam($exam->id);

        if (in_array($result->status, ['passed', 'failed'], true)) {
            return redirect()->route('student.exams.result', $exam);
        }

        $exam     = $result->exam;
        $duration = (int) ($exam->duration_minutes ?? 60);
        $deadline = $result->started_at?->addMinutes($duration);

        return view('student-portal.exams.take', compact('student', 'exam', 'result', 'duration', 'deadline'));
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $student = $this->student();
        abort_if($exam->school_id !== $student->school_id || $exam->class_section_id !== $student->class_section_id, 403);

        $this->service->submitExam($exam->id, $request->input('answers', []));

        return redirect()->route('student.exams.result', $exam)->with('success', 'Ujian telah dikumpulkan.');
    }

    public function result(Exam $exam): View
    {
        $student = $this->student();

        $result = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $exam->load('questions');

        return view('student-portal.exams.result', compact('student', 'exam', 'result'));
    }
}
