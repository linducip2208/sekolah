<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentQuestion;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\Lesson;
use App\Services\AutoGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();
        $assignments = Assignment::where('school_id', $schoolId)
            ->with(['lesson:id,title', 'questions'])
            ->withCount('submissions')
            ->orderByDesc('due_date')->paginate(20);
        $lessons = Lesson::where('school_id', $schoolId)->orderBy('title')->get();

        return view('school-admin.classroom.assignments.index', compact('assignments', 'lessons'));
    }

    public function create(): View
    {
        $lessons = Lesson::where('school_id', $this->schoolId())->orderBy('title')->get();
        return view('school-admin.classroom.assignments.create', compact('lessons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lesson_id'            => 'required|exists:lessons,id',
            'title'                => 'required|string|max:255',
            'instructions'         => 'nullable|string',
            'due_date'             => 'required|date',
            'total_marks'          => 'required|integer|min:1|max:1000',
            'question_type'        => 'required|in:essay,multiple_choice,mixed',
            'allow_late_submission' => 'boolean',
            'max_file_size_mb'     => 'nullable|integer|min:1|max:100',
            'questions'            => 'nullable|array',
            'questions.*.question_text' => 'required|string|max:2000',
            'questions.*.question_type' => 'required|in:mcq,essay,short_answer,file_upload',
            'questions.*.options'       => 'nullable|string',
            'questions.*.correct_answer'=> 'nullable|string',
            'questions.*.points'        => 'required|integer|min:1|max:100',
        ]);

        $assignment = Assignment::create([
            'school_id'             => $this->schoolId(),
            'lesson_id'             => $data['lesson_id'],
            'title'                 => $data['title'],
            'instructions'          => $data['instructions'] ?? null,
            'due_date'              => $data['due_date'],
            'total_marks'           => $data['total_marks'],
            'question_type'         => $data['question_type'],
            'auto_grade'            => in_array($data['question_type'], ['multiple_choice', 'mixed']),
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'max_file_size_mb'      => $data['max_file_size_mb'] ?? 10,
            'answer_key'            => null,
        ]);

        $answerKey = [];

        if (!empty($data['questions'])) {
            foreach ($data['questions'] as $i => $q) {
                $options = null;
                if ($q['question_type'] === 'mcq' && !empty($q['options'])) {
                    $lines = explode("\n", str_replace("\r", '', $q['options']));
                    $options = array_values(array_filter(array_map('trim', $lines)));
                }

                AssignmentQuestion::create([
                    'assignment_id'   => $assignment->id,
                    'question_number' => $i + 1,
                    'question_text'   => $q['question_text'],
                    'question_type'   => $q['question_type'],
                    'options'         => $options,
                    'correct_answer'  => $q['correct_answer'] ?? null,
                    'points'          => (int) $q['points'],
                ]);

                if ($q['question_type'] === 'mcq' && !empty($q['correct_answer'])) {
                    $answerKey[$i + 1] = $q['correct_answer'];
                }
            }
        }

        if (!empty($answerKey)) {
            $assignment->update(['answer_key' => $answerKey]);
        }

        return redirect()->route('admin.assignments.index')->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorizeOwn($assignment);
        $assignment->load('questions');
        $lessons = Lesson::where('school_id', $this->schoolId())->orderBy('title')->get();

        return view('school-admin.classroom.assignments.create', compact('assignment', 'lessons'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorizeOwn($assignment);

        $data = $request->validate([
            'lesson_id'            => 'required|exists:lessons,id',
            'title'                => 'required|string|max:255',
            'instructions'         => 'nullable|string',
            'due_date'             => 'required|date',
            'total_marks'          => 'required|integer|min:1|max:1000',
            'question_type'        => 'required|in:essay,multiple_choice,mixed',
            'allow_late_submission' => 'boolean',
            'max_file_size_mb'     => 'nullable|integer|min:1|max:100',
        ]);

        $assignment->update([
            'lesson_id'             => $data['lesson_id'],
            'title'                 => $data['title'],
            'instructions'          => $data['instructions'] ?? null,
            'due_date'              => $data['due_date'],
            'total_marks'           => $data['total_marks'],
            'question_type'         => $data['question_type'],
            'auto_grade'            => in_array($data['question_type'], ['multiple_choice', 'mixed']),
            'allow_late_submission' => $request->boolean('allow_late_submission'),
            'max_file_size_mb'      => $data['max_file_size_mb'] ?? 10,
        ]);

        return redirect()->route('admin.assignments.index')->with('success', 'Tugas diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorizeOwn($assignment);
        $assignment->delete();
        return back()->with('success', 'Tugas dihapus.');
    }

    public function submissions(Assignment $assignment, Request $request): View
    {
        $this->authorizeOwn($assignment);
        $assignment->load(['questions', 'lesson.subject:id,name']);

        $query = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->with('student.user:id,name', 'student.classSection.classRoom');

        if ($request->has('search')) {
            $query->whereHas('student.user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $submissions = $query->paginate(30)->appends($request->query());

        return view('school-admin.classroom.assignments.submissions', compact('assignment', 'submissions'));
    }

    public function grade(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $assignment = $submission->assignment;
        $this->authorizeOwn($assignment);

        $data = $request->validate([
            'marks'    => 'required|numeric|min:0|max:' . $assignment->total_marks,
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'marks'    => $data['marks'],
            'feedback' => $data['feedback'],
        ]);

        return back()->with('success', 'Nilai disimpan.');
    }

    public function destroySubmission(AssignmentSubmission $submission): RedirectResponse
    {
        $assignment = $submission->assignment;
        $this->authorizeOwn($assignment);
        $submission->delete();
        return back()->with('success', 'Pengumpulan dihapus.');
    }

    // Student-facing: do assignment
    public function doAssignment(Assignment $assignment): View
    {
        $student = \App\Models\Academic\Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $assignment->load('questions');
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)->first();

        return view('student-portal.assignments.do', compact('assignment', 'student', 'existingSubmission'));
    }

    public function submitAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = \App\Models\Academic\Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)->first();

        if ($existingSubmission) {
            return back()->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }

        $answers = $request->input('answers', []);
        $file = $request->file('file');
        $filePath = null;

        if ($file) {
            $request->validate(['file' => 'file|max:' . (($assignment->max_file_size_mb ?? 10) * 1024)]);
            $filePath = $file->store('assignment-submissions', 'public');
        }

        $isLate = $assignment->due_date && now()->gt($assignment->due_date);

        if ($isLate && !$assignment->allow_late_submission) {
            return back()->with('error', 'Batas waktu pengumpulan sudah lewat.');
        }

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'answer'        => json_encode($answers),
            'file'          => $filePath,
            'is_late'       => $isLate,
            'marks'         => null,
            'feedback'      => null,
        ]);

        if ($assignment->auto_grade) {
            $gradingService = app(AutoGradingService::class);
            $result = $gradingService->grade($submission);
            $submission->update([
                'marks'    => $result['score'],
                'feedback' => $result['feedback'],
            ]);
        }

        return redirect()->route('student.assignments')
            ->with('success', 'Tugas berhasil dikumpulkan! ' . ($assignment->auto_grade ? 'Nilai otomatis: ' . ($result['score'] ?? 0) : ''));
    }
}
