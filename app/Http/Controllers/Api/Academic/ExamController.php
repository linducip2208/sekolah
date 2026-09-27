<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Services\Academic\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(private ExamService $service) {}

    public function index(Request $request): JsonResponse
    {
        $exams = Exam::when($request->class_section_id, fn($q) => $q->where('class_section_id', $request->class_section_id))
            ->with('subject', 'classSection')
            ->latest()
            ->get();
        return response()->json($exams);
    }

    public function store(Request $request): JsonResponse
    {
        $schoolId = (int) auth()->user()->school_id;
        $validated = $request->validate([
            'class_section_id'  => [
                'required', 'integer',
                \Illuminate\Validation\Rule::exists('class_sections', 'id')->where('school_id', $schoolId),
            ],
            'subject_id'        => [
                'required', 'integer',
                \Illuminate\Validation\Rule::exists('subjects', 'id')->where('school_id', $schoolId),
            ],
            'title'             => 'required|string|max:255',
            'type'              => 'sometimes|in:online,offline',
            'start_at'          => 'nullable|date',
            'end_at'            => 'nullable|date|after:start_at',
            'duration_minutes'  => 'nullable|integer|min:1',
            'total_marks'       => 'sometimes|integer|min:1',
            'pass_marks'        => 'sometimes|integer|min:1',
            'shuffle_questions' => 'sometimes|boolean',
        ]);

        $validated['school_id'] = $schoolId;
        return response()->json(Exam::create($validated), 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $validated = $request->validate([
            'title'             => 'sometimes|string|max:255',
            'start_at'          => 'nullable|date',
            'end_at'            => 'nullable|date',
            'duration_minutes'  => 'nullable|integer|min:1',
            'total_marks'       => 'sometimes|integer|min:1',
            'pass_marks'        => 'sometimes|integer|min:1',
            'shuffle_questions' => 'sometimes|boolean',
        ]);
        $exam->update($validated);
        return response()->json($exam->fresh());
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $exam->delete();
        return response()->json(['message' => 'Exam deleted.']);
    }

    public function questions(Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $questions = $exam->questions()->orderBy('order')->get();
        if (! $this->canSeeAnswerKey()) {
            $questions->each->makeHidden(['correct_answer']);
        }
        return response()->json($questions);
    }

    public function storeQuestion(Request $request, Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $validated = $request->validate([
            'question'       => 'required|string',
            'type'           => 'required|in:mcq,true_false,essay',
            'options'        => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'marks'          => 'sometimes|integer|min:1',
            'order'          => 'sometimes|integer|min:0',
        ]);

        $question = $exam->questions()->create($validated);
        return response()->json($question, 201);
    }

    public function updateQuestion(Request $request, ExamQuestion $question): JsonResponse
    {
        abort_unless((int) $question->school_id === (int) auth()->user()->school_id, 404);
        $validated = $request->validate([
            'question'       => 'sometimes|string',
            'options'        => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'marks'          => 'sometimes|integer|min:1',
            'order'          => 'sometimes|integer|min:0',
        ]);
        $question->update($validated);
        return response()->json($question->fresh());
    }

    public function destroyQuestion(ExamQuestion $question): JsonResponse
    {
        abort_unless((int) $question->school_id === (int) auth()->user()->school_id, 404);
        $question->delete();
        return response()->json(['message' => 'Question deleted.']);
    }

    public function start(Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $result = $this->service->startExam($exam->id);
        return response()->json($result);
    }

    public function submit(Request $request, Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $result = $this->service->submitExam($exam->id, $validated['answers']);
        return response()->json($result);
    }

    public function result(Exam $exam): JsonResponse
    {
        $student = \App\Models\Academic\Student::where('user_id', auth()->id())->firstOrFail();
        $result  = $exam->results()->where('student_id', $student->id)->firstOrFail();
        return response()->json($result->load('exam'));
    }

    public function submissions(Exam $exam): JsonResponse
    {
        $this->assertOwnSchool($exam);
        abort_unless(
            auth()->user()->hasRole(['super_admin', 'admin', 'principal', 'teacher', 'homeroom_teacher']),
            403
        );
        return response()->json($exam->results()->with('student.user')->get());
    }

    private function assertOwnSchool(Exam $exam): void
    {
        abort_unless((int) $exam->school_id === (int) auth()->user()->school_id, 404);
    }

    private function canSeeAnswerKey(): bool
    {
        return (bool) auth()->user()->hasRole(['super_admin', 'admin', 'principal', 'teacher', 'homeroom_teacher']);
    }
}
