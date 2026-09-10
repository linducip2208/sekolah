<?php

namespace App\Jobs;

use App\Models\Visitor\VisitorVisit;
use App\Services\Notification\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyCanonicalVisitorHostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $visitorVisitId) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $visit = VisitorVisit::withoutGlobalScopes()
            ->whereKey($this->visitorVisitId)
            ->with('visitor')
            ->first();

        if (! $visit || ! $visit->host_user_id || ! $visit->visitor) {
            return;
        }

        $dispatcher->dispatch(
            (int) $visit->school_id,
            [(int) $visit->host_user_id],
            'visitor_arrived',
            'Tamu menunggu di lobi',
            "{$visit->visitor->name} tiba untuk: {$visit->purpose}",
            [
                'type' => 'visitor_arrived',
                'visitor_visit_id' => $visit->id,
                'qr_token' => $visit->qr_token,
            ],
        );
    }
}
