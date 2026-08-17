<?php

namespace App\Http\Controllers\Web\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Services\Transport\TransportTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransportTrackingController extends Controller
{
    public function __construct(private TransportTrackingService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $locations = $this->service->latestLocations($this->schoolId());

        return view('school-admin.transport.tracking', [
            'locations' => $locations,
            'staleCount'=> $this->service->staleVehicles($this->schoolId())->count(),
        ]);
    }

    public function latest(): JsonResponse
    {
        $locations = $this->service->latestLocations($this->schoolId());

        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'vehicles'   => $locations->map(fn ($loc) => [
                'id'              => $loc->vehicle_id,
                'registration_no' => $loc->vehicle?->registration_no ?? '—',
                'driver_name'     => $loc->vehicle?->driver_name,
                'lat'             => (float) $loc->lat,
                'lng'             => (float) $loc->lng,
                'speed_kmh'       => (float) $loc->speed_kmh,
                'heading_deg'     => (float) $loc->heading_deg,
                'recorded_at'     => $loc->recorded_at?->toIso8601String(),
                'recorded_human'  => $loc->recorded_at?->diffForHumans(),
            ])->values(),
        ]);
    }
}
