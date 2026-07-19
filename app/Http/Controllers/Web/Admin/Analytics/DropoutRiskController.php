<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\Analytics\AiDropoutPrediction;
use App\Models\Analytics\StudentRiskScore;
use App\Services\Analytics\DropoutPredictionService;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Notification\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DropoutRiskController extends Controller
{
    public function __construct(
        protected DropoutPredictionService $service,
        protected NotificationDispatcher $dispatcher,
    ) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $riskLevel = $request->risk_level;

        $predictions = AiDropoutPrediction::where('school_id', $schoolId)
            ->with(['student.user:id,name', 'student.classSection.classRoom', 'aiModel'])
            ->when($riskLevel, fn ($q) => $q->where('risk_level', $riskLevel))
            ->orderByDesc('prediction_date')
            ->orderByDesc('risk_score')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total'     => AiDropoutPrediction::where('school_id', $schoolId)->whereDate('prediction_date', today())->count(),
            'critical'  => AiDropoutPrediction::where('school_id', $schoolId)->whereDate('prediction_date', today())->where('risk_level', 'critical')->count(),
            'high'      => AiDropoutPrediction::where('school_id', $schoolId)->whereDate('prediction_date', today())->where('risk_level', 'high')->count(),
            'medium'    => AiDropoutPrediction::where('school_id', $schoolId)->whereDate('prediction_date', today())->where('risk_level', 'medium')->count(),
            'low'       => AiDropoutPrediction::where('school_id', $schoolId)->whereDate('prediction_date', today())->where('risk_level', 'low')->count(),
            'lastRun'   => AiDropoutPrediction::where('school_id', $schoolId)->max('prediction_date'),
        ];

        $riskScores = StudentRiskScore::where('school_id', $schoolId)
            ->whereDate('snapshot_date', today())
            ->with('student.user:id,name')
            ->orderByDesc('overall_risk')
            ->get();

        $providers = AiProvider::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('priority')->orderBy('name')
            ->get();

        $aiModels = AiModel::where('school_id', $schoolId)
            ->where('is_active', true)
            ->with('provider')
            ->orderBy('priority')
            ->get();

        return view('school-admin.analytics.dropout-risk', [
            'predictions' => $predictions,
            'summary'     => $summary,
            'riskScores'  => $riskScores,
            'riskLevel'   => $riskLevel,
            'providers'   => $providers,
            'aiModels'    => $aiModels,
        ]);
    }

    public function runPrediction(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();

        $providerId = $request->ai_provider_id ? (int) $request->ai_provider_id : null;
        $modelId    = $request->ai_model_id ? (int) $request->ai_model_id : null;

        try {
            $count = $this->service->predictForSchool($schoolId, $providerId, $modelId);
            return back()->with('success', "Prediksi dropout selesai. {$count} siswa diproses.");
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal menjalankan prediksi: ' . $e->getMessage());
        }
    }

    public function runSinglePrediction(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'ai_provider_id' => 'nullable|exists:ai_providers,id',
            'ai_model_id'    => 'nullable|exists:ai_models,id',
        ]);

        try {
            $this->service->predictForStudent(
                $schoolId,
                (int) $data['student_id'],
                $data['ai_provider_id'] ? (int) $data['ai_provider_id'] : null,
                $data['ai_model_id'] ? (int) $data['ai_model_id'] : null,
            );
            return back()->with('success', 'Prediksi dropout untuk siswa berhasil.');
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal prediksi: ' . $e->getMessage());
        }
    }

    public function notifyParents(Request $request): RedirectResponse
    {
        $schoolId = $this->schoolId();

        $data = $request->validate([
            'prediction_ids'   => 'required|array|min:1',
            'prediction_ids.*' => 'exists:ai_dropout_predictions,id',
        ]);

        $notified = 0;
        $predictions = AiDropoutPrediction::where('school_id', $schoolId)
            ->whereIn('id', $data['prediction_ids'])
            ->with('student.parents')
            ->get();

        foreach ($predictions as $prediction) {
            $student = $prediction->student;
            if (!$student) continue;

            $message = $this->buildNotificationMessage($prediction);
            $title = "Peringatan Risiko Akademik";

            $parentUserIds = $student->parents->pluck('id')->all();
            if (!empty($parentUserIds)) {
                try {
                    $this->dispatcher->dispatch($schoolId, $parentUserIds, 'whatsapp', $title, $message);
                } catch (\Throwable $e) {
                    \Log::error('Failed to notify parents for prediction ' . $prediction->id, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            $guardianPhone = $student->guardian_phone ?? $student->whatsapp_phone;
            if ($guardianPhone && empty($parentUserIds)) {
                try {
                    app(WhatsAppService::class)->send($guardianPhone, $message);
                } catch (\Throwable $e) {
                    \Log::error('Failed to notify guardian for prediction ' . $prediction->id, [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            $prediction->update(['notified_parents' => true]);
            $notified++;
        }

        return back()->with('success', "{$notified} orang tua/wali berhasil dinotifikasi.");
    }

    protected function buildNotificationMessage(AiDropoutPrediction $prediction): string
    {
        $studentName = $prediction->student?->user?->name ?? 'Ananda';
        $level = match ($prediction->risk_level) {
            'critical' => 'SANGAT TINGGI',
            'high'     => 'TINGGI',
            'medium'   => 'SEDANG',
            default    => 'RENDAH',
        };

        return "Yth. Orang Tua/Wali {$studentName},\n\n"
            . "Sistem kami mendeteksi risiko akademik {$level} untuk {$studentName}.\n"
            . "Skor risiko: {$prediction->risk_score}/100\n\n"
            . "Analisis:\n{$prediction->ai_analysis}\n\n"
            . "Rekomendasi:\n{$prediction->recommended_actions}\n\n"
            . "Silakan hubungi wali kelas untuk diskusi lebih lanjut.\n"
            . "Terima kasih.\n\n"
            . "— " . config('app.name');
    }
}
