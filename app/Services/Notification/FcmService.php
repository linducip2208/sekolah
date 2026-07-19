<?php

namespace App\Services\Notification;

use App\Models\Communication\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $serverKey;
    private string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key', '');
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$user->fcm_token) {
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->chunk(500);

        $sent = 0;
        foreach ($tokens as $chunk) {
            $sent += $this->sendToMultiple($chunk->values()->all(), $title, $body, $data);
        }
        return $sent;
    }

    public function sendToSchool(int $schoolId, string $title, string $body, array $roles = [], array $data = []): int
    {
        $query = User::where('school_id', $schoolId)->where('is_active', true)->whereNotNull('fcm_token');
        if (!empty($roles)) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $roles));
        }

        $tokens = $query->pluck('fcm_token')->chunk(500);
        $sent   = 0;
        foreach ($tokens as $chunk) {
            $sent += $this->sendToMultiple($chunk->values()->all(), $title, $body, $data);
        }
        return $sent;
    }

    private function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey)) {
            return false;
        }

        try {
            $response = Http::withToken($this->serverKey, 'key')
                ->post($this->endpoint, [
                    'to'           => $token,
                    'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                    'data'         => $data,
                    'priority'     => 'high',
                ]);

            return $response->successful() && ($response->json('success') ?? 0) > 0;
        } catch (\Throwable $e) {
            Log::warning('FCM send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendToMultiple(array $tokens, string $title, string $body, array $data = []): int
    {
        if (empty($tokens) || empty($this->serverKey)) {
            return 0;
        }

        try {
            $response = Http::withToken($this->serverKey, 'key')
                ->post($this->endpoint, [
                    'registration_ids' => $tokens,
                    'notification'     => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                    'data'             => $data,
                    'priority'         => 'high',
                ]);

            return $response->json('success', 0);
        } catch (\Throwable $e) {
            Log::warning('FCM multicast failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    public function logAndSend(int $schoolId, array $userIds, string $type, string $title, string $body, array $data = []): void
    {
        $now      = now();
        $inserts  = [];
        foreach ($userIds as $uid) {
            $inserts[] = [
                'school_id'  => $schoolId,
                'user_id'    => $uid,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'data'       => json_encode($data),
                'is_read'    => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($inserts)) {
            NotificationLog::insert($inserts);
        }

        $this->sendToUsers($userIds, $title, $body, $data);
    }
}
