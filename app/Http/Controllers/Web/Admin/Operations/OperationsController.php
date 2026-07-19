<?php

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Gate\IdGateDevice;
use App\Models\Gate\IdGateEvent;
use App\Models\Transport\VehicleTrip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OperationsController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }
    private function authorizeOwn($model): void { abort_unless($model->school_id === $this->schoolId(), 403); }

    /* ============== ID GATE DEVICES ============== */

    public function gateDevices(): View
    {
        return view('school-admin.operations.gate-devices', [
            'devices' => IdGateDevice::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeGateDevice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:200',
            'location' => 'required|string|max:200',
            'type'     => 'required|in:entry,exit,both',
        ]);
        $token = Str::random(40);
        IdGateDevice::create([
            'school_id'              => $this->schoolId(),
            'name'                   => $data['name'],
            'location'               => $data['location'],
            'type'                   => $data['type'],
            'device_token_encrypted' => Crypt::encryptString($token),
            'is_active'              => true,
        ]);
        return back()->with('success', "Gerbang ditambahkan. Token: {$token} (simpan sekarang!)");
    }

    public function deleteGateDevice(IdGateDevice $device): RedirectResponse
    {
        $this->authorizeOwn($device);
        $device->delete();
        return back()->with('success', 'Gerbang dihapus.');
    }

    public function gateEvents(): View
    {
        return view('school-admin.operations.gate-events', [
            'events' => IdGateEvent::where('school_id', $this->schoolId())
                ->with(['device:id,name', 'user:id,name'])
                ->orderByDesc('scanned_at')->paginate(50),
        ]);
    }

    /* ============== VEHICLE TRIPS / BUS TRACKING ============== */

    public function vehicleTrips(): View
    {
        return view('school-admin.operations.vehicle-trips', [
            'trips' => VehicleTrip::where('school_id', $this->schoolId())
                ->orderByDesc('created_at')->paginate(25),
        ]);
    }
}
