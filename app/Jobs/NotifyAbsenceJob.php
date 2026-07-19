<?php

namespace App\Jobs;

use App\Models\Academic\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAbsenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $studentIds,
        private string $date,
        private int $schoolId
    ) {}

    public function handle(): void
    {
        Student::whereIn('id', $this->studentIds)
            ->with('parents', 'user')
            ->chunkById(50, function ($students) {
                foreach ($students as $student) {
                    foreach ($student->parents as $parent) {
                        if ($parent->fcm_token) {
                            // FCM notification would be sent here via NotificationService
                        }
                    }
                }
            });
    }
}
