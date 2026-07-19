<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\LessonPlan\LessonPlan;

class LessonPlanGeneratorService
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        string $subjectName,
        string $classLevel,
        string $topic,
        int $meetingNumber,
        string $curriculumType,
        ?int $providerId,
        ?int $modelId,
    ): array {
        $model    = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter  = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($subjectName, $classLevel, $topic, $meetingNumber, $curriculumType);

        $start  = microtime(true);
        $result = null;
        $error  = null;

        try {
            $result = $adapter->chat($messages, [
                'temperature' => 0.7,
                'max_tokens'  => 2048,
            ]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $tokens    = ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0);
            $cost      = $this->estimateCost($model, $result['input_tokens'] ?? 0, $result['output_tokens'] ?? 0);

            AiUsageLog::create([
                'school_id'      => $schoolId,
                'user_id'        => $userId,
                'ai_model_id'    => $model->id,
                'feature_key'    => 'lesson_plan_generator',
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        $parsed = $this->parseRpp($result['text'] ?? '');

        return [
            'parsed'            => $parsed,
            'raw_text'          => $result['text'] ?? '',
            'ai_provider_id'    => $provider->id,
            'ai_model_id'       => $model->id,
            'tokens_used'       => $tokens,
            'processing_time_ms'=> $latencyMs,
        ];
    }

    public function generateAndSave(
        int $schoolId,
        int $userId,
        array $lessonPlanData,
        ?int $providerId,
        ?int $modelId,
    ): LessonPlan {
        $subjectName   = $lessonPlanData['subject_name'] ?? 'Umum';
        $classLevel    = $lessonPlanData['class_level'] ?? 'X';
        $topic         = $lessonPlanData['title'] ?? 'Pembelajaran';
        $meetingNumber = (int) ($lessonPlanData['meeting_number'] ?? 1);
        $curriculumType= $lessonPlanData['curriculum_type'] ?? 'Merdeka';

        $result = $this->generate(
            $schoolId, $userId,
            $subjectName, $classLevel, $topic, $meetingNumber, $curriculumType,
            $providerId, $modelId,
        );

        $parsed = $result['parsed'];

        $lessonPlan = new LessonPlan();
        $lessonPlan->fill([
            'school_id'            => $schoolId,
            'class_section_id'     => $lessonPlanData['class_section_id'] ?? null,
            'subject_id'           => $lessonPlanData['subject_id'] ?? null,
            'teacher_id'           => $lessonPlanData['teacher_id'] ?? null,
            'title'                => $lessonPlanData['title'] ?? "RPP {$topic}",
            'lesson_date'          => $lessonPlanData['lesson_date'] ?? null,
            'duration_minutes'     => $lessonPlanData['duration_minutes'] ?? 90,
            'learning_objectives'  => $parsed['tujuan_pembelajaran'] ?? [],
            'material_summary'     => $parsed['materi'] ?? $parsed['kegiatan_pendahuluan'] ?? $result['raw_text'],
            'activities'           => array_filter([
                'pendahuluan' => $parsed['kegiatan_pendahuluan'] ?? null,
                'inti'        => $parsed['kegiatan_inti'] ?? null,
                'penutup'     => $parsed['penutup'] ?? null,
            ]),
            'assessment_methods'   => $parsed['asesmen'] ?? [],
            'curriculum_type'      => $curriculumType,
            'status'               => 'draft',
            'ai_provider_id'       => $result['ai_provider_id'],
            'ai_model_id'          => $result['ai_model_id'],
            'ai_generated'         => true,
            'ai_prompt_used'       => $this->buildPromptText($subjectName, $classLevel, $topic, $meetingNumber, $curriculumType),
            'ai_tokens_used'       => $result['tokens_used'],
        ]);
        $lessonPlan->save();

        return $lessonPlan;
    }

    protected function buildPrompt(
        string $subjectName,
        string $classLevel,
        string $topic,
        int $meetingNumber,
        string $curriculumType,
    ): array {
        $systemPrompt = <<<PROMPT
Anda adalah guru profesional di Indonesia yang ahli dalam menyusun Rencana Pelaksanaan Pembelajaran (RPP).
Buatkan RPP 1 lembar dalam Bahasa Indonesia dengan format terstruktur.
Response HARUS dalam format JSON dengan struktur berikut:
{
  "identitas": {"mata_pelajaran": "...", "kelas": "...", "topik": "...", "pertemuan_ke": 1, "alokasi_waktu": "..."},
  "tujuan_pembelajaran": ["tujuan 1", "tujuan 2", "tujuan 3"],
  "kegiatan_pendahuluan": "Deskripsi kegiatan pendahuluan (5-10 menit)...",
  "kegiatan_inti": "Deskripsi kegiatan inti (60-70 menit) dengan pendekatan saintifik/student-centered...",
  "penutup": "Deskripsi kegiatan penutup (5-10 menit)...",
  "asesmen": ["jenis asesmen 1", "jenis asesmen 2"],
  "materi": "Ringkasan materi pembelajaran...",
  "media_sumber": "Media dan sumber belajar yang digunakan..."
}
Pastikan respons HANYA JSON yang valid, tidak ada teks lain di luar JSON.
PROMPT;

        $userMessage = "Buatkan RPP format {$curriculumType} untuk:\n"
            . "- Mata Pelajaran: {$subjectName}\n"
            . "- Kelas: {$classLevel}\n"
            . "- Topik: {$topic}\n"
            . "- Pertemuan ke: {$meetingNumber}\n"
            . "- Alokasi Waktu: 2 x 45 menit\n\n"
            . "Buat yang praktis, sesuai kurikulum {$curriculumType}, dengan kegiatan yang engage siswa secara aktif.";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];
    }

    protected function buildPromptText(
        string $subjectName,
        string $classLevel,
        string $topic,
        int $meetingNumber,
        string $curriculumType,
    ): string {
        return "Buatkan RPP format {$curriculumType} untuk:\n"
            . "- Mata Pelajaran: {$subjectName}\n"
            . "- Kelas: {$classLevel}\n"
            . "- Topik: {$topic}\n"
            . "- Pertemuan ke: {$meetingNumber}\n"
            . "- Alokasi Waktu: 2 x 45 menit";
    }

    protected function parseRpp(string $raw): array
    {
        $json = $this->extractJson($raw);

        $tujuan = $json['tujuan_pembelajaran'] ?? [];
        if (is_string($tujuan)) {
            $tujuan = array_filter(array_map('trim', preg_split('/[\r\n]+|(?<=[.!?])\s+(?=[A-Z])/', $tujuan)));
        }

        $asesmen = $json['asesmen'] ?? [];
        if (is_string($asesmen)) {
            $asesmen = array_filter(array_map('trim', preg_split('/[\r\n;]+/', $asesmen)));
        }

        return [
            'identitas'             => $json['identitas'] ?? null,
            'tujuan_pembelajaran'   => $tujuan,
            'kegiatan_pendahuluan'  => $json['kegiatan_pendahuluan'] ?? null,
            'kegiatan_inti'         => $json['kegiatan_inti'] ?? null,
            'penutup'               => $json['penutup'] ?? null,
            'asesmen'               => $asesmen,
            'materi'                => $json['materi'] ?? null,
            'media_sumber'          => $json['media_sumber'] ?? null,
        ];
    }

    protected function extractJson(string $raw): array
    {
        $trimmed = trim($raw);
        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) return $decoded;
        }
        if (preg_match('/\{[\s\S]*\}/', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return ['raw' => $raw];
    }

    protected function resolveModel(int $schoolId, ?int $modelId): AiModel
    {
        if ($modelId) {
            return AiModel::where('school_id', $schoolId)
                ->where('id', $modelId)
                ->where('is_active', true)
                ->firstOrFail();
        }
        return AiModel::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('priority')
            ->firstOrFail();
    }

    protected function estimateCost(AiModel $model, int $inputTokens, int $outputTokens): float
    {
        $inCost  = ($inputTokens / 1000) * (float) $model->input_price_per_1k;
        $outCost = ($outputTokens / 1000) * (float) $model->output_price_per_1k;
        return round($inCost + $outCost, 6);
    }
}
