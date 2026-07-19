<?php

namespace App\Http\Controllers\Web\Parent;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\Achievement\StudentAchievement;
use App\Models\Counseling\CounselingSession;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Finance\FeeInvoice;
use App\Models\Medical\ClinicVisit;
use App\Models\Medical\Vaccination;
use App\Services\ActivityTimelineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    public function dashboard(): View
    {
        $userId = auth()->id();

        // Children of this parent (via parent_student pivot)
        $children = Student::whereHas('parents', fn ($q) => $q->where('parent_id', $userId))
            ->with(['user:id,name', 'classSection.classRoom', 'classSection.section'])
            ->get();

        // Outstanding invoices summary
        $outstandingTotal = FeeInvoice::where('school_id', auth()->user()->school_id)
            ->whereIn('student_id', $children->pluck('id'))
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('SUM(amount - paid_amount) as t')
            ->value('t') ?? 0;

        return view('parent-portal.dashboard', [
            'children'         => $children,
            'outstandingTotal' => (int) $outstandingTotal,
        ]);
    }

    public function child(Student $student): View
    {
        $this->authorizeChild($student);

        // Last 30 days attendance
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')->pluck('cnt', 'status');

        // Recent marks (last 10)
        $recentMarks = Mark::where('student_id', $student->id)
            ->with(['subject:id,name'])
            ->orderByDesc('created_at')->limit(10)->get();

        // Outstanding & paid
        $outstanding = (int) FeeInvoice::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('SUM(amount - paid_amount) as t')->value('t') ?? 0;

        return view('parent-portal.child', [
            'student'     => $student->load(['user', 'classSection.classRoom', 'classSection.section']),
            'attendance'  => $attendance,
            'recentMarks' => $recentMarks,
            'outstanding' => $outstanding,
        ]);
    }

    public function childAttendance(Student $student): View
    {
        $this->authorizeChild($student);
        $records = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')->paginate(40);
        return view('parent-portal.child-attendance', compact('student', 'records'));
    }

    public function childMarks(Student $student): View
    {
        $this->authorizeChild($student);
        $marks = Mark::where('student_id', $student->id)
            ->with(['subject:id,name', 'exam:id,title'])
            ->orderByDesc('created_at')->paginate(40);
        return view('parent-portal.child-marks', compact('student', 'marks'));
    }

    public function childHealth(Student $student): View
    {
        $this->authorizeChild($student);
        $visits = ClinicVisit::where('student_id', $student->id)
            ->orderByDesc('visit_at')->paginate(20);
        $vaccinations = Vaccination::where('student_id', $student->id)
            ->orderByDesc('vaccinated_at')->get();
        return view('parent-portal.child-health', compact('student', 'visits', 'vaccinations'));
    }

    public function childDiscipline(Student $student): View
    {
        $this->authorizeChild($student);
        $records = DisciplineRecord::where('student_id', $student->id)
            ->with('category')
            ->orderByDesc('incident_date')->paginate(20);
        $totalPoints = (int) DisciplineRecord::where('student_id', $student->id)->sum('points');
        return view('parent-portal.child-discipline', compact('student', 'records', 'totalPoints'));
    }

    public function childAchievements(Student $student): View
    {
        $this->authorizeChild($student);
        $achievements = StudentAchievement::where('student_id', $student->id)
            ->with('category')->orderByDesc('achieved_at')->paginate(20);
        return view('parent-portal.child-achievements', compact('student', 'achievements'));
    }

    public function childCounseling(Student $student): View
    {
        $this->authorizeChild($student);
        $sessions = CounselingSession::where('student_id', $student->id)
            ->orderByDesc('scheduled_at')->paginate(20);
        return view('parent-portal.child-counseling', compact('student', 'sessions'));
    }

    public function childActivity(Student $student): View
    {
        $this->authorizeChild($student);
        $service = app(ActivityTimelineService::class);
        $activities = $service->getTimeline($student->id, request()->all());
        $grouped = $service->groupByDate($student->id);

        return view('parent-portal.child-activity', compact('student', 'activities', 'grouped', 'service'));
    }

    private function authorizeChild(Student $student): void
    {
        $userId = auth()->id();
        $isParent = DB::table('parent_student')
            ->where('parent_id', $userId)
            ->where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->exists();
        abort_unless($isParent || auth()->user()->hasRole(['admin', 'super_admin']), 403);
    }
}
