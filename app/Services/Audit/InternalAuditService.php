<?php

namespace App\Services\Audit;

use App\Models\Audit\AuditFinding;
use App\Models\Audit\InternalAudit;

class InternalAuditService
{
    public function start(InternalAudit $audit): InternalAudit
    {
        $audit->update([
            'status'     => 'in_progress',
            'started_at' => $audit->started_at ?? now()->toDateString(),
        ]);

        return $audit->fresh();
    }

    public function complete(InternalAudit $audit): InternalAudit
    {
        $open = $audit->findings()->where('status', '!=', 'resolved')->exists();
        abort_if($open, 422, 'Masih ada temuan yang belum diselesaikan.');

        $audit->update([
            'status'       => 'completed',
            'completed_at' => now()->toDateString(),
        ]);

        return $audit->fresh();
    }

    public function resolve(AuditFinding $finding): AuditFinding
    {
        $finding->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        return $finding->fresh();
    }

    public function summary(int $schoolId): array
    {
        $findings = AuditFinding::where('school_id', $schoolId)->get();

        return [
            'open'     => $findings->where('status', 'open')->count(),
            'progress' => $findings->where('status', 'in_progress')->count(),
            'resolved' => $findings->where('status', 'resolved')->count(),
            'high'     => $findings->where('severity', 'high')->where('status', '!=', 'resolved')->count(),
        ];
    }
}
