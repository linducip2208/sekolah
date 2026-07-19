<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\Student;
use App\Models\AI\AiEssayGrading;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Services\AI\EssayGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EssayGradingController extends Controller
{
    public function __construct(protected EssayGradingService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $exams = Exam::where('school_id', $schoolId)
            ->with(['classSection.classRoom', 'subject'])
            ->orderByDesc('start_at')
            ->get();

        $selectedExamId = $request->exam_id;
        $selectedQuestionId = $request->question_id;

        $questions = collect();
        $students = collect();
        $gradings = collect();

        if ($selectedExamId) {
            $questions = ExamQuestion::where('exam_id', $selectedExamId)
                ->orderBy('order')->get();

            if ($selectedQuestionId) {
                $selectedExam = Exam::with('classSection')->find($selectedExamId);
                $students = Student::where('school_id', $schoolId)
                    ->where('class_section_id', $selectedExam?->class_section_id)
                    ->with('user:id,name')
                    ->orderBy('admission_no')
                    ->get();

                $gradings = AiEssayGrading::where('school_id', $schoolId)
                    ->where('exam_id', $selectedExamId)
                    ->when($selectedQuestionId, fn ($q) => $q->where('question_text', 'like', '%' . ($questions->find($selectedQuestionId)?->question ?? '') . '%'))
                    ->with(['student.user:id,name', 'aiModel', 'grader:id,name'])
                    ->orderByDesc('graded_at')
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $providers = AiProvider::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('priority')->orderBy('name')
            ->get();

        $aiModels = AiModel::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('provider')
            ->orderBy('priority')
            ->get();

        return view('school-admin.academic.essay-grading.index', [
            'exams'                => $exams,
            'selectedExamId'       => $selectedExamId,
            'selectedQuestionId'   => $selectedQuestionId,
            'questions'            => $questions,
            'students'             => $students,
            'gradings'             => $gradings,
            'providers'            => $providers,
            'aiModels'             => $aiModels,
        ]);
    }

    public function gradeSingle(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();
        $userId   = auth()->id();

        $data = $request->validate([
            'exam_id'          => 'required|exists:exams,id',
            'student_id'       => 'required|exists:students,id',
            'question_text'    => 'required|string',
            'student_answer'   => 'required|string',
            'reference_answer' => 'nullable|string',
            'ai_provider_id'   => 'nullable|exists:ai_providers,id',
            'ai_model_id'      => 'nullable|exists:ai_models,id',
            'rubric'           => 'nullable|string',
        ]);

        try {
            $this->service->grade(
                $schoolId, $userId,
                (int) $data['exam_id'], (int) $data['student_id'],
                $data['question_text'], $data['student_answer'],
                $data['reference_answer'] ?: null,
                $data['ai_provider_id'] ? (int) $data['ai_provider_id'] : null,
                $data['ai_model_id'] ? (int) $data['ai_model_id'] : null,
                $data['rubric'] ?: null,
            );
            return back()->with('success', 'Essay berhasil dinilai oleh AI.');
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal menilai essay: ' . $e->getMessage());
        }
    }

    public function gradeBatch(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();
        $userId   = auth()->id();

        $data = $request->validate([
            'exam_id'         => 'required|exists:exams,id',
            'question_text'   => 'required|string',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
            'rubric'          => 'nullable|string',
            'submissions'     => 'required|array|min:1',
            'submissions.*.student_id'      => 'required|exists:students,id',
            'submissions.*.answer'          => 'nullable|string',
            'submissions.*.reference_answer'=> 'nullable|string',
        ]);

        $submissions = [];
        foreach ($data['submissions'] as $sub) {
            $answer = trim($sub['answer'] ?? '');
            if (empty($answer)) continue;
            $submissions[] = [
                'student_id'       => (int) $sub['student_id'],
                'question_text'    => $data['question_text'],
                'student_answer'   => $answer,
                'reference_answer' => $sub['reference_answer'] ?? null,
            ];
        }

        if (empty($submissions)) {
            return back()->withErrors('Tidak ada jawaban siswa yang diisi.');
        }

        try {
            $count = $this->service->gradeBatch(
                $schoolId, $userId, (int) $data['exam_id'],
                $submissions,
                $data['ai_provider_id'] ? (int) $data['ai_provider_id'] : null,
                $data['ai_model_id'] ? (int) $data['ai_model_id'] : null,
                $data['rubric'] ?: null,
            );
            return back()->with('success', "{$count} essay berhasil dinilai oleh AI.");
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal menilai essay batch: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $schoolId = $this->schoolId();

        $examId = $request->exam_id;
        if (!$examId) {
            return back()->withErrors('Pilih ujian terlebih dahulu.');
        }

        $gradings = AiEssayGrading::where('school_id', $schoolId)
            ->where('exam_id', $examId)
            ->with(['student.user:id,name'])
            ->orderBy('student_id')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="penilaian-essay-'.date('Ymd-His').'.csv"',
        ];

        $callback = function () use ($gradings) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

            fputcsv($handle, ['Siswa', 'Pertanyaan', 'Jawaban', 'Skor AI', 'Level', 'Feedback', 'Rubrik', 'Waktu Penilaian']);

            foreach ($gradings as $g) {
                fputcsv($handle, [
                    $g->student?->user?->name ?? "Siswa #{$g->student_id}",
                    \Str::limit($g->question_text, 100),
                    \Str::limit($g->student_answer, 100),
                    $g->ai_score,
                    $g->scoreLabel(),
                    $g->ai_feedback,
                    json_encode($g->ai_rubric_breakdown),
                    $g->graded_at?->format('d M Y H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
