<?php

namespace App\Http\Controllers\Api\Gate;

use App\Http\Controllers\Controller;
use App\Models\Gate\StudentIdCard;
use App\Services\Gate\IdGateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdGateController extends Controller
{
    public function __construct(private IdGateService $service) {}

    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_token' => 'required|string',
            'card'         => 'required|string|max:200',
            'direction'    => 'required|in:in,out',
        ]);

        $device = $this->service->authenticateDevice($data['device_token']);
        if (!$device) {
            return response()->json(['message' => 'Unknown device'], 401);
        }

        try {
            $event = $this->service->scan($device, $data['card'], $data['direction']);
            return response()->json(['ok' => true, 'event' => $event], 201);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Card not recognized'], 404);
        }
    }

    public function issueCard(Request $request, int $studentId): JsonResponse
    {
        $card = $this->service->issueCard($request->user()->school_id, $studentId);
        return response()->json($card, 201);
    }

    public function rotateQr(Request $request, int $cardId): JsonResponse
    {
        $card = StudentIdCard::where('school_id', $request->user()->school_id)
            ->findOrFail($cardId);

        return response()->json($this->service->rotateQrToken($card));
    }

    public function gateEventsForChild(Request $request, int $studentId): JsonResponse
    {
        $student = \App\Models\Academic\Student::where('school_id', $request->user()->school_id)
            ->findOrFail($studentId);

        $userId = $student->user_id;
        if (!$userId) return response()->json(['data' => []]);

        return response()->json([
            'data' => $this->service->eventsForUser($userId, (int) $request->input('days', 30)),
        ]);
    }
}
