<?php

namespace App\Services\Academic;

use App\Models\Academic\ReportCard;

class GradeApprovalService
{
    public function submit(ReportCard $card): ReportCard
    {
        abort_unless(in_array($card->status, ['draft'], true), 422, 'Hanya rapor draft yang bisa diajukan.');

        $card->update(['status' => 'submitted']);

        return $card->fresh();
    }

    public function approve(ReportCard $card, int $userId): ReportCard
    {
        abort_unless(in_array($card->status, ['submitted'], true), 422, 'Hanya rapor yang diajukan yang bisa disetujui.');

        $card->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'is_published'=> true,
        ]);

        return $card->fresh();
    }

    public function lock(ReportCard $card): ReportCard
    {
        abort_unless($card->status === 'approved', 422, 'Hanya rapor yang disetujui yang bisa dikunci.');

        $card->update(['status' => 'locked', 'locked_at' => now()]);

        return $card->fresh();
    }

    public function reject(ReportCard $card): ReportCard
    {
        abort_unless($card->status === 'submitted', 422, 'Hanya rapor yang diajukan yang bisa ditolak.');

        $card->update(['status' => 'draft']);

        return $card->fresh();
    }
}
