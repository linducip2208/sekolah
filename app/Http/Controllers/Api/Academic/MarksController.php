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

    public function byStudent(Request $request, int $studentId): JsonResponse
    {
        $this->requirePermission($request, 'marks.view');
        $student = Student::withoutGlobalScopes()
            ->where('school_id', $request->user()->school_id)
            ->findOrFail($studentId);
        $this->assertStudentReadable($request, $student);
        $marks = Mark::where('school_id', $request->user()->school_id)
            ->where('student_id', $studentId)
            ->with('subject', 'semester', 'exam')
            ->get();

        return response()->json($marks);
    }

    public function bulk(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'marks.manage');
        $validated = $request->validate([
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => 'required|integer|exists:students,id',
            'marks.*.subject_id' => 'required|integer|exists:subjects,id',
            'marks.*.semester_id' => 'required|integer|exists:semesters,id',
            'marks.*.exam_id' => 'nullable|integer|exists:exams,id',
            'marks.*.obtained_marks' => 'required|integer|min:0',
            'marks.*.total_marks' => 'required|integer|min:1',
        ]);

        $count = $this->service->bulkSave($validated['marks']);

        return response()->json(['saved' => $count]);
    }

    public function update(Request $request, Mark $mark): JsonResponse
    {
        $this->requirePermission($request, 'marks.manage');
        $validated = $request->validate([
            'obtained_marks' => 'sometimes|integer|min:0',
            'total_marks' => 'sometimes|integer|min:1',
        ]);

        return response()->json($this->service->update($mark, $validated));
    }

    // Grade Systems
    public function gradeSystems(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'marks.view');

        return response()->json(GradeSystem::where('school_id', $request->user()->school_id)->with('rules')->get());
    }

    public function storeGradeSystem(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'marks.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rules' => 'required|array|min:1',
            'rules.*.grade' => 'required|string|max:10',
            'rules.*.min_percent' => 'required|numeric|min:0|max:100',
            'rules.*.max_percent' => 'required|numeric|min:0|max:100',
            'rules.*.gpa_point' => 'sometimes|numeric|min:0|max:4',
        ]);

        $system = GradeSystem::create([
            'school_id' => auth()->user()->school_id,
            'name' => $validated['name'],
        ]);

        foreach ($validated['rules'] as $rule) {
            $system->rules()->create($rule);
        }

        return response()->json($system->load('rules'), 201);
    }

    // Report Cards
    public function generateReportCards(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'marks.manage');
        $validated = $request->validate(['semester_id' => 'required|integer|exists:semesters,id']);
        $count = $this->service->generateReportCards($validated['semester_id']);

        return response()->json(['generated' => $count]);
    }

    public function studentReportCard(Request $request, int $studentId): JsonResponse
    {
        $this->requirePermission($request, 'marks.view');
        $student = Student::withoutGlobalScopes()->where('school_id', $request->user()->school_id)->findOrFail($studentId);
        $this->assertStudentReadable($request, $student);
        $cards = ReportCard::where('student_id', $studentId)
            ->with('semester')
            ->orderByDesc('id')
            ->get();

        return response()->json($cards);
    }

    public function publishReportCard(Request $request, ReportCard $reportCard): JsonResponse
    {
        $this->requirePermission($request, 'marks.manage');
        abort_unless((int) $reportCard->school_id === (int) $request->user()->school_id, 404);
        abort_unless($reportCard->status === 'approved', 422, 'Rapor harus disetujui sebelum dipublikasikan.');
        $reportCard->update(['is_published' => true]);

        return response()->json(['message' => 'Report card published.']);
    }

    public function mine(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'marks.view');
        $student = Student::where('school_id', $request->user()->school_id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json($student
            ? Mark::where('school_id', $request->user()->school_id)->where('student_id', $student->id)->with('subject', 'semester', 'exam')->get()
            : []);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin nilai.');
    }

    private function assertStudentReadable(Request $request, Student $student): void
    {
        $user = $request->user();
        if ($user->hasRole('student')) {
            abort_unless((int) $student->user_id === (int) $user->id, 403);
        }
        if ($user->hasRole('parent')) {
            abort_unless($student->parents()->whereKey($user->id)->exists(), 403);
        }
    }
}
