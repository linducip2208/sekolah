<?php

namespace App\Http\Controllers\Web\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiRecommendation;
use App\Services\AI\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        $recommendations = AiRecommendation::where('school_id', $schoolId)
            ->with('student.user')
            ->orderByDesc('risk_level')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('school-admin.ai.recommendations', [
            'recommendations' => $recommendations,
            'pendingCount'    => AiRecommendation::where('school_id', $schoolId)->where('status', 'pending')->count(),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $count = $this->service->generateFromRisk($this->schoolId());

        return back()->with('success', "Rekomendasi dibuat untuk {$count} siswa at-risk.");
    }

    public function action(AiRecommendation $recommendation): RedirectResponse
    {
        abort_unless($recommendation->school_id === $this->schoolId(), 403);
        $this->service->action($recommendation, auth()->id());
        return back()->with('success', 'Rekomendasi ditandai ditindaklanjuti.');
    }

    public function dismiss(AiRecommendation $recommendation): RedirectResponse
    {
        abort_unless($recommendation->school_id === $this->schoolId(), 403);
        $this->service->dismiss($recommendation, auth()->id());
        return back()->with('success', 'Rekomendasi dibuang.');
    }
}
