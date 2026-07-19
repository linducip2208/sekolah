<?php

namespace App\Http\Controllers\Web\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\Student;
use App\Models\Academic\TimetableSlot;
use App\Models\LessonPlan\LessonPlan;
use App\Models\LiveClass\LiveClassSession;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function dashboard(): View
    {
        $userId = auth()->id();
        $today = now()->dayOfWeekIso;

        // Class sections where I'm wali kelas
        $myClasses = ClassSection::where('class_teacher_id', $userId)
            ->with(['classRoom', 'section'])->withCount('students')->get();

        // My schedule today
        $todaySchedule = TimetableSlot::where('teacher_id', $userId)
            ->where('day_of_week', $today)
            ->with(['subject:id,name', 'classSection.classRoom', 'classSection.section'])
            ->orderBy('start_time')->get();

        // My recent lesson plans
        $recentRpp = LessonPlan::where('teacher_id', $userId)
            ->with(['subject:id,name', 'classSection.classRoom'])
            ->orderByDesc('created_at')->limit(5)->get();

        // Upcoming live class
        $upcomingLive = LiveClassSession::where('teacher_id', $userId)
            ->where('scheduled_start', '>=', now())
            ->orderBy('scheduled_start')->limit(5)->get();

        // Exams for my class sections (as wali kelas)
        $myClassIds = ClassSection::where('class_teacher_id', $userId)->pluck('id');
        $pendingExams = Exam::whereIn('class_section_id', $myClassIds)
            ->orderByDesc('start_at')->limit(5)->get();

        return view('teacher-portal.dashboard', compact(
            'myClasses', 'todaySchedule', 'recentRpp', 'upcomingLive', 'pendingExams'
        ));
    }

    public function myClass(ClassSection $classSection): View
    {
        $userId = auth()->id();
        abort_unless($classSection->class_teacher_id === $userId || auth()->user()->hasRole('admin'), 403);

        $students = Student::where('class_section_id', $classSection->id)
            ->with('user:id,name')->orderBy('admission_no')->get();

        // Recent attendance summary per student (last 30 days)
        $studentIds = $students->pluck('id');
        $attendanceSummary = Attendance::whereIn('student_id', $studentIds)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('student_id, status, COUNT(*) as cnt')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        return view('teacher-portal.my-class', compact('classSection', 'students', 'attendanceSummary'));
    }
}
