<?php

namespace App\Http\Controllers\Web\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Library\LibraryIssue;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first() ?? 'admin';
        $schoolId = $user->school_id;

        $data = match ($role) {
            'principal'     => $this->principalData($schoolId),
            'teacher'       => $this->teacherData($schoolId, $user),
            'parent'        => $this->parentData($schoolId, $user),
            'student'       => $this->studentData($schoolId, $user),
            'accountant'    => $this->accountantData($schoolId),
            'librarian'     => $this->librarianData($schoolId),
            default         => $this->defaultData($schoolId),
        };

        return view('school-admin.dashboard.role-dashboard', array_merge($data, ['role' => $role]));
    }

    private function principalData(int $schoolId): array
    {
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('users.school_id', $schoolId)
            ->whereIn('roles.name', ['teacher', 'admin', 'principal'])
            ->where('users.is_active', true)
            ->count();

        $attendanceToday = DB::table('attendances')
            ->where('school_id', $schoolId)
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();

        $attendanceTotal = DB::table('attendances')
            ->where('school_id', $schoolId)
            ->whereDate('date', today())
            ->count();

        $attendanceRate = $attendanceTotal > 0 ? round(($attendanceToday / $attendanceTotal) * 100, 1) : 0;

        $revenueThisMonth = FeeInvoice::where('school_id', $schoolId)
            ->where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');

        $outstandingInvoices = FeeInvoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();

        $atRiskStudents = DB::table('attendances')
            ->where('school_id', $schoolId)
            ->where('status', 'absent')
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('student_id, COUNT(*) as absences')
            ->groupBy('student_id')
            ->having('absences', '>=', 5)
            ->count();

        return [
            'widgets' => [
                [
                    'title' => 'Total Siswa',
                    'value' => number_format($totalStudents),
                    'icon'  => 'students',
                    'color' => 'indigo',
                    'url'   => route('admin.students.index'),
                ],
                [
                    'title' => 'Total Guru & Staff',
                    'value' => number_format($totalTeachers),
                    'icon'  => 'people',
                    'color' => 'emerald',
                    'url'   => route('admin.staff.index'),
                ],
                [
                    'title' => 'Kehadiran Hari Ini',
                    'value' => $attendanceRate . '%',
                    'icon'  => 'calendar',
                    'color' => 'sky',
                    'url'   => route('admin.attendance.index'),
                ],
                [
                    'title' => 'Revenue Bulan Ini',
                    'value' => 'Rp ' . number_format($revenueThisMonth / 100, 0, ',', '.'),
                    'icon'  => 'finance',
                    'color' => 'amber',
                    'url'   => route('admin.finance.reports.summary'),
                ],
                [
                    'title' => 'Invoice Belum Dibayar',
                    'value' => number_format($outstandingInvoices),
                    'icon'  => 'tasks',
                    'color' => 'rose',
                    'url'   => route('admin.fee.invoices.index'),
                ],
                [
                    'title' => 'Siswa At-Risk (5+ Bolos)',
                    'value' => number_format($atRiskStudents),
                    'icon'  => 'bell',
                    'color' => 'orange',
                    'url'   => route('admin.analytics.dropout-risk.index'),
                ],
            ],
        ];
    }

    private function teacherData(int $schoolId, $user): array
    {
        $myClasses = DB::table('class_section_teacher')
            ->join('class_sections', 'class_section_teacher.class_section_id', '=', 'class_sections.id')
            ->where('class_section_teacher.teacher_id', $user->id)
            ->where('class_sections.school_id', $schoolId)
            ->count();

        $myStudents = DB::table('student_class_section')
            ->join('class_sections', 'student_class_section.class_section_id', '=', 'class_sections.id')
            ->join('class_section_teacher', 'class_sections.id', '=', 'class_section_teacher.class_section_id')
            ->where('class_section_teacher.teacher_id', $user->id)
            ->where('student_class_section.school_id', $schoolId)
            ->distinct('student_class_section.student_id')
            ->count('student_class_section.student_id');

        $pendingGrading = DB::table('exam_marks')
            ->join('exams', 'exam_marks.exam_id', '=', 'exams.id')
            ->where('exams.school_id', $schoolId)
            ->where('exams.created_by', $user->id)
            ->whereNull('exam_marks.score')
            ->count();

        $todayAttendance = DB::table('attendances')
            ->where('school_id', $schoolId)
            ->where('recorded_by', $user->id)
            ->whereDate('date', today())
            ->count();

        return [
            'widgets' => [
                [
                    'title' => 'Rombel Saya',
                    'value' => $myClasses,
                    'icon'  => 'academic',
                    'color' => 'indigo',
                    'url'   => route('admin.timetable.index'),
                ],
                [
                    'title' => 'Siswa Saya',
                    'value' => number_format($myStudents),
                    'icon'  => 'students',
                    'color' => 'emerald',
                    'url'   => route('admin.students.index'),
                ],
                [
                    'title' => 'Perlu Dinilai',
                    'value' => $pendingGrading,
                    'icon'  => 'tasks',
                    'color' => 'amber',
                    'url'   => route('admin.exams.index'),
                ],
                [
                    'title' => 'Absensi Hari Ini',
                    'value' => $todayAttendance . ' kelas',
                    'icon'  => 'calendar',
                    'color' => 'sky',
                    'url'   => route('admin.attendance.index'),
                ],
            ],
        ];
    }

    private function parentData(int $schoolId, $user): array
    {
        $children = $user->parentStudents()->where('students.school_id', $schoolId)->get();

        $childIds = $children->pluck('id')->toArray();

        $pendingInvoices = FeeInvoice::where('school_id', $schoolId)
            ->whereIn('student_id', $childIds)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();

        $recentGrades = DB::table('exam_marks')
            ->join('exams', 'exam_marks.exam_id', '=', 'exams.id')
            ->whereIn('exam_marks.student_id', $childIds)
            ->where('exams.school_id', $schoolId)
            ->orderByDesc('exam_marks.created_at')
            ->limit(5)
            ->get();

        $absenceAlerts = DB::table('attendances')
            ->whereIn('student_id', $childIds)
            ->where('school_id', $schoolId)
            ->where('status', 'absent')
            ->where('date', '>=', now()->subDays(14))
            ->count();

        return [
            'children'        => $children,
            'pendingInvoices' => $pendingInvoices,
            'recentGrades'    => $recentGrades,
            'absenceAlerts'   => $absenceAlerts,
            'widgets'         => [
                [
                    'title' => 'Anak Terdaftar',
                    'value' => $children->count(),
                    'icon'  => 'students',
                    'color' => 'indigo',
                    'url'   => route('portal.dashboard'),
                ],
                [
                    'title' => 'Invoice Pending',
                    'value' => $pendingInvoices,
                    'icon'  => 'finance',
                    'color' => 'amber',
                    'url'   => route('portal.invoices'),
                ],
                [
                    'title' => 'Peringatan Absensi',
                    'value' => $absenceAlerts . ' hari',
                    'icon'  => 'bell',
                    'color' => 'rose',
                    'url'   => route('portal.dashboard'),
                ],
            ],
        ];
    }

    private function studentData(int $schoolId, $user): array
    {
        $student = Student::where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        $upcomingExams = DB::table('exams')
            ->where('school_id', $schoolId)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->limit(5)
            ->get();

        $myGrades = DB::table('exam_marks')
            ->join('exams', 'exam_marks.exam_id', '=', 'exams.id')
            ->where('exam_marks.student_id', $student->id ?? 0)
            ->where('exams.school_id', $schoolId)
            ->orderByDesc('exam_marks.created_at')
            ->limit(5)
            ->get();

        $leaderboardRank = DB::table('leaderboard_entries')
            ->where('school_id', $schoolId)
            ->where('student_id', $student->id ?? 0)
            ->value('rank');

        return [
            'student'           => $student,
            'upcomingExams'     => $upcomingExams,
            'myGrades'          => $myGrades,
            'leaderboardRank'   => $leaderboardRank,
            'widgets'           => [
                [
                    'title' => 'Ujian Mendatang',
                    'value' => $upcomingExams->count(),
                    'icon'  => 'tasks',
                    'color' => 'indigo',
                    'url'   => route('student.exams.index'),
                ],
                [
                    'title' => 'Ranking',
                    'value' => $leaderboardRank ? '#' . $leaderboardRank : '-',
                    'icon'  => 'academic',
                    'color' => 'amber',
                    'url'   => route('student.leaderboard'),
                ],
                [
                    'title' => 'Jadwal Hari Ini',
                    'value' => DB::table('timetable_slots')
                        ->where('school_id', $schoolId)
                        ->where('day', strtolower(now()->englishDayOfWeek))
                        ->count() . ' jam',
                    'icon'  => 'calendar',
                    'color' => 'emerald',
                    'url'   => route('student.schedule'),
                ],
            ],
        ];
    }

    private function accountantData(int $schoolId): array
    {
        $revenueThisMonth = FeeInvoice::where('school_id', $schoolId)
            ->where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');

        $outstanding = FeeInvoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum(DB::raw('amount - paid_amount'));

        $recentPayments = FeeInvoice::where('school_id', $schoolId)
            ->where('status', 'paid')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $overdueCount = FeeInvoice::where('school_id', $schoolId)
            ->where('status', 'overdue')
            ->count();

        return [
            'widgets' => [
                [
                    'title' => 'Revenue Bulan Ini',
                    'value' => 'Rp ' . number_format($revenueThisMonth / 100, 0, ',', '.'),
                    'icon'  => 'finance',
                    'color' => 'emerald',
                    'url'   => route('admin.finance.reports.summary'),
                ],
                [
                    'title' => 'Belum Dibayar',
                    'value' => 'Rp ' . number_format($outstanding / 100, 0, ',', '.'),
                    'icon'  => 'tasks',
                    'color' => 'amber',
                    'url'   => route('admin.finance.reports.outstanding'),
                ],
                [
                    'title' => 'Overdue',
                    'value' => number_format($overdueCount),
                    'icon'  => 'bell',
                    'color' => 'rose',
                    'url'   => route('admin.fee.invoices.index'),
                ],
                [
                    'title' => 'Cash Flow',
                    'value' => 'Lihat →',
                    'icon'  => 'reports',
                    'color' => 'sky',
                    'url'   => route('admin.reports.cash-flow'),
                ],
            ],
            'recentPayments' => $recentPayments,
        ];
    }

    private function librarianData(int $schoolId): array
    {
        $borrowedBooks = DB::table('library_issues')
            ->where('school_id', $schoolId)
            ->whereNull('returned_at')
            ->count();

        $overdueBooks = DB::table('library_issues')
            ->where('school_id', $schoolId)
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->count();

        $totalBooks = DB::table('library_books')
            ->where('school_id', $schoolId)
            ->count();

        return [
            'widgets' => [
                [
                    'title' => 'Buku Dipinjam',
                    'value' => $borrowedBooks,
                    'icon'  => 'library',
                    'color' => 'indigo',
                    'url'   => route('admin.library.issues.index'),
                ],
                [
                    'title' => 'Terlambat Dikembalikan',
                    'value' => $overdueBooks,
                    'icon'  => 'bell',
                    'color' => 'rose',
                    'url'   => route('admin.library.issues.index'),
                ],
                [
                    'title' => 'Total Buku',
                    'value' => number_format($totalBooks),
                    'icon'  => 'academic',
                    'color' => 'emerald',
                    'url'   => route('admin.library.books.index'),
                ],
            ],
        ];
    }

    private function defaultData(int $schoolId): array
    {
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('users.school_id', $schoolId)
            ->whereIn('roles.name', ['teacher', 'admin'])
            ->count();

        return [
            'widgets' => [
                [
                    'title' => 'Total Siswa',
                    'value' => number_format($totalStudents),
                    'icon'  => 'students',
                    'color' => 'indigo',
                    'url'   => route('admin.students.index'),
                ],
                [
                    'title' => 'Total Guru',
                    'value' => number_format($totalTeachers),
                    'icon'  => 'people',
                    'color' => 'emerald',
                    'url'   => route('admin.staff.index'),
                ],
            ],
        ];
    }
}
