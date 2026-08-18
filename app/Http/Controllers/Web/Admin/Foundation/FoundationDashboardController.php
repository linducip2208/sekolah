<?php

namespace App\Http\Controllers\Web\Admin\Foundation;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationMasterData;
use App\Models\Foundation\FoundationUserManagement;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundationDashboardController extends Controller
{
    private function getFoundation(): Foundation
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);
        abort_unless($school->foundation_id, 404, 'Sekolah ini tidak terafiliasi dengan yayasan.');
        return Foundation::with('schools')->findOrFail($school->foundation_id);
    }

    public function index(): View
    {
        $foundation = $this->getFoundation();
        $foundation->load(['schools' => function ($q) {
            $q->withCount(['users as student_count' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'student'))]);
        }]);

        $schoolIds = $foundation->schools->pluck('id')->toArray();

        $studentCounts = \App\Models\Academic\Student::whereIn('school_id', $schoolIds)
            ->selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->pluck('total', 'school_id');

        $attendanceRates = \App\Models\Academic\Attendance::whereIn('school_id', $schoolIds)
            ->whereDate('date', '>=', now()->subDays(30))
            ->selectRaw('school_id,
                round(sum(case when status = \'present\' then 1 else 0 end) / count(*) * 100, 1) as rate')
            ->groupBy('school_id')
            ->pluck('rate', 'school_id');

        $schoolsData = $foundation->schools->map(function ($school) use ($studentCounts, $attendanceRates) {
            return [
                'id'              => $school->id,
                'name'            => $school->name,
                'student_count'   => $studentCounts->get($school->id, 0),
                'attendance_rate' => $attendanceRates->get($school->id, 0),
            ];
        });

        return view('school-admin.foundation.dashboard', [
            'foundation'  => $foundation,
            'schoolsData' => $schoolsData,
            'totalStudents'=> $studentCounts->sum(),
        ]);
    }
}
