<?php

namespace App\Services\Communication\Adapters;

use App\Models\Communication\NotificationProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppAdapter
{
    private NotificationProvider $provider;

    public function __construct(NotificationProvider $provider)
    {
        $this->provider = $provider;
    }

    public function send(string $phone, string $message): array
    {
        $format = $this->provider->api_format;
        $baseUrl = rtrim($this->provider->base_url, '/');
        $apiKey = $this->provider->api_key;
        $senderId = $this->provider->sender_id;
        $config = $this->provider->extra_config ?? [];

        $phone = $this->normalizePhone($phone);

        return match ($format) {
            'chatgo'     => $this->sendChatGo($baseUrl, $apiKey, $phone, $message, $config),
            'wablas'     => $this->sendWablas($baseUrl, $apiKey, $phone, $message),
            'fonnte'     => $this->sendFonnte($baseUrl, $apiKey, $phone, $message, $senderId),
            'whacenter'  => $this->sendWhacenter($baseUrl, $apiKey, $phone, $message, $config),
            'ruangwa'    => $this->sendRuangWa($baseUrl, $apiKey, $phone, $message, $config),
            'wa_cloud'   => $this->sendCloudApi($baseUrl, $apiKey, $phone, $message, $config),
            'rest_generic' => $this->sendGeneric($baseUrl, $apiKey, $phone, $message, $config),
            default      => $this->sendGeneric($baseUrl, $apiKey, $phone, $message, $config),
        };
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    private function sendChatGo(string $baseUrl, string $apiKey, string $phone, string $message, array $config): array
    {
        $sender = $config['sender'] ?? '';
        $url = $baseUrl ?: 'https://chatgo.whitelabel.co.id';

        $payload = [
            'api_key' => $apiKey,
            'sender'  => $sender,
            'number'  => $phone,
            'message' => $message,
        ];

        $response = Http::asForm()->post("{$url}/api/v1/send-message", $payload);

        $data = $response->json() ?? [];

        if ($response->successful() && ($data['status'] ?? false)) {
            return ['success' => true, 'data' => $data, 'status' => $response->status()];
        }

        Log::warning('ChatGo send failed', [
            'provider' => $this->provider->name,
            'status'   => $response->status(),
            'body'     => $data,
        ]);

        return ['success' => false, 'error' => $data['message'] ?? "HTTP {$response->status()}", 'data' => $data];
    }

    private function sendWablas(string $baseUrl, string $apiKey, string $phone, string $message): array
    {
        $payload = [
            'data' => [[
                'phone'   => $phone,
                'message' => $message,
            ]],
        ];

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Content-Type'  => 'application/json',
        ])->post("{$baseUrl}/api/v1/send-message", $payload);

        return $this->parseResponse($response);
    }

    private function sendFonnte(string $baseUrl, string $apiKey, string $phone, string $message, ?string $senderId): array
    {
        $payload = [
            'target'  => $phone,
            'message' => $message,
        ];

        if ($senderId) {
            $payload['countryCode'] = '62';
        }

        $response = Http::withToken($apiKey)
            ->asForm()
            ->post("{$baseUrl}/send", $payload);

        return $this->parseResponse($response);
    }

    private function sendWhacenter(string $baseUrl, string $apiKey, string $phone, string $message, array $config): array
    {
        $deviceId = $config['device_id'] ?? '';

        $payload = [
            'device_id' => $deviceId,
            'number'    => $phone,
            'message'   => $message,
        ];

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->asForm()->post("{$baseUrl}/api/v2/send", $payload);

        return $this->parseResponse($response);
    }

    private function sendRuangWa(string $baseUrl, string $apiKey, string $phone, string $message, array $config): array
    {
        $sender = $config['sender'] ?? '';

        $payload = [
            'token'   => $apiKey,
            'number'  => $phone,
            'message' => $message,
        ];

        if ($sender) {
            $payload['sender'] = $sender;
        }

        $response = Http::asForm()->post("{$baseUrl}/api/send-message.php", $payload);

        return $this->parseResponse($response);
    }

    private function sendCloudApi(string $baseUrl, string $apiKey, string $phone, string $message, array $config): array
    {
        $phoneNumberId = $config['phone_number_id'] ?? '';
        $url = $baseUrl ?: 'https://graph.facebook.com/v18.0';

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ];

        $response = Http::withToken($apiKey)
            ->post("{$url}/{$phoneNumberId}/messages", $payload);

        return $this->parseResponse($response);
    }

    private function sendGeneric(string $baseUrl, string $apiKey, string $phone, string $message, array $config): array
    {
        $method = strtolower($config['method'] ?? 'post');
        $phoneKey = $config['phone_key'] ?? 'phone';
        $messageKey = $config['message_key'] ?? 'message';
        $tokenType = $config['token_type'] ?? 'Bearer';
        $tokenKey = $config['token_key'] ?? 'Authorization';
        $extraBody = $config['extra_body'] ?? [];

        $body = array_merge($extraBody, [
            $phoneKey   => $phone,
            $messageKey => $message,
        ]);

        $headers = [$tokenKey => "{$tokenType} {$apiKey}"];

        if ($config['content_type'] ?? null) {
            $headers['Content-Type'] = $config['content_type'];
        }

        if (isset($config['extra_headers']) && is_array($config['extra_headers'])) {
            $headers = array_merge($headers, $config['extra_headers']);
        }

        $http = Http::withHeaders($headers);

        if (($config['content_type'] ?? '') === 'application/json') {
            $response = $http->{$method}($baseUrl, $body);
        } else {
            $response = $http->asForm()->{$method}($baseUrl, $body);
        }

        return $this->parseResponse($response);
    }

    private function parseResponse($response): array
    {
        $statusCode = $response->status();
        $body = $response->json() ?? $response->body();

        if ($statusCode >= 200 && $statusCode < 300) {
            return ['success' => true, 'data' => $body, 'status' => $statusCode];
        }

        Log::warning('WhatsApp send failed', [
            'provider' => $this->provider->name,
            'status'   => $statusCode,
            'body'     => $body,
        ]);

        return ['success' => false, 'error' => "HTTP {$statusCode}", 'data' => $body];
    }
}
