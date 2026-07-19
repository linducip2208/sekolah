<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\Assignment;
use App\Models\Academic\Attendance;
use App\Models\Academic\Lesson;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\Academic\TimetableSlot;
use App\Services\LeaderboardService;
use App\Models\Finance\FeeInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    private function student(): Student
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404, 'Profil siswa tidak ditemukan.');
        return $student;
    }

    public function dashboard(): View
    {
        $student = $this->student();
        $today = now()->dayOfWeekIso;

        $todaySchedule = TimetableSlot::where('class_section_id', $student->class_section_id)
            ->where('day_of_week', $today)
            ->with(['subject:id,name', 'teacher:id,name'])
            ->orderBy('start_time')->get();

        $recentMarks = Mark::where('student_id', $student->id)
            ->with('subject:id,name')->orderByDesc('created_at')->limit(5)->get();

        $unpaidInvoices = FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])->count();

        $attendance30 = Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->pluck('cnt', 'status');

        return view('student-portal.dashboard', compact(
            'student', 'todaySchedule', 'recentMarks', 'unpaidInvoices', 'attendance30'
        ));
    }

    public function schedule(): View
    {
        $student = $this->student();
        $slots = TimetableSlot::where('class_section_id', $student->class_section_id)
            ->with(['subject:id,name', 'teacher:id,name'])
            ->orderBy('day_of_week')->orderBy('start_time')->get()->groupBy('day_of_week');

        return view('student-portal.schedule', compact('student', 'slots'));
    }

    public function marks(): View
    {
        $student = $this->student();
        $marks = Mark::where('student_id', $student->id)
            ->with(['subject:id,name', 'exam:id,title'])
            ->orderByDesc('created_at')->paginate(40);
        return view('student-portal.marks', compact('student', 'marks'));
    }

    public function attendance(): View
    {
        $student = $this->student();
        $records = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')->paginate(40);
        return view('student-portal.attendance', compact('student', 'records'));
    }

    public function lessons(): View
    {
        $student = $this->student();
        $lessons = Lesson::where('class_section_id', $student->class_section_id)
            ->with(['subject:id,name', 'teacher:id,name'])
            ->orderByDesc('created_at')->paginate(20);
        return view('student-portal.lessons', compact('student', 'lessons'));
    }

    public function assignments(): View
    {
        $student = $this->student();
        $assignments = Assignment::whereHas('lesson', fn($q) => $q->where('class_section_id', $student->class_section_id))
            ->with('lesson.subject:id,name')
            ->orderBy('due_date')->paginate(20);
        return view('student-portal.assignments', compact('student', 'assignments'));
    }

    public function leaderboard(Request $request): View
    {
        $student = $this->student();
        $schoolId = $student->school_id;
        $configType = $request->query('period', 'monthly');

        $service = app(LeaderboardService::class);
        $rankings = $service->calculateRankings($schoolId, $configType, $student->class_section_id);
        $myRanking = $service->getStudentRanking($schoolId, $student->id, $configType);

        $top3 = array_slice($rankings, 0, 3);
        $remaining = array_slice($rankings, 3);

        $periodLabel = match ($configType) {
            'weekly'   => 'Minggu Ini',
            'monthly'  => 'Bulan Ini',
            'semester' => 'Semester Ini',
            default    => 'Bulan Ini',
        };

        return view('student-portal.leaderboard', compact(
            'student', 'rankings', 'myRanking', 'top3', 'remaining', 'configType', 'periodLabel'
        ));
    }

    public function qrAttendance(): View
    {
        return view('student-portal.qr-attendance');
    }
}
