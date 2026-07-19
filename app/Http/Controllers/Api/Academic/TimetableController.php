<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\TimetableBreak;
use App\Models\Academic\TimetableSlot;
use App\Models\Academic\Student;
use App\Services\Academic\TimetableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(private TimetableService $service) {}

    public function byClass(int $classSectionId): JsonResponse
    {
        $data = $this->service->getWeeklyForClass($classSectionId);
        return response()->json($data);
    }

    public function byTeacher(int $teacherId): JsonResponse
    {
        $data = $this->service->getWeeklyForTeacher($teacherId);
        return response()->json($data);
    }

    public function mySchedule(Request $request): JsonResponse
    {
        $data = $this->service->getWeeklyForTeacher($request->user()->id);
        return response()->json($data);
    }

    public function studentSchedule(Request $request): JsonResponse
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        abort_if(!$student->class_section_id, 404, 'Student not assigned to a class.');

        $data = $this->service->getWeeklyForClass($student->class_section_id);
        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
            'subject_id'       => 'required|integer|exists:subjects,id',
            'teacher_id'       => 'required|integer|exists:users,id',
            'day_of_week'      => 'required|integer|between:1,7',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'room'             => 'nullable|string|max:100',
        ]);

        $slot = $this->service->store($validated);
        return response()->json($slot->load('subject', 'teacher'), 201);
    }

    public function update(Request $request, TimetableSlot $timetableSlot): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'sometimes|integer|exists:class_sections,id',
            'subject_id'       => 'sometimes|integer|exists:subjects,id',
            'teacher_id'       => 'sometimes|integer|exists:users,id',
            'day_of_week'      => 'sometimes|integer|between:1,7',
            'start_time'       => 'sometimes|date_format:H:i',
            'end_time'         => 'sometimes|date_format:H:i|after:start_time',
            'room'             => 'nullable|string|max:100',
        ]);

        return response()->json($this->service->update($timetableSlot, $validated));
    }

    public function destroy(TimetableSlot $timetableSlot): JsonResponse
    {
        $timetableSlot->delete();
        return response()->json(['message' => 'Slot deleted.']);
    }

    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
            'slots'            => 'required|array|min:1',
            'slots.*.subject_id'  => 'required|integer|exists:subjects,id',
            'slots.*.teacher_id'  => 'required|integer|exists:users,id',
            'slots.*.day_of_week' => 'required|integer|between:1,7',
            'slots.*.start_time'  => 'required|date_format:H:i',
            'slots.*.end_time'    => 'required|date_format:H:i',
            'slots.*.room'        => 'nullable|string|max:100',
        ]);

        $count = $this->service->bulkReplace($validated['class_section_id'], $validated['slots']);
        return response()->json(['message' => "{$count} slot(s) saved."]);
    }

    public function checkConflict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|integer',
            'teacher_id'       => 'required|integer',
            'day_of_week'      => 'required|integer|between:1,7',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i',
            'exclude_id'       => 'nullable|integer',
        ]);

        $conflicts = $this->service->checkConflict($validated, $validated['exclude_id'] ?? null);
        return response()->json(['conflicts' => $conflicts, 'has_conflict' => !empty($conflicts)]);
    }

    public function breaks(): JsonResponse
    {
        $breaks = TimetableBreak::orderBy('day_of_week')->orderBy('start_time')->get();
        return response()->json($breaks);
    }

    public function storeBreak(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'day_of_week' => 'required|integer|between:0,7',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $validated['school_id'] = $request->user()->school_id;
        $break = TimetableBreak::create($validated);
        return response()->json($break, 201);
    }
}
