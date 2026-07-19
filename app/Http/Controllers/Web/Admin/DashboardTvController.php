<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Academic\AcademicYear;
use App\Models\Event\SchoolEvent;
use App\Models\Finance\FeePayment;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardTvController extends Controller
{
    public function config(): View
    {
        $school = School::find(auth()->user()->school_id);
        $tvConfig = $school->getSetting('dashboard_tv', []);

        return view('school-admin.signage.dashboard-tv-config', compact('tvConfig'));
    }

    public function saveConfig(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'show_attendance'     => 'boolean',
            'show_revenue'        => 'boolean',
            'show_attendance_chart'=> 'boolean',
            'show_activities'     => 'boolean',
            'show_events'         => 'boolean',
            'show_ticker'         => 'boolean',
            'refresh_interval'    => 'nullable|integer|min:10|max:600',
        ]);

        $school = School::find(auth()->user()->school_id);
        $currentSettings = $school->settings ?? [];
        $currentSettings['dashboard_tv'] = $data;
        $school->update(['settings' => $currentSettings]);

        return back()->with('success', 'Konfigurasi Dashboard TV disimpan.');
    }

    public function display(Request $request, int $schoolId): View
    {
        $school = School::findOrFail($schoolId);
        $tvConfig = $school->getSetting('dashboard_tv', []);
        $refreshInterval = $tvConfig['refresh_interval'] ?? 30;

        $today = Carbon::today();

        // Today's attendance
        $todayAttendance = [];
        if (($tvConfig['show_attendance'] ?? true)) {
            $totalStudents = Student::where('school_id', $schoolId)->count();
            $presentCount = Attendance::where('school_id', $schoolId)
                ->whereDate('date', $today)
                ->where('status', 'present')
                ->count();
            $attendancePercent = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;
            $todayAttendance = compact('totalStudents', 'presentCount', 'attendancePercent');
        }

        // Today's revenue
        $todayRevenue = 0;
        if (($tvConfig['show_revenue'] ?? true)) {
            $todayRevenue = FeePayment::whereDate('payment_date', $today)
                ->whereHas('invoice', fn ($q) => $schoolId)
                ->sum('amount');
        }

        // Attendance by class chart data
        $attendanceChartData = [];
        if (($tvConfig['show_attendance_chart'] ?? true)) {
            $classSections = ClassSection::where('school_id', $schoolId)
                ->with(['classRoom', 'section'])
                ->get();

            foreach ($classSections as $cs) {
                $total = Student::where('class_section_id', $cs->id)->count();
                if ($total === 0) continue;
                $present = Attendance::where('school_id', $schoolId)
                    ->where('class_section_id', $cs->id)
                    ->whereDate('date', $today)
                    ->where('status', 'present')
                    ->count();

                $attendanceChartData[] = [
                    'label'   => $cs->classRoom->name . ' ' . $cs->section->name,
                    'present' => $present,
                    'total'   => $total,
                    'percent' => round(($present / $total) * 100, 1),
                ];
            }
        }

        // Recent activities (last 10 attendances + payments)
        $recentActivities = [];
        if (($tvConfig['show_activities'] ?? true)) {
            $recentAttendance = Attendance::where('school_id', $schoolId)
                ->with(['student.user', 'classSection.classRoom', 'classSection.section'])
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn ($a) => [
                    'time'    => $a->created_at->format('H:i'),
                    'type'    => 'attendance',
                    'label'   => $a->student->user->name ?? 'Siswa',
                    'detail'  => ($a->classSection->classRoom->name ?? '') . ' ' . ($a->classSection->section->name ?? ''),
                    'status'  => $a->status,
                ]);

            $recentPayments = FeePayment::with(['invoice', 'invoice.student.user', 'collector'])
                ->whereHas('invoice', fn ($q) => $schoolId)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn ($p) => [
                    'time'    => $p->created_at->format('H:i'),
                    'type'    => 'payment',
                    'label'   => $p->invoice->student->user->name ?? 'Siswa',
                    'detail'  => 'Rp ' . number_format($p->amount / 100, 0, ',', '.'),
                    'status'  => 'paid',
                ]);

            $recentActivities = $recentAttendance
                ->merge($recentPayments)
                ->sortByDesc('time')
                ->take(10)
                ->values();
        }

        // Upcoming events
        $upcomingEvents = [];
        if (($tvConfig['show_events'] ?? true)) {
            $upcomingEvents = SchoolEvent::where('school_id', $schoolId)
                ->where('is_published', true)
                ->where('starts_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(3)
                ->get();
        }

        // School stats
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->count();
        $totalRombel = ClassSection::where('school_id', $schoolId)->count();

        $activeAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $academicYearLabel = $activeAcademicYear
            ? "{$activeAcademicYear->name}"
            : date('Y') . '/' . (date('Y') + 1);

        return view('signage.dashboard-tv', compact(
            'school', 'tvConfig', 'refreshInterval',
            'todayAttendance', 'todayRevenue', 'attendanceChartData',
            'recentActivities', 'upcomingEvents',
            'totalStudents', 'totalTeachers', 'totalRombel', 'academicYearLabel'
        ));
    }
}
