<?php

namespace App\Services\Transport;

use App\Models\Transport\DriverSchedule;
use Illuminate\Support\Collection;

class DriverScheduleService
{
    public function schedule(int $schoolId, int $routeId, string $date, string $shift, ?int $vehicleId, ?string $driverName, ?string $note = null): DriverSchedule
    {
        return DriverSchedule::updateOrCreate(
            [
                'school_id'          => $schoolId,
                'transport_route_id' => $routeId,
                'date'               => $date,
                'shift'              => $shift,
            ],
            [
                'vehicle_id'  => $vehicleId,
                'driver_name' => $driverName,
                'note'        => $note,
            ]
        );
    }

    public function forDate(int $schoolId, string $date): Collection
    {
        return DriverSchedule::where('school_id', $schoolId)
            ->where('date', $date)
            ->with(['route', 'vehicle'])
            ->orderBy('shift')
            ->orderBy('id')
            ->get();
    }

    public function forDriver(int $schoolId, string $driverName): Collection
    {
        return DriverSchedule::where('school_id', $schoolId)
            ->where('driver_name', $driverName)
            ->where('date', '>=', now()->toDateString())
            ->with(['route', 'vehicle'])
            ->orderBy('date')
            ->get();
    }
}
