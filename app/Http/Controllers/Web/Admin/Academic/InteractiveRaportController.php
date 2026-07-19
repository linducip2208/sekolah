<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InteractiveRaportController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $students = Student::where('school_id', $schoolId)
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        $semesters = Semester::where('school_id', $schoolId)
            ->with('academicYear')
            ->orderByDesc('start_date')
            ->get();

        $selectedStudent = null;
        $selectedSemester = null;
        $chartData = null;
        $progressData = null;
        $reportCard = null;
        $attendanceData = null;

        if ($request->filled('student_id') && $request->filled('semester_id')) {
            $selectedStudent = Student::where('school_id', $schoolId)
                ->where('id', $request->input('student_id'))
                ->with('user:id,name')
                ->first();

            $selectedSemester = Semester::where('school_id', $schoolId)
                ->where('id', $request->input('semester_id'))
                ->first();

            if ($selectedStudent && $selectedSemester) {
                $reportCard = ReportCard::where('student_id', $selectedStudent->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->first();

                $marks = Mark::where('student_id', $selectedStudent->id)
                    ->where('semester_id', $selectedSemester->id)
                    ->with('subject:id,name')
                    ->get();

                $chartData = $this->buildSubjectComparison($marks);
                $progressData = $this->buildSemesterProgress($selectedStudent->id, $selectedSemester);
                $attendanceData = $this->buildAttendanceData($selectedStudent->id, $selectedSemester);
            }
        }

        return view('school-admin.academic.raport-interaktif', compact(
            'students', 'semesters', 'selectedStudent', 'selectedSemester',
            'reportCard', 'chartData', 'progressData', 'attendanceData'
        ));
    }

    public function parentView(Student $student): View
    {
        $userId = auth()->id();
        $isParent = DB::table('parent_student')
            ->where('parent_id', $userId)
            ->where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->exists();
        abort_unless($isParent || auth()->user()->hasRole(['admin', 'super_admin']), 403);

        $semesters = Semester::where('school_id', $student->school_id)
            ->orderByDesc('start_date')
            ->get();

        $selectedSemester = Semester::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->first() ?? $semesters->first();

        $reportCard = null;
        $chartData = null;
        $progressData = null;
        $attendanceData = null;

        if ($selectedSemester) {
            $reportCard = ReportCard::where('student_id', $student->id)
                ->where('semester_id', $selectedSemester->id)
                ->first();

            $marks = Mark::where('student_id', $student->id)
                ->where('semester_id', $selectedSemester->id)
                ->with('subject:id,name')
                ->get();

            $chartData = $this->buildSubjectComparison($marks);
            $progressData = $this->buildSemesterProgress($student->id, $selectedSemester);
            $attendanceData = $this->buildAttendanceData($student->id, $selectedSemester);
        }

        return view('parent-portal.raport-interaktif', compact(
            'student', 'semesters', 'selectedSemester',
            'reportCard', 'chartData', 'progressData', 'attendanceData'
        ));
    }

    private function buildSubjectComparison($marks): array
    {
        $labels = [];
        $obtained = [];
        $total = [];

        foreach ($marks as $mark) {
            $name = $mark->subject?->name ?? 'Mata Pelajaran #' . $mark->subject_id;
            $labels[] = $name;
            $obtained[] = max(0, (int) $mark->obtained_marks);
            $total[] = max(0, (int) $mark->total_marks);
        }

        return [
            'labels'   => $labels,
            'obtained' => $obtained,
            'total'    => $total,
        ];
    }

    private function buildSemesterProgress(int $studentId, Semester $currentSemester): array
    {
        $allSemesters = Semester::where('school_id', $this->schoolId())
            ->orderBy('start_date')
            ->get();

        $labels = [];
        $gpaValues = [];

        foreach ($allSemesters as $sem) {
            $card = ReportCard::where('student_id', $studentId)
                ->where('semester_id', $sem->id)
                ->first();

            $labels[] = $sem->name;
            $gpaValues[] = $card?->gpa ? round((float) $card->gpa, 2) : null;
        }

        return [
            'labels' => $labels,
            'gpa'    => $gpaValues,
        ];
    }

    private function buildAttendanceData(int $studentId, Semester $semester): array
    {
        $records = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$semester->start_date, $semester->end_date])
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'hadir'   => (int) ($records['hadir'] ?? $records['present'] ?? 0),
            'izin'    => (int) ($records['izin'] ?? $records['excused'] ?? 0),
            'sakit'   => (int) ($records['sakit'] ?? $records['sick'] ?? 0),
            'alpa'    => (int) ($records['alpa'] ?? $records['absent'] ?? 0),
            'terlambat' => (int) ($records['terlambat'] ?? $records['late'] ?? 0),
        ];
    }
}
