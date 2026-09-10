<?php

namespace App\Http\Controllers\Api\DailyReport;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\DailyReport\DailyReport;
use App\Services\DailyReport\DailyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function __construct(private DailyReportService $service) {}

    public function reportsForChild(Request $request, int $studentId): JsonResponse
    {
        $student = Student::where('school_id', $request->user()->school_id)->findOrFail($studentId);
        $user = $request->user();
        $isParentOfStudent = $user->parentStudents()->whereKey($student->id)->exists();
        $isStudent = (int) $student->user_id === (int) $user->id;
        $isStaff = $user->hasRole('super_admin') || $user->can('daily_reports.view');
        abort_unless($isParentOfStudent || $isStudent || $isStaff, 403, 'Tidak memiliki akses laporan harian siswa.');

        return response()->json([
            'data' => DailyReport::where('school_id', $request->user()->school_id)
                ->where('student_id', $studentId)
                ->orderByDesc('report_date')
                ->paginate(30),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $count = $this->service->generateForSchool(
            $request->user()->school_id,
            $request->input('date') ? new \DateTimeImmutable($request->input('date')) : null,
        );

        return response()->json(['count' => $count]);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $report = DailyReport::where('school_id', $request->user()->school_id)->findOrFail($id);

        return response()->json($this->service->send($report));
    }
}
