<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use Illuminate\Http\JsonResponse;

class ParentPortalController extends Controller
{
    public function children(): JsonResponse
    {
        $children = auth()->user()->parentStudents()
            ->with(['user', 'classSection.classRoom', 'classSection.section'])
            ->get();
        return response()->json($children);
    }

    public function childAttendance(int $studentId): JsonResponse
    {
        $this->authorizeChild($studentId);

        $attendance = Attendance::where('student_id', $studentId)
            ->orderByDesc('date')
            ->paginate(30);

        return response()->json($attendance);
    }

    public function childMarks(int $studentId): JsonResponse
    {
        $this->authorizeChild($studentId);

        $marks = Mark::where('student_id', $studentId)
            ->with(['subject', 'semester'])
            ->orderByDesc('semester_id')
            ->get();

        return response()->json($marks);
    }

    public function childInvoices(int $studentId): JsonResponse
    {
        $this->authorizeChild($studentId);

        $invoices = FeeInvoice::where('student_id', $studentId)
            ->with('feeStructure')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($invoices);
    }

    private function authorizeChild(int $studentId): void
    {
        $owns = auth()->user()->parentStudents()->where('students.id', $studentId)->exists();
        if (!$owns) {
            abort(403, 'Access denied.');
        }
    }
}
