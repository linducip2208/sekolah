<?php

namespace App\Services\Notification\Adapters;

use App\Models\Communication\NotificationProvider;
use App\Services\Notification\Contracts\NotificationAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmLegacyAdapter implements NotificationAdapter
{
    public function send(NotificationProvider $provider, array $recipients, string $title, string $body, array $data = []): array
    {
        $apiKey = $provider->api_key;
        $endpoint = $provider->base_url ?: 'https://fcm.googleapis.com/fcm/send';
        if (!$apiKey || empty($recipients)) {
            return ['sent' => 0, 'failed' => count($recipients), 'error' => 'missing_credentials_or_recipients'];
        }

        $sent = 0;
        $failed = 0;
        foreach (array_chunk($recipients, 500) as $chunk) {
            try {
                $resp = Http::withHeaders(array_merge([
                    'Authorization' => 'key=' . $apiKey,
                    'Content-Type'  => 'application/json',
                ], $provider->extra_headers ?? []))
                    ->timeout(15)
                    ->post($endpoint, [
                        'registration_ids' => $chunk,
                        'notification'     => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                        'data'             => $data,
                        'priority'         => 'high',
                    ]);
                if ($resp->successful()) {
                    $sent   += (int) $resp->json('success', 0);
                    $failed += (int) $resp->json('failure', 0);
                } else {
                    $failed += count($chunk);
                }
            } catch (\Throwable $e) {
                $failed += count($chunk);
                Log::warning('FCM legacy adapter failed', ['error' => $e->getMessage()]);
            }
        }
        return ['sent' => $sent, 'failed' => $failed];
    }
}
