<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\GradeSystem;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Student;
use App\Services\Academic\MarksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    public function __construct(private MarksService $service) {}

    public function byStudent(int $studentId): JsonResponse
    {
        $marks = Mark::where('student_id', $studentId)
            ->with('subject', 'semester', 'exam')
            ->get();
        return response()->json($marks);
    }

    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'marks'                 => 'required|array|min:1',
            'marks.*.student_id'   => 'required|integer|exists:students,id',
            'marks.*.subject_id'   => 'required|integer|exists:subjects,id',
            'marks.*.semester_id'  => 'required|integer|exists:semesters,id',
            'marks.*.exam_id'      => 'nullable|integer|exists:exams,id',
            'marks.*.obtained_marks' => 'required|integer|min:0',
            'marks.*.total_marks'    => 'required|integer|min:1',
        ]);

        $count = $this->service->bulkSave($validated['marks']);
        return response()->json(['saved' => $count]);
    }

    public function update(Request $request, Mark $mark): JsonResponse
    {
        $validated = $request->validate([
            'obtained_marks' => 'sometimes|integer|min:0',
            'total_marks'    => 'sometimes|integer|min:1',
        ]);
        $mark->update($validated);
        return response()->json($mark->fresh());
    }

    // Grade Systems
    public function gradeSystems(): JsonResponse
    {
        return response()->json(GradeSystem::with('rules')->get());
    }

    public function storeGradeSystem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'rules'   => 'required|array|min:1',
            'rules.*.grade'       => 'required|string|max:10',
            'rules.*.min_percent' => 'required|numeric|min:0|max:100',
            'rules.*.max_percent' => 'required|numeric|min:0|max:100',
            'rules.*.gpa_point'   => 'sometimes|numeric|min:0|max:4',
        ]);

        $system = GradeSystem::create([
            'school_id' => auth()->user()->school_id,
            'name'      => $validated['name'],
        ]);

        foreach ($validated['rules'] as $rule) {
            $system->rules()->create($rule);
        }

        return response()->json($system->load('rules'), 201);
    }

    // Report Cards
    public function generateReportCards(Request $request): JsonResponse
    {
        $validated = $request->validate(['semester_id' => 'required|integer|exists:semesters,id']);
        $count = $this->service->generateReportCards($validated['semester_id']);
        return response()->json(['generated' => $count]);
    }

    public function studentReportCard(int $studentId): JsonResponse
    {
        $cards = ReportCard::where('student_id', $studentId)
            ->with('semester')
            ->orderByDesc('id')
            ->get();
        return response()->json($cards);
    }

    public function publishReportCard(ReportCard $reportCard): JsonResponse
    {
        $reportCard->update(['is_published' => true]);
        return response()->json(['message' => 'Report card published.']);
    }
}
