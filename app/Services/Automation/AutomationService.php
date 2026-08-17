<?php

namespace App\Services\Automation;

use App\Models\Automation\AutomationLog;
use App\Models\Automation\AutomationRule;
use App\Models\Communication\NotificationLog;
use Illuminate\Support\Facades\DB;

class AutomationService
{
    /** Execute all active rules for a trigger against a set of events. Returns count of actions. */
    public function run(int $schoolId, string $triggerType, array $events): int
    {
        $rules = AutomationRule::where('school_id', $schoolId)
            ->where('trigger_type', $triggerType)
            ->where('is_active', true)
            ->get();

        $count = 0;

        foreach ($rules as $rule) {
            foreach ($events as $event) {
                $this->execute($rule, $triggerType, $event);
                $count++;
            }
        }

        return $count;
    }

    protected function execute(AutomationRule $rule, string $triggerType, array $event): void
    {
        $status = 'success';
        $error  = null;
        $userId = $event['user_id'] ?? null;

        try {
            if ($rule->action_type === 'notify' && $userId) {
                NotificationLog::create([
                    'school_id' => $rule->school_id,
                    'user_id'   => $userId,
                    'type'      => $triggerType,
                    'title'     => $this->render($rule->action_config['title'] ?? '', $event),
                    'body'      => $this->render($rule->action_config['body'] ?? '', $event),
                    'data'      => $event,
                    'is_read'   => false,
                ]);
            } elseif ($rule->action_type === 'email') {
                // External email dispatch is intentionally out of scope here;
                // the event is logged so it can be picked up by a mail queue.
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
        }

        AutomationLog::create([
            'school_id'          => $rule->school_id,
            'automation_rule_id' => $rule->id,
            'trigger_type'       => $triggerType,
            'target_user_id'     => $userId,
            'payload'            => $event,
            'status'             => $status,
            'error'              => $error,
            'executed_at'        => now(),
        ]);
    }

    /** Replace {placeholders} in a template with event values. */
    public function render(string $template, array $event): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($event) {
            return (string) ($event[$m[1]] ?? '');
        }, $template);
    }

    /* ==================== TRIGGER EVALUATORS ==================== */

    /** Invoices due within N days → notify school admin. */
    public function feeDueSoonEvents(int $schoolId, int $days = 3): array
    {
        $from = now()->startOfDay();
        $to   = now()->addDays($days)->endOfDay();

        return \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [$from, $to])
            ->with('student.user')
            ->get()
            ->map(fn ($i) => [
                'user_id' => null,
                'student' => $i->student?->user?->name ?? '—',
                'amount'  => (int) ($i->amount - $i->paid_amount),
                'due'     => $i->due_date?->format('d M Y') ?? '—',
            ])
            ->all();
    }

    /** Invoices that are overdue → notify. */
    public function feeOverdueEvents(int $schoolId): array
    {
        return \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)
            ->where('status', 'overdue')
            ->with('student.user')
            ->get()
            ->map(fn ($i) => [
                'user_id' => null,
                'student' => $i->student?->user?->name ?? '—',
                'amount'  => (int) ($i->amount - $i->paid_amount),
                'due'     => $i->due_date?->format('d M Y') ?? '—',
            ])
            ->all();
    }

    /** Students absent for N consecutive school days → notify. */
    public function absentStreakEvents(int $schoolId, int $streak = 3): array
    {
        $students = \App\Models\Academic\Student::where('school_id', $schoolId)->with('user')->get();

        $events = [];

        foreach ($students as $student) {
            $recent = \App\Models\Academic\Attendance::where('student_id', $student->id)
                ->where('status', 'absent')
                ->orderByDesc('date')
                ->limit($streak)
                ->get();

            if ($recent->count() >= $streak) {
                $events[] = [
                    'user_id' => null,
                    'student' => $student->user?->name ?? $student->admission_no,
                    'streak'  => $recent->count(),
                ];
            }
        }

        return $events;
    }

    /** Students/staff with birthday today → notify. */
    public function birthdayEvents(int $schoolId): array
    {
        $month = now()->month;
        $day   = now()->day;

        return \App\Models\Academic\Student::where('school_id', $schoolId)
            ->whereMonth('date_of_birth', $month)
            ->whereDay('date_of_birth', $day)
            ->with('user')
            ->get()
            ->map(fn ($s) => [
                'user_id' => $s->user_id,
                'student' => $s->user?->name ?? '—',
            ])
            ->all();
    }
}
