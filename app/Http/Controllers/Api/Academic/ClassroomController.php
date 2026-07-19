<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\Lesson;
use App\Services\Academic\ClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function __construct(private ClassroomService $service) {}

    // --- Lessons ---

    public function lessons(Request $request): JsonResponse
    {
        $request->validate(['class_section_id' => 'required|integer']);
        $lessons = $this->service->getLessonsForClass(
            $request->class_section_id,
            $request->subject_id
        );
        return response()->json($lessons);
    }

    public function storeLesson(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
            'subject_id'       => 'required|integer|exists:subjects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
        ]);

        return response()->json($this->service->createLesson($validated), 201);
    }

    public function updateLesson(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);
        $lesson->update($validated);
        return response()->json($lesson->fresh());
    }

    public function destroyLesson(Lesson $lesson): JsonResponse
    {
        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted.']);
    }

    // --- Study Materials ---

    public function storeMaterial(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type'  => 'required|in:file,link,video',
            'url'   => 'required|string|max:1000',
        ]);

        $material = $this->service->addMaterial($lesson->id, $validated);
        return response()->json($material, 201);
    }

    public function destroyMaterial(int $materialId): JsonResponse
    {
        \App\Models\Academic\StudyMaterial::findOrFail($materialId)->delete();
        return response()->json(['message' => 'Material deleted.']);
    }

    // --- Assignments ---

    public function assignments(Request $request): JsonResponse
    {
        $request->validate(['lesson_id' => 'required|integer']);
        $assignments = Assignment::where('lesson_id', $request->lesson_id)
            ->with('lesson.subject')
            ->latest()
            ->get();
        return response()->json($assignments);
    }

    public function storeAssignment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lesson_id'   => 'required|integer|exists:lessons,id',
            'title'       => 'required|string|max:255',
            'instructions'=> 'nullable|string',
            'due_date'    => 'required|date|after:now',
            'total_marks' => 'sometimes|integer|min:1|max:1000',
        ]);

        return response()->json($this->service->createAssignment($validated), 201);
    }

    public function updateAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'instructions' => 'nullable|string',
            'due_date'     => 'sometimes|date',
            'total_marks'  => 'sometimes|integer|min:1',
        ]);
        $assignment->update($validated);
        return response()->json($assignment->fresh());
    }

    public function destroyAssignment(Assignment $assignment): JsonResponse
    {
        $assignment->delete();
        return response()->json(['message' => 'Assignment deleted.']);
    }

    public function showAssignment(Assignment $assignment): JsonResponse
    {
        return response()->json($assignment->load('lesson.subject', 'lesson.classSection'));
    }

    // --- Submissions ---

    public function submit(Request $request, Assignment $assignment): JsonResponse
    {
        $validated = $request->validate([
            'answer' => 'nullable|string',
            'file'   => 'nullable|string|max:1000',
        ]);

        $submission = $this->service->submitAssignment($assignment->id, $validated);
        return response()->json($submission, 201);
    }

    public function submissions(Assignment $assignment): JsonResponse
    {
        $submissions = $assignment->submissions()->with('student.user')->get();
        return response()->json($submissions);
    }

    public function grade(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'marks'    => 'required|integer|min:0',
            'feedback' => 'nullable|string',
        ]);

        return response()->json($this->service->gradeSubmission($submission, $validated));
    }
}
