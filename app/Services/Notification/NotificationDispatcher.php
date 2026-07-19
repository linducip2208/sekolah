<?php

namespace App\Services\Notification;

use App\Models\Communication\DeviceToken;
use App\Models\Communication\NotificationLog;
use App\Models\Communication\NotificationProvider;
use App\Models\User;
use App\Services\Notification\Adapters\FcmLegacyAdapter;
use App\Services\Notification\Adapters\RestGenericAdapter;
use App\Services\Notification\Contracts\NotificationAdapter;

class NotificationDispatcher
{
    public function dispatch(int $schoolId, array $userIds, string $type, string $title, string $body, array $data = []): array
    {
        $now = now();
        $rows = [];
        foreach ($userIds as $uid) {
            $rows[] = [
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
        if ($rows) {
            NotificationLog::withoutGlobalScopes()->insert($rows);
        }

        $result = ['push' => null, 'sms' => null, 'whatsapp' => null];

        $pushProvider = $this->getProvider($schoolId, 'push');
        if ($pushProvider) {
            $tokens = DeviceToken::withoutGlobalScopes()
                ->whereIn('user_id', $userIds)
                ->where('school_id', $schoolId)
                ->pluck('token')
                ->all();
            if (empty($tokens)) {
                $tokens = User::whereIn('id', $userIds)
                    ->whereNotNull('fcm_token')
                    ->pluck('fcm_token')
                    ->all();
            }
            if ($tokens) {
                $result['push'] = $this->adapterFor($pushProvider)->send($pushProvider, $tokens, $title, $body, $data);
            }
        }

        $phoneNumbers = User::whereIn('id', $userIds)->whereNotNull('phone')->pluck('phone')->all();

        $smsProvider = $this->getProvider($schoolId, 'sms');
        if ($smsProvider && $phoneNumbers) {
            $result['sms'] = $this->adapterFor($smsProvider)->send($smsProvider, $phoneNumbers, $title, $body, $data);
        }

        $waProvider = $this->getProvider($schoolId, 'whatsapp');
        if ($waProvider && $phoneNumbers) {
            $result['whatsapp'] = $this->adapterFor($waProvider)->send($waProvider, $phoneNumbers, $title, $body, $data);
        }

        return $result;
    }

    public function getProvider(int $schoolId, string $transport): ?NotificationProvider
    {
        return NotificationProvider::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('transport', $transport)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    public function adapterFor(NotificationProvider $provider): NotificationAdapter
    {
        return match ($provider->api_format) {
            'fcm_legacy'   => app(FcmLegacyAdapter::class),
            default        => app(RestGenericAdapter::class),
        };
    }

    public function test(NotificationProvider $provider, string $recipient, string $title, string $body): array
    {
        return $this->adapterFor($provider)->send($provider, [$recipient], $title, $body);
    }
}
