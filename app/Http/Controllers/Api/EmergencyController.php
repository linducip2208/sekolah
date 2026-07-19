<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency\EmergencyAlert;
use App\Services\EmergencyAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function __construct(
        private EmergencyAlertService $alertService
    ) {}

    public function panic(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'message'   => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        $alert = $this->alertService->sendPanicAlert(
            $user->id,
            $request->latitude,
            $request->longitude
        );

        return response()->json([
            'success' => true,
            'message' => 'Peringatan darurat dikirim.',
            'alert'   => [
                'id'         => $alert->id,
                'status'     => $alert->status,
                'created_at' => $alert->created_at,
            ],
        ]);
    }

    public function recent(): JsonResponse
    {
        $schoolId = auth()->user()->school_id;

        $alerts = EmergencyAlert::where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'alert_type', 'title', 'severity', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'alerts'  => $alerts,
        ]);
    }

    public function contacts(): JsonResponse
    {
        $schoolId = auth()->user()->school_id;

        $contacts = \App\Models\Emergency\EmergencyContact::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('priority_order')
            ->get();

        return response()->json([
            'success'  => true,
            'contacts' => $contacts,
        ]);
    }
}
