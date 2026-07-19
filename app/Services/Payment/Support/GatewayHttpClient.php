<?php

namespace App\Services\Payment\Support;

use App\Models\Payment\PaymentProvider;
use App\Services\Payment\Exceptions\GatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GatewayHttpClient
{
    public function __construct(protected PaymentProvider $provider) {}

    public function request(string $method, string $endpoint, array $payload = []): array
    {
        $url      = rtrim($this->provider->base_url ?? '', '/') . '/' . ltrim($endpoint, '/');
        $request  = $this->buildRequest();
        $response = strtoupper($method) === 'GET'
            ? $request->get($url, $payload)
            : $request->{strtolower($method)}($url, $payload);

        if (!$response->successful()) {
            throw new GatewayException(
                "Gateway request failed: {$response->status()}",
                ['status' => $response->status(), 'body' => $response->body()]
            );
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new GatewayException('Gateway returned non-JSON', ['body' => $response->body()]);
        }

        return $body;
    }

    protected function buildRequest(): PendingRequest
    {
        $request = Http::timeout(20);

        $extraHeaders = $this->provider->extra_headers ?? [];
        if ($extraHeaders) {
            $request = $request->withHeaders($extraHeaders);
        }

        $authConfig = (array) ($this->provider->extra_config['auth'] ?? []);
        $authType   = $authConfig['type'] ?? null;
        $apiKey     = $this->provider->api_key;
        $secretKey  = $this->provider->secret_key;

        if ($authType === 'basic_username_only' && $apiKey) {
            $request = $request->withBasicAuth($apiKey, '');
        } elseif ($authType === 'basic_password_only' && $apiKey) {
            $request = $request->withBasicAuth('', $apiKey);
        } elseif ($authType === 'basic' && $apiKey) {
            $request = $request->withBasicAuth($apiKey, $secretKey ?? '');
        } elseif ($authType === 'bearer' && $apiKey) {
            $request = $request->withToken($apiKey);
        } elseif ($authType === 'header' && $apiKey) {
            $headerName = $authConfig['header'] ?? 'X-API-Key';
            $request    = $request->withHeaders([$headerName => $apiKey]);
        }

        return $request->acceptJson();
    }
}
