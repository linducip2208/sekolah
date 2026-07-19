<?php

namespace App\Services\Notification\Adapters;

use App\Models\Communication\NotificationProvider;
use App\Services\Notification\Contracts\NotificationAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic REST adapter — sends a POST to base_url with extra_headers.
 * Payload mapping is configured via extra_config:
 *   - method: POST|GET (default POST)
 *   - auth: bearer|basic|key_header|query (how to send api_key)
 *   - auth_param: header/key name (default Authorization)
 *   - to_field: field name for recipient (default 'to')
 *   - message_field: field for message body (default 'message')
 *   - title_field: optional title field
 *   - sender_field: optional sender id field
 *   - extra_payload: array of static fields to merge in
 *
 * Sends one request per recipient (so works for both single and bulk).
 */
class RestGenericAdapter implements NotificationAdapter
{
    public function send(NotificationProvider $provider, array $recipients, string $title, string $body, array $data = []): array
    {
        $cfg = $provider->extra_config ?? [];
        $method        = strtoupper($cfg['method'] ?? 'POST');
        $authMode      = $cfg['auth'] ?? 'bearer';
        $authParam     = $cfg['auth_param'] ?? 'Authorization';
        $toField       = $cfg['to_field'] ?? 'to';
        $msgField      = $cfg['message_field'] ?? 'message';
        $titleField    = $cfg['title_field'] ?? null;
        $senderField   = $cfg['sender_field'] ?? null;
        $extraPayload  = $cfg['extra_payload'] ?? [];

        $headers = array_merge(['Accept' => 'application/json'], $provider->extra_headers ?? []);
        $apiKey  = $provider->api_key;

        switch ($authMode) {
            case 'bearer':
                if ($apiKey) $headers[$authParam] = 'Bearer ' . $apiKey;
                break;
            case 'basic':
                if ($apiKey) $headers[$authParam] = 'Basic ' . base64_encode($apiKey . ':' . ($provider->secret ?? ''));
                break;
            case 'key_header':
                if ($apiKey) $headers[$authParam] = $apiKey;
                break;
        }

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $to) {
            $payload = array_merge($extraPayload, [
                $toField  => $to,
                $msgField => $body,
            ]);
            if ($titleField)  $payload[$titleField]  = $title;
            if ($senderField) $payload[$senderField] = $provider->sender_id;

            try {
                $http = Http::withHeaders($headers)->timeout(15);
                if ($authMode === 'query' && $apiKey) {
                    $http = $http->withQueryParameters([$authParam => $apiKey]);
                }
                $resp = $method === 'GET'
                    ? $http->get($provider->base_url, $payload)
                    : $http->post($provider->base_url, $payload);

                $resp->successful() ? $sent++ : $failed++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('REST generic adapter failed', [
                    'provider' => $provider->id, 'to' => $to, 'error' => $e->getMessage(),
                ]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
