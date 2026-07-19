<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Communication\WaBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaBotWebhookController extends Controller
{
    public function __invoke(Request $request, WaBotService $service): JsonResponse
    {
        $phone   = $request->input('phone') ?? $request->input('sender') ?? $request->input('from');
        $message = $request->input('message') ?? $request->input('text') ?? $request->input('body');

        if (!$phone || !$message) {
            return response()->json(['success' => false, 'error' => 'Phone and message required'], 400);
        }

        $result = $service->processIncoming($phone, $message);

        $service->sendReply($phone, $result['reply']);

        return response()->json([
            'success'  => true,
            'reply'    => $result['reply'],
            'matched'  => $result['matched'],
            'command'  => $result['command'] ?? null,
        ]);
    }
}
