<?php

namespace App\Http\Controllers\Web\Admin\Phase8;

use App\Http\Controllers\Controller;
use App\Models\Counseling\BullyingReport;
use App\Models\Counseling\CounselingSession;
use App\Models\Discipline\DisciplineCategory;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Gate\IdGateEvent;
use App\Models\Medical\ClinicVisit;
use App\Models\Medical\Vaccination;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\Transport\VehicleTrip;
use App\Models\Wellness\WellnessCheckin;
use Illuminate\View\View;

class Phase8WebController extends Controller
{
    // PPDB
    public function ppdbDashboard(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.ppdb.dashboard', [
            'periods'      => PpdbPeriod::where('school_id', $schoolId)->orderByDesc('open_date')->get(),
            'applications' => PpdbApplication::where('school_id', $schoolId)
                ->orderByDesc('created_at')->limit(50)->get(),
            'stats' => [
                'total'     => PpdbApplication::where('school_id', $schoolId)->count(),
                'submitted' => PpdbApplication::where('school_id', $schoolId)->where('status', 'submitted')->count(),
                'accepted'  => PpdbApplication::where('school_id', $schoolId)->where('status', 'accepted')->count(),
                'rejected'  => PpdbApplication::where('school_id', $schoolId)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    // Transport / Bus tracking
    public function transportDashboard(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.transport.dashboard', [
            'activeTrips' => VehicleTrip::where('school_id', $schoolId)
                ->where('status', 'active')->with('vehicle')->get(),
            'gateEvents' => IdGateEvent::where('school_id', $schoolId)
                ->whereDate('scanned_at', today())->orderByDesc('scanned_at')->limit(50)->get(),
        ]);
    }

    // UKS / Klinik
    public function clinicDashboard(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.medical.dashboard', [
            'recentVisits' => ClinicVisit::where('school_id', $schoolId)
                ->orderByDesc('visit_at')->limit(50)->get(),
            'todayCount' => ClinicVisit::where('school_id', $schoolId)
                ->whereDate('visit_at', today())->count(),
            'sentHomeCount' => ClinicVisit::where('school_id', $schoolId)
                ->whereDate('visit_at', today())->where('sent_home', true)->count(),
            'recentVaccinations' => Vaccination::where('school_id', $schoolId)
                ->orderByDesc('vaccinated_at')->limit(20)->get(),
        ]);
    }

    // BP/BK + Discipline
    public function counselingDashboard(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.counseling.dashboard', [
            'upcomingSessions' => CounselingSession::where('school_id', $schoolId)
                ->where('scheduled_at', '>=', now())
                ->where('status', 'scheduled')
                ->orderBy('scheduled_at')->limit(20)->get(),
            'openBullyingReports' => BullyingReport::where('school_id', $schoolId)
                ->whereNotIn('status', ['closed', 'unfounded'])
                ->orderByDesc('created_at')->get(),
            'flaggedWellness' => WellnessCheckin::where('school_id', $schoolId)
                ->where('flagged_for_review', true)
                ->where('checkin_date', '>=', now()->subDays(14))
                ->get(),
        ]);
    }

    public function disciplineDashboard(): View
    {
        $schoolId = auth()->user()->school_id;
        return view('school-admin.discipline.dashboard', [
            'categories'   => DisciplineCategory::where('school_id', $schoolId)->orderBy('type')->get(),
            'recentRecords' => DisciplineRecord::where('school_id', $schoolId)
                ->orderByDesc('incident_date')->limit(50)->get(),
            'leaderboard' => DisciplineRecord::where('school_id', $schoolId)
                ->selectRaw('student_id, SUM(points) as total_points')
                ->groupBy('student_id')
                ->orderByDesc('total_points')
                ->limit(10)->get(),
        ]);
    }
}
