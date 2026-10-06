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
        // Shared-secret gate: when WA_BOT_WEBHOOK_SECRET is configured, the
        // gateway must sign requests (X-Signature = HMAC-SHA256 of raw body).
        // Unconfigured installations log a warning but keep working so
        // existing gateways are not broken by deploy.
        $secret = (string) config('services.wabot.webhook_secret', '');
        if ($secret !== '') {
            $given = (string) $request->header('X-Signature', '');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if ($given === '' || ! hash_equals($expected, $given)) {
                return response()->json(['success' => false, 'error' => 'Invalid signature'], 401);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('WA bot webhook called without WA_BOT_WEBHOOK_SECRET configured');
        }

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
