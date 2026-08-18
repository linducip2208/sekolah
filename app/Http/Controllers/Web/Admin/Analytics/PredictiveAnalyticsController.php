<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Academic\ClassSection;
use App\Services\Analytics\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PredictiveAnalyticsController extends Controller
{
    public function __construct(
        protected PredictiveAnalyticsService $service,
    ) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $results = $this->service->predictDropoutRisk($schoolId);
        $distribution = $this->service->getRiskDistribution($schoolId);

        $classSectionId = $request->class_section_id;
        if ($classSectionId) {
            $results = $results->filter(fn ($r) => Student::where('id', $r['student_id'])->where('class_section_id', $classSectionId)->exists());
        }

        $highRisk = $results->where('risk_level', '!=', 'low')->take(50);

        $classSections = ClassSection::where('school_id', $schoolId)
            ->with('classRoom:id,name')
            ->orderBy('name')
            ->get();

        return view('school-admin.analytics.predictive-analytics', [
            'results'        => $results,
            'distribution'   => $distribution,
            'highRisk'       => $highRisk,
            'classSections'  => $classSections,
            'classSectionId' => $classSectionId,
        ]);
    }

    public function studentDetail(Student $student): View
    {
        $schoolId = $this->schoolId();
        $factors = $this->service->calculateRiskFactors($schoolId, $student);
        $riskScore = $this->service->computeWeightedScorePublic($factors);

        return view('school-admin.analytics.predictive-student-detail', [
            'student'   => $student->load('user:id,name', 'classSection.classRoom'),
            'factors'   => $factors,
            'riskScore' => round($riskScore, 1),
            'riskLevel' => match (true) {
                $riskScore >= 70 => 'critical',
                $riskScore >= 50 => 'high',
                $riskScore >= 30 => 'medium',
                default          => 'low',
            },
            'weights'   => [
                'attendance' => 30,
                'academic'   => 25,
                'discipline' => 20,
                'engagement' => 15,
                'financial'  => 10,
            ],
        ]);
    }
}
