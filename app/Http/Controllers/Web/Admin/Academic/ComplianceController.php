<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AdiwiyataEvidence;
use App\Models\Academic\AdiwiyataIndicator;
use App\Models\Academic\AdiwiyataLevel;
use App\Models\AccreditationActionPlan;
use App\Models\AccreditationScore;
use App\Models\AccreditationStandard;
use App\Services\Audit\InternalAuditService;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    public function __construct(private InternalAuditService $audits) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        // Accreditation predicted score
        $standards = AccreditationStandard::with('instruments')->orderBy('code')->get();
        $totalPredicted = 0;
        $totalMax = 0;

        foreach ($standards as $std) {
            $instIds = $std->instruments->pluck('id');
            $avg = AccreditationScore::where('school_id', $schoolId)
                ->whereIn('accreditation_instrument_id', $instIds)
                ->whereNotNull('self_score')
                ->avg('self_score') ?? 0;

            $totalPredicted += ($avg / max($std->max_score, 1)) * $std->weight_percent;
            $totalMax += $std->weight_percent;
        }

        $predictedScore = $totalMax > 0 ? round($totalPredicted, 1) : 0;
        $grade = $this->predictGrade($predictedScore);

        // Adiwiyata
        $level = AdiwiyataLevel::where('school_id', $schoolId)->latest()->first();
        $indicatorCount = AdiwiyataIndicator::count();
        $evidenceCount = AdiwiyataEvidence::where('school_id', $schoolId)->count();
        $verifiedCount = AdiwiyataEvidence::where('school_id', $schoolId)->where('status', 'verified')->count();

        // Internal audit
        $auditSummary = $this->audits->summary($schoolId);

        // Action plans
        $plans = AccreditationActionPlan::where('school_id', $schoolId)->get()->groupBy('status');

        return view('school-admin.compliance.dashboard', compact(
            'predictedScore', 'grade',
            'level', 'indicatorCount', 'evidenceCount', 'verifiedCount',
            'auditSummary', 'plans'
        ));
    }

    private function predictGrade(float $score): array
    {
        return match (true) {
            $score >= 91 => ['grade' => 'A', 'label' => 'Unggul', 'color' => '#16A34A'],
            $score >= 81 => ['grade' => 'A', 'label' => 'Baik Sekali', 'color' => '#22C55E'],
            $score >= 71 => ['grade' => 'B', 'label' => 'Baik', 'color' => '#2563EB'],
            $score >= 61 => ['grade' => 'C', 'label' => 'Cukup', 'color' => '#EAB308'],
            default      => ['grade' => 'TT', 'label' => 'Tidak Terakreditasi', 'color' => '#DC2626'],
        };
    }
}
