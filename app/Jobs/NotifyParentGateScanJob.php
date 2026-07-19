<?php

namespace App\Jobs;

use App\Models\Gate\IdGateEvent;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyParentGateScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $gateEventId) {}

    public function handle(FcmService $fcm): void
    {
        $event = IdGateEvent::find($this->gateEventId);
        if (!$event || !$event->user_id) return;

        $student = \App\Models\Academic\Student::where('user_id', $event->user_id)->first();
        if (!$student) return;

        $parents = \DB::table('parent_student')
            ->where('student_id', $student->id)
            ->pluck('parent_id');

        $verb  = $event->direction === 'in' ? 'masuk sekolah' : 'keluar sekolah';
        $emoji = $event->direction === 'in' ? '🏫' : '🚪';

        $studentName = \App\Models\User::find($event->user_id)?->name ?? 'Anak';
        $time        = $event->scanned_at->format('H:i');

        $fcm->sendToUsers($parents->toArray(),
            "{$emoji} Anak Anda {$verb}",
            "{$studentName} {$verb} pada pukul {$time}.",
            ['type' => 'gate_scan', 'direction' => $event->direction],
        );
    }
}
