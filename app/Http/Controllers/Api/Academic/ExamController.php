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
        $validated = $request->validate([
            'class_section_id'  => 'required|integer|exists:class_sections,id',
            'subject_id'        => 'required|integer|exists:subjects,id',
            'title'             => 'required|string|max:255',
            'type'              => 'sometimes|in:online,offline',
            'start_at'          => 'nullable|date',
            'end_at'            => 'nullable|date|after:start_at',
            'duration_minutes'  => 'nullable|integer|min:1',
            'total_marks'       => 'sometimes|integer|min:1',
            'pass_marks'        => 'sometimes|integer|min:1',
            'shuffle_questions' => 'sometimes|boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(Exam::create($validated), 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
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
        $exam->delete();
        return response()->json(['message' => 'Exam deleted.']);
    }

    public function questions(Exam $exam): JsonResponse
    {
        return response()->json($exam->questions()->orderBy('order')->get());
    }

    public function storeQuestion(Request $request, Exam $exam): JsonResponse
    {
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
        $question->delete();
        return response()->json(['message' => 'Question deleted.']);
    }

    public function start(Exam $exam): JsonResponse
    {
        $result = $this->service->startExam($exam->id);
        return response()->json($result);
    }

    public function submit(Request $request, Exam $exam): JsonResponse
    {
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
        return response()->json($exam->results()->with('student.user')->get());
    }
}
