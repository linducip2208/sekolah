<?php

namespace App\Http\Controllers\Web\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
use App\Services\Transport\DriverScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverScheduleController extends Controller
{
    public function __construct(private DriverScheduleService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $date = $request->date ?? now()->toDateString();

        $schedules = $this->service->forDate($schoolId, $date);
        $routes    = TransportRoute::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $vehicles  = Vehicle::where('school_id', $schoolId)->orderBy('registration_no')->get();

        return view('school-admin.transport.driver-schedule', compact('schedules', 'routes', 'vehicles', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'date'               => 'required|date',
            'shift'              => 'required|in:morning,afternoon',
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'driver_name'        => 'nullable|string|max:200',
            'note'               => 'nullable|string|max:255',
        ]);

        $this->service->schedule(
            $this->schoolId(),
            (int) $data['transport_route_id'],
            $data['date'],
            $data['shift'],
            $data['vehicle_id'] ?? null,
            $data['driver_name'] ?? null,
            $data['note'] ?? null,
        );

        return back()->with('success', 'Jadwal sopir disimpan.');
    }

    public function destroy(\App\Models\Transport\DriverSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->school_id === $this->schoolId(), 403);
        $schedule->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }
}
