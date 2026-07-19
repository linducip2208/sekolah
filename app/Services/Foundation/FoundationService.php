<?php

namespace App\Services\Foundation;

use App\Models\Academic\Student;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationAdmin;

class FoundationService
{
    public function aggregateMetrics(Foundation $foundation): array
    {
        $schoolIds = $foundation->schools()->pluck('schools.id');

        return [
            'foundation_id'    => $foundation->id,
            'name'             => $foundation->name,
            'school_count'     => $schoolIds->count(),
            'student_total'    => Student::whereIn('school_id', $schoolIds)->count(),
            'teacher_total'    => \App\Models\User::whereIn('school_id', $schoolIds)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                ->count(),
            'revenue_ytd'      => \App\Models\Finance\FeePayment::whereIn(
                'fee_invoice_id',
                \App\Models\Finance\FeeInvoice::whereIn('school_id', $schoolIds)->pluck('id'),
            )->whereYear('payment_date', now()->year)->sum('amount'),
        ];
    }

    public function isAdmin(int $userId, int $foundationId): bool
    {
        return FoundationAdmin::where('user_id', $userId)
            ->where('foundation_id', $foundationId)
            ->exists();
    }
}
