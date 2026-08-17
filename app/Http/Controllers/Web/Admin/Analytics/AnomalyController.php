<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Analytics\AnomalyAlert;
use App\Services\Analytics\AnomalyDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnomalyController extends Controller
{
    public function __construct(private AnomalyDetectionService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $alerts = AnomalyAlert::where('school_id', $schoolId)
            ->when($request->has('resolved'), fn ($q) => $q->whereNotNull('resolved_at'))
            ->when($request->has('unresolved'), fn ($q) => $q->whereNull('resolved_at'))
            ->orderByDesc('detected_at')
            ->paginate(30)
            ->withQueryString();

        $unresolvedCount = AnomalyAlert::where('school_id', $schoolId)->whereNull('resolved_at')->count();

        return view('school-admin.analytics.anomalies', compact('alerts', 'unresolvedCount'));
    }

    public function run(): RedirectResponse
    {
        $count = $this->service->run($this->schoolId());

        return back()->with('success', "Deteksi anomali dijalankan: {$count} alert baru.");
    }

    public function resolve(AnomalyAlert $alert): RedirectResponse
    {
        abort_unless($alert->school_id === $this->schoolId(), 403);

        $this->service->resolve($alert, auth()->id());

        return back()->with('success', 'Anomali ditandai selesai.');
    }
}
