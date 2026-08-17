<?php

namespace App\Http\Controllers\Web\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Models\Facilities\TransportRoute;
use App\Services\Transport\TransportAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportAttendanceController extends Controller
{
    public function __construct(private TransportAttendanceService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $routeId  = $request->route_id;
        $date     = $request->date ?? now()->toDateString();
        $direction = $request->direction ?? 'to_school';

        $routes = TransportRoute::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();

        $students = collect();
        if ($routeId) {
            $students = $this->service->studentsForRoute($schoolId, (int) $routeId);
        }

        $existing = \App\Models\Transport\TransportAttendance::where('school_id', $schoolId)
            ->when($routeId, fn ($q) => $q->where('transport_route_id', $routeId))
            ->where('date', $date)
            ->where('direction', $direction)
            ->get()
            ->keyBy('student_id');

        return view('school-admin.transport.attendance', [
            'routes'    => $routes,
            'routeId'   => $routeId,
            'date'      => $date,
            'direction' => $direction,
            'students'  => $students,
            'existing'  => $existing,
            'summary'   => $this->service->summary($schoolId, $date),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'date'               => 'required|date',
            'direction'          => 'required|in:to_school,from_school',
            'attendance'         => 'required|array',
            'attendance.*'       => 'in:present,absent',
        ]);

        $count = $this->service->mark(
            $this->schoolId(),
            (int) $data['transport_route_id'],
            $data['date'],
            $data['direction'],
            $data['attendance']
        );

        return back()->with('success', "$count siswa tercatat kehadirannya.");
    }
}
