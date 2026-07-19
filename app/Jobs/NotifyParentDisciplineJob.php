<?php

namespace App\Jobs;

use App\Models\Discipline\DisciplineRecord;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyParentDisciplineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $recordId) {}

    public function handle(FcmService $fcm): void
    {
        $record = DisciplineRecord::with('category')->find($this->recordId);
        if (!$record) return;

        $student = \App\Models\Academic\Student::find($record->student_id);
        if (!$student) return;

        $parents = \DB::table('parent_student')
            ->where('student_id', $student->id)
            ->pluck('parent_id');

        $type   = $record->category?->type === 'achievement' ? 'Prestasi' : 'Pelanggaran';
        $emoji  = $record->category?->type === 'achievement' ? '🏆' : '⚠️';
        $title  = "{$emoji} {$type} dilaporkan";
        $body   = "Anak Anda mendapat {$record->points} poin: {$record->category?->name}";

        $fcm->sendToUsers($parents->toArray(), $title, $body, [
            'type'      => 'discipline',
            'record_id' => $record->id,
        ]);

        $record->update(['parent_notified' => true]);
    }
}
