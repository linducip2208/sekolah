<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor\VisitorQrSession;
use App\Models\Visitor\VisitorVisit;
use App\Services\Visitor\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorScanController extends Controller
{
    public function __construct(private readonly VisitorService $service) {}

    public function scan(Request $request): JsonResponse
    {
        $token = $request->input('qr_token') ?? $request->input('token');

        if (! $token) {
            return response()->json(['success' => false, 'error' => 'QR token required'], 400);
        }

        $visit = VisitorVisit::withoutGlobalScopes()->where('qr_token', $token)->first();
        if ($visit) {
            try {
                $visit = $this->service->checkIn((int) $visit->school_id, (int) $visit->id, auth()->id());

                return response()->json([
                    'success' => true,
                    'visitor_name' => $visit->visitor?->name,
                    'purpose' => $visit->purpose,
                    'checked_in_at' => $visit->check_in_at?->toISOString(),
                    'badge_number' => $visit->badge_number,
                ]);
            } catch (\RuntimeException $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
        }

        $session = VisitorQrSession::where('qr_token', $token)->first();

        if (! $session) {
            return response()->json(['success' => false, 'error' => 'Token tidak valid'], 404);
        }

        if ($session->scanned_at) {
            return response()->json([
                'success' => false,
                'error' => 'QR sudah digunakan',
                'scanned_at' => $session->scanned_at->toISOString(),
            ], 409);
        }

        if ($session->expires_at->isPast()) {
            return response()->json(['success' => false, 'error' => 'QR sudah kadaluarsa'], 410);
        }

        $visitor = $session->visitorLog;
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-ID') ?? 'gate-scanner';
        $scannedBy = $request->input('scanned_by') ?? $request->header('X-Scanned-By') ?? $deviceId;

        $session->update([
            'scanned_at' => now(),
            'scanned_by' => $scannedBy,
        ]);

        $visitor->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
        if (auth()->id()) {
            $visitor->update(['logged_by' => auth()->id()]);
        }

        return response()->json([
            'success' => true,
            'visitor_name' => $visitor->visitor_name,
            'purpose' => $visitor->purpose,
            'host_staff' => $visitor->hostStaff?->user?->name,
            'checked_in_at' => $visitor->checked_in_at->toISOString(),
        ]);
    }
}
