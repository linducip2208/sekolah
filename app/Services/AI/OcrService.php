<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\DocumentOcrResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    public function __construct(protected AiAdapterFactory $factory) {}

    /** OCR an uploaded image using the school's active AI provider. */
    public function process(int $schoolId, int $userId, UploadedFile $file): DocumentOcrResult
    {
        $path = $file->store('ocr/' . $schoolId, 'public');

        $base = DocumentOcrResult::create([
            'school_id' => $schoolId,
            'user_id'   => $userId,
            'filename'  => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
            'status'    => 'failed',
            'error'     => 'OCR belum diproses.',
        ]);

        try {
            $model = $this->resolveModel($schoolId);
            $text  = $this->extractWithAi($model, $path, $file->getMimeType());

            $base->update([
                'extracted_text' => $text,
                'status'         => 'completed',
                'error'          => null,
            ]);
        } catch (\Throwable $e) {
            $base->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);
        }

        return $base->fresh();
    }

    protected function extractWithAi(AiModel $model, string $path, string $mime): string
    {
        $provider = $model->provider;
        $adapter  = $this->factory->for($provider, $model);

        $imageData = base64_encode(Storage::disk('public')->get($path));
        $dataUrl   = 'data:' . $mime . ';base64,' . $imageData;

        $messages = [
            ['role' => 'system', 'content' => 'Anda adalah mesin OCR. Ekstrak SEMUA teks dari gambar dengan akurat, pertahankan struktur baris. Balas hanya teks hasil ekstraksi, tanpa komentar.'],
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Ekstrak teks dari gambar ini:'],
                    ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                ],
            ],
        ];

        $result = $adapter->chat($messages, ['temperature' => 0, 'max_tokens' => 4096]);

        return trim($result['text'] ?? '');
    }

    protected function resolveModel(int $schoolId): AiModel
    {
        return AiModel::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('priority')
            ->firstOrFail();
    }
}
