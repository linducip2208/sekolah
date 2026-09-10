<?php

namespace App\Services\Integrations\Dapodik;

use App\Models\Dapodik\DapodikConnection;

class FakeDapodikClient implements DapodikClientInterface
{
    public function __construct(private array $fixtures = []) {}

    public function testConnection(DapodikConnection $connection): array
    {
        return ['ok' => true, 'status' => 200, 'message' => 'Fake Dapodik connection ready.'];
    }

    public function fetch(DapodikConnection $connection, string $entityType): array
    {
        return array_values(array_filter($this->fixtures[$entityType] ?? [], 'is_array'));
    }
}
