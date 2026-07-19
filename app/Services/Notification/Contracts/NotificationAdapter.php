<?php

namespace App\Services\Notification\Contracts;

use App\Models\Communication\NotificationProvider;

interface NotificationAdapter
{
    public function send(NotificationProvider $provider, array $recipients, string $title, string $body, array $data = []): array;
}
