<?php

namespace App\Services\Integrations\Dapodik;

use App\Models\Dapodik\DapodikConnection;

interface DapodikClientInterface
{
    public function testConnection(DapodikConnection $connection): array;

    /** @return array<int, array<string, mixed>> */
    public function fetch(DapodikConnection $connection, string $entityType): array;
}
