<?php

namespace App\Services\Integrations\Dapodik;

use App\Models\Dapodik\DapodikConnection;
use Illuminate\Support\Facades\Http;

/**
 * Configurable REST adapter. Dapodik endpoints differ by deployment, so the
 * school supplies endpoint paths in field_mappings._endpoints; this adapter
 * never invents vendor URLs or credentials.
 */
class DapodikRestClient implements DapodikClientInterface
{
    public function testConnection(DapodikConnection $connection): array
    {
        abort_if(blank($connection->host), 422, 'Host Dapodik belum diatur.');
        $response = $this->request($connection, 'get', $connection->host);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->successful() ? 'Koneksi berhasil.' : 'Koneksi ditolak oleh endpoint.',
        ];
    }

    public function fetch(DapodikConnection $connection, string $entityType): array
    {
        $path = data_get($connection->field_mappings, "_endpoints.{$entityType}");
        abort_if(blank($path), 422, "Endpoint untuk entitas {$entityType} belum dikonfigurasi.");

        $response = $this->request($connection, 'get', rtrim($connection->host, '/').'/'.ltrim($path, '/'));
        $response->throw();
        $payload = $response->json();
        $records = data_get($payload, 'data', $payload);

        abort_unless(is_array($records), 422, 'Respons Dapodik bukan daftar data yang valid.');

        return array_values(array_filter($records, 'is_array'));
    }

    private function request(DapodikConnection $connection, string $method, string $url)
    {
        $request = Http::timeout(max(1, min((int) $connection->timeout, 300)))
            ->withOptions(['verify' => (bool) $connection->verify_ssl])
            ->acceptJson();

        if ($token = $connection->secret('token')) {
            $request = $request->withToken($token);
        } elseif ($username = $connection->secret('username')) {
            $request = $request->withBasicAuth($username, (string) $connection->secret('password'));
        }

        return $request->{$method}($url);
    }
}
