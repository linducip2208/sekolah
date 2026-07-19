<?php

namespace App\Jobs;

use App\Models\Medical\ClinicVisit;
use App\Services\Notification\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyParentClinicVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $clinicVisitId) {}

    public function handle(FcmService $fcm): void
    {
        $visit = ClinicVisit::with('student.user')->find($this->clinicVisitId);
        if (!$visit) return;

        $student = $visit->student;
        if (!$student) return;

        $parents = \DB::table('parent_student')
            ->where('student_id', $student->id)
            ->pluck('parent_id');

        $title = $visit->sent_home
            ? '🏥 Anak Anda perlu dijemput'
            : '🏥 Kunjungan klinik';

        $body = $visit->sent_home
            ? "{$student->user->name} dipulangkan dari sekolah karena: " . ($visit->diagnosis ?? $visit->symptoms)
            : "{$student->user->name} kunjungi klinik. Diagnosis: " . ($visit->diagnosis ?? $visit->symptoms);

        $fcm->sendToUsers($parents->toArray(), $title, $body, [
            'type'    => 'clinic_visit',
            'visit_id'=> $visit->id,
        ]);
    }
}
