<?php

namespace App\Http\Controllers\Web\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\DocumentOcrResult;
use App\Services\AI\OcrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrController extends Controller
{
    public function __construct(private OcrService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $results = DocumentOcrResult::where('school_id', $this->schoolId())
            ->orderByDesc('id')
            ->paginate(20);

        return view('school-admin.ai.ocr', ['results' => $results]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $result = $this->service->process($this->schoolId(), auth()->id(), $data['file']);

        if ($result->status === 'completed') {
            return back()->with('success', 'OCR selesai. Teks berhasil diekstrak.');
        }

        return back()->with('success', 'File diunggah, namun OCR gagal: ' . $result->error);
    }

    public function show(DocumentOcrResult $result): View
    {
        abort_unless($result->school_id === $this->schoolId(), 403);

        return view('school-admin.ai.ocr-show', ['result' => $result]);
    }
}
