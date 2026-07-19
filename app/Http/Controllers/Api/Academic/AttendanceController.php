<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Services\Academic\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function getByClass(Request $request, int $classSectionId): JsonResponse
    {
        $request->validate(['date' => 'required|date']);

        $records = Attendance::where('class_section_id', $classSectionId)
            ->where('date', $request->date)
            ->with('student.user')
            ->get();

        return response()->json($records);
    }

    public function bulkMark(Request $request, int $classSectionId): JsonResponse
    {
        $data = $request->validate([
            'date'                  => 'required|date',
            'attendances'           => 'required|array|min:1',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status'  => 'required|in:present,absent,late,half_day,on_leave',
            'attendances.*.note'    => 'nullable|string|max:255',
        ]);

        $summary = $this->service->bulkMark(
            $classSectionId,
            $data['date'],
            $data['attendances'],
            $request->user()
        );

        return response()->json([
            'message'          => 'Attendance marked for ' . count($data['attendances']) . ' students',
            'date'             => $data['date'],
            'class_section_id' => $classSectionId,
            'summary'          => $summary,
        ]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:present,absent,late,half_day,on_leave',
            'note'   => 'nullable|string|max:255',
        ]);

        $attendance->update($data);
        return response()->json($attendance->fresh());
    }

    public function getByStudent(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        $records = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$request->from_date, $request->to_date])
            ->orderBy('date')
            ->get();

        return response()->json($records);
    }

    public function summary(int $studentId): JsonResponse
    {
        $fromDate = request('from_date', now()->startOfMonth()->toDateString());
        $toDate   = request('to_date', now()->toDateString());

        return response()->json(
            $this->service->getStudentSummary($studentId, $fromDate, $toDate)
        );
    }

    public function mine(Request $request): JsonResponse
    {
        $student = \App\Models\Academic\Student::where('user_id', $request->user()->id)->first();
        if (!$student) {
            return response()->json([]);
        }

        $from = $request->get('from_date', now()->startOfMonth()->toDateString());
        $to   = $request->get('to_date', now()->toDateString());

        $records = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($records);
    }
}
