<?php

namespace App\Observers;

use App\Models\Academic\Student;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Support\Facades\Cache;

class StudentObserver
{
    public function __construct(private WebhookDispatcher $webhooks) {}

    public function created(Student $student): void
    {
        Cache::forget("school_stats_{$student->school_id}");
        activity('student')
            ->causedBy(auth()->user())
            ->performedOn($student)
            ->withProperties(['school_id' => $student->school_id])
            ->log('Student enrolled: ' . optional($student->user)->name);

        $this->webhooks->fire($student->school_id, 'student.created', $student->toArray(), 'student-' . $student->id);
    }

    public function updated(Student $student): void
    {
        $this->webhooks->fire($student->school_id, 'student.updated', $student->toArray(), 'student-upd-' . $student->id);
    }

    public function deleted(Student $student): void
    {
        Cache::forget("school_stats_{$student->school_id}");
        $this->webhooks->fire($student->school_id, 'student.deleted', ['id' => $student->id], 'student-del-' . $student->id);
    }
}
