<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;

class AiModulAjarGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        string $subjectName,
        string $topic,
        string $gradeLevel,
        int $hours,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $model    = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter  = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($subjectName, $topic, $gradeLevel, $hours);

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.7, 'max_tokens' => 2048]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $cost = $this->estimateCost($model, $result['input_tokens'] ?? 0, $result['output_tokens'] ?? 0);
            AiUsageLog::create([
                'school_id'      => $schoolId,
                'user_id'        => $userId,
                'ai_model_id'    => $model->id,
                'feature_key'    => 'modul_ajar_generator',
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        $parsed = $this->parseResult($result['text'] ?? '');

        return [
            'parsed'             => $parsed,
            'raw_text'           => $result['text'] ?? '',
            'ai_provider_id'     => $provider->id,
            'ai_model_id'        => $model->id,
            'tokens_used'        => ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0),
            'processing_time_ms' => $latencyMs,
        ];
    }

    protected function buildPrompt(string $subject, string $topic, string $grade, int $hours): array
    {
        $system = <<<'PROMPT'
Anda adalah guru profesional Indonesia ahli menyusun Modul Ajar Kurikulum Merdeka.
Buatkan modul ajar terstruktur dalam Bahasa Indonesia.
Response HARUS dalam format JSON:
{
  "identitas": {"mata_pelajaran": "...", "fase": "...", "kelas": "...", "topik": "...", "alokasi_waktu": "..."},
  "kompetensi_awal": ["..."],
  "profil_pelajar_pancasila": ["..."],
  "tujuan_pembelajaran": ["..."],
  "pemahaman_bermakna": "...",
  "pertanyaan_pemantik": ["..."],
  "materi_pembelajaran": ["ringkasan materi..."],
  "kegiatan_pembelajaran": {
    "pendahuluan": "...",
    "inti": ["..."],
    "penutup": "..."
  },
  "asesmen": {
    "diagnostik": "...",
    "formulatif": "...",
    "sumatif": "..."
  },
  "media_sumber": ["..."],
  "refleksi": "..."
}
Pastikan JSON valid.
PROMPT;

        $user = "Buatkan Modul Ajar untuk:\n"
            . "- Mata Pelajaran: {$subject}\n"
            . "- Kelas/Fase: {$gradeLevel}\n"
            . "- Topik: {$topic}\n"
            . "- Alokasi Waktu: {$hours} jam pelajaran\n\n"
            . "Sesuai Kurikulum Merdeka, praktis dan implementatif.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    protected function parseResult(string $raw): array
    {
        $trimmed = trim($raw);
        if (preg_match('/\{[\s\S]*\}/', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return ['raw' => $raw];
    }

    protected function estimateCost(AiModel $model, int $in, int $out): float
    {
        return round(($in / 1000) * (float) $model->input_price_per_1k + ($out / 1000) * (float) $model->output_price_per_1k, 6);
    }

    protected function resolveModel(int $schoolId, ?int $modelId): AiModel
    {
        if ($modelId) {
            return AiModel::where('school_id', $schoolId)->where('id', $modelId)->where('is_active', true)->firstOrFail();
        }
        return AiModel::where('school_id', $schoolId)->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('priority')->firstOrFail();
    }
}
