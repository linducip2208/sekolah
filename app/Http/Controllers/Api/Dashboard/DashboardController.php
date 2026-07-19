<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\Exam;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Academic\TimetableSlot;
use App\Models\Communication\NotificationLog;
use App\Models\Finance\FeeInvoice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function student(Request $request): JsonResponse
    {
        $user = $request->user();

        $student = Student::where('user_id', $user->id)->with('classSection.classRoom')->first();

        if (!$student) {
            return response()->json(['data' => $this->emptyStudent()]);
        }

        $todaySchedule = $this->scheduleForSection($student->class_section_id, now()->dayOfWeekIso);

        $monthAttendance = Attendance::where('student_id', $student->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        $present = $monthAttendance->whereIn('status', ['present', 'late'])->count();
        $total = max($monthAttendance->count(), 1);
        $attendancePct = round(($present / $total) * 100);

        $pendingTasks = DB::table('assignment_submissions')
            ->where('student_id', $student->id)
            ->where('status', '!=', 'submitted')
            ->count();

        $upcomingExams = Exam::where('class_section_id', $student->class_section_id)
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDays(14))
            ->count();

        $unpaidInvoices = FeeInvoice::where('student_id', $student->id)
            ->where('status', '!=', 'paid')
            ->count();

        return response()->json([
            'data' => [
                'class_name'       => optional($student->classSection?->classRoom)->name ?? '-',
                'class_section_id' => $student->class_section_id,
                'pending_tasks'    => $pendingTasks,
                'upcoming_exams'   => $upcomingExams,
                'unpaid_invoices'  => $unpaidInvoices,
                'attendance_pct'   => $attendancePct,
                'today_schedule'   => $todaySchedule,
            ],
        ]);
    }

    public function teacher(Request $request): JsonResponse
    {
        $user = $request->user();

        $todaySlots = TimetableSlot::where('teacher_id', $user->id)
            ->where('day_of_week', now()->dayOfWeekIso)
            ->with('subject', 'classSection.classRoom')
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'class_name' => optional($s->classSection?->classRoom)->name . ' ' . optional($s->classSection)->name,
                'subject'    => optional($s->subject)->name,
                'start'      => substr($s->start_time, 0, 5),
                'end'        => substr($s->end_time, 0, 5),
                'room'       => $s->room,
                'section_id' => $s->class_section_id,
            ])->values();

        $sectionIds = $todaySlots->pluck('section_id')->unique();
        $markedSections = Attendance::whereIn('class_section_id', $sectionIds)
            ->whereDate('date', now())
            ->distinct('class_section_id')
            ->pluck('class_section_id');
        $unmarked = $sectionIds->diff($markedSections)->count();

        $pendingGrading = DB::table('assignment_submissions')
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->where('assignments.created_by', $user->id)
            ->where('assignment_submissions.status', 'submitted')
            ->whereNull('assignment_submissions.score')
            ->count();

        return response()->json([
            'data' => [
                'classes_today'     => $todaySlots->count(),
                'pending_grading'   => $pendingGrading,
                'unmarked_classes'  => $unmarked,
                'today_classes'     => $todaySlots,
            ],
        ]);
    }

    public function parent(Request $request): JsonResponse
    {
        $user = $request->user();

        $children = $user->parentStudents()
            ->with('user', 'classSection.classRoom')
            ->get()
            ->map(function ($s) {
                $monthAtt = Attendance::where('student_id', $s->id)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->get();
                $present = $monthAtt->whereIn('status', ['present', 'late'])->count();
                $total = max($monthAtt->count(), 1);
                return [
                    'id'              => $s->id,
                    'name'            => optional($s->user)->name,
                    'class_name'      => optional($s->classSection?->classRoom)->name . ' ' . optional($s->classSection)->name,
                    'attendance_pct'  => round(($present / $total) * 100),
                ];
            });

        $studentIds = $children->pluck('id');
        $totalUnpaid = FeeInvoice::whereIn('student_id', $studentIds)
            ->where('status', '!=', 'paid')
            ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        return response()->json([
            'data' => [
                'children'             => $children,
                'total_unpaid_amount'  => (int) $totalUnpaid,
            ],
        ]);
    }

    public function admin(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Staff::where('school_id', $schoolId)->count();

        $feesCollected = FeeInvoice::where('school_id', $schoolId)
            ->whereYear('created_at', now()->year)
            ->sum('paid_amount');
        $feesPending = FeeInvoice::where('school_id', $schoolId)
            ->where('status', '!=', 'paid')
            ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $records = Attendance::where('school_id', $schoolId)
                ->whereDate('date', $day)
                ->get();
            $present = $records->whereIn('status', ['present', 'late'])->count();
            $total = max($records->count(), 1);
            $trend[] = [
                'date'  => $day,
                'value' => round(($present / $total) * 100),
            ];
        }

        return response()->json([
            'data' => [
                'total_students'    => $totalStudents,
                'total_teachers'    => $totalTeachers,
                'fees_collected'    => (int) $feesCollected,
                'fees_pending'      => (int) $feesPending,
                'attendance_trend'  => $trend,
            ],
        ]);
    }

    private function scheduleForSection(int $sectionId, int $isoDay): array
    {
        return TimetableSlot::where('class_section_id', $sectionId)
            ->where('day_of_week', $isoDay)
            ->with('subject', 'teacher')
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'subject' => optional($s->subject)->name,
                'teacher' => optional($s->teacher)->name,
                'start'   => substr($s->start_time, 0, 5),
                'end'     => substr($s->end_time, 0, 5),
                'room'    => $s->room,
            ])->toArray();
    }

    private function emptyStudent(): array
    {
        return [
            'class_name'       => '-',
            'pending_tasks'    => 0,
            'upcoming_exams'   => 0,
            'unpaid_invoices'  => 0,
            'attendance_pct'   => 0,
            'today_schedule'   => [],
        ];
    }
}
