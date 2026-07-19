<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\Analytics\DropoutPredictionService;
use Illuminate\Console\Command;

class PredictDropoutRisk extends Command
{
    protected $signature   = 'analytics:predict-dropout {--school_id=* : Limit to specific schools} {--provider_id= : AI provider ID} {--model_id= : AI model ID} {--no-notify : Skip parent notifications}';
    protected $description = 'Run AI dropout risk prediction for all active students (weekly)';

    public function handle(DropoutPredictionService $service): int
    {
        $query = School::where('is_active', true);
        if ($this->option('school_id')) {
            $query->whereIn('id', $this->option('school_id'));
        }

        $providerId = $this->option('provider_id') ? (int) $this->option('provider_id') : null;
        $modelId    = $this->option('model_id') ? (int) $this->option('model_id') : null;
        $shouldNotify = !$this->option('no-notify');

        $total = 0;
        $totalNotified = 0;
        $query->chunk(20, function ($schools) use ($service, $providerId, $modelId, $shouldNotify, &$total, &$totalNotified) {
            foreach ($schools as $school) {
                try {
                    $count = $service->predictForSchool($school->id, $providerId, $modelId);
                    $total += $count;
                    $this->info("✓ {$school->name}: {$count} siswa diprediksi");

                    $critical = \App\Models\Analytics\AiDropoutPrediction::where('school_id', $school->id)
                        ->whereDate('prediction_date', today())
                        ->whereIn('risk_level', ['high', 'critical'])
                        ->count();

                    if ($critical > 0) {
                        $this->warn("  ⚠ {$critical} siswa dengan risiko tinggi/kritis!");
                    }

                    if ($shouldNotify) {
                        $notified = $this->notifyParents($school->id);
                        $totalNotified += $notified;
                        if ($notified > 0) {
                            $this->info("  📲 {$notified} notifikasi WhatsApp terkirim.");
                        }
                    }
                } catch (\Throwable $e) {
                    $this->error("✗ {$school->name}: " . $e->getMessage());
                }
            }
        });

        $this->info("Done. {$total} prediksi dropout selesai.");
        if ($shouldNotify) {
            $this->info("Total notifikasi terkirim: {$totalNotified}");
        }
        return self::SUCCESS;
    }

    protected function notifyParents(int $schoolId): int
    {
        $predictions = \App\Models\Analytics\AiDropoutPrediction::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->whereDate('prediction_date', today())
            ->whereIn('risk_level', ['high', 'critical'])
            ->where('notified_parents', false)
            ->with('student.user')
            ->get();

        if ($predictions->isEmpty()) {
            return 0;
        }

        $whatsapp = app(\App\Services\Notification\WhatsAppService::class);
        $notifiedCount = 0;

        foreach ($predictions as $prediction) {
            $student = $prediction->student;
            if (!$student) continue;

            $phone = $student->whatsapp_phone ?? $student->guardian_phone;
            if (!$phone) continue;

            $studentName = $student->user?->name ?? "Siswa #{$student->id}";
            $riskLabel = match ($prediction->risk_level) {
                'critical' => 'SANGAT TINGGI',
                'high'     => 'TINGGI',
                default    => $prediction->risk_level,
            };

            $message = "*Peringatan Risiko Putus Sekolah*\n\n"
                . "Yth. Orang Tua/Wali dari:\n"
                . "*{$studentName}* (NIS: {$student->admission_no})\n\n"
                . "Berdasarkan analisis sistem, terdeteksi *risiko putus sekolah dengan kategori {$riskLabel}*.\n\n"
                . "Faktor yang berkontribusi: kehadiran rendah, nilai akademik, atau catatan disiplin.\n\n"
                . "Mohon segera menghubungi wali kelas untuk konsultasi lebih lanjut.\n\n"
                . "— " . config('app.name', 'eSchool');

            try {
                $sent = $whatsapp->send($phone, $message);
                if ($sent) {
                    $prediction->update(['notified_parents' => true]);
                    $notifiedCount++;
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to notify dropout prediction for student {$student->id}", [
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notifiedCount;
    }
}
