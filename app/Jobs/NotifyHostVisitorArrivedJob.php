<?php

namespace App\Jobs;

use App\Models\Visitor\VisitorLog;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyHostVisitorArrivedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $visitorLogId) {}

    public function handle(FcmService $fcm): void
    {
        $log = VisitorLog::find($this->visitorLogId);
        if (!$log || !$log->host_user_id) return;

        $host = \App\Models\User::find($log->host_user_id);
        if (!$host) return;

        $fcm->sendToUser($host,
            '👋 Tamu menunggu di lobi',
            "{$log->visitor_name} tiba untuk: {$log->purpose}",
            ['type' => 'visitor_arrived', 'visitor_id' => $log->id],
        );
    }
}
