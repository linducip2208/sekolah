<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Letter;
use App\Models\Communication\LetterTemplate;
use App\Services\LetterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Surat-menyurat (docs §admin — komunikasi).
 * Mirrors Web LetterController validation; numbering via LetterService.
 */
class LetterController extends Controller
{
    private const RECIPIENT_TYPES = ['student', 'staff', 'other'];

    public function __construct(private LetterService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission($request);

        $letters = Letter::where('school_id', $request->user()->school_id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')->paginate(20);

        return response()->json($letters);
    }

    public function templates(Request $request): JsonResponse
    {
        $this->requirePermission($request);

        return response()->json([
            'data' => LetterTemplate::where('school_id', $request->user()->school_id)
                ->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission($request);
        $data = $request->validate([
            'letter_template_id' => 'nullable|exists:letter_templates,id',
            'recipient_type' => 'required|in:' . implode(',', self::RECIPIENT_TYPES),
            'recipient_id' => 'nullable|integer',
            'recipient_name' => 'required|string|max:200',
            'recipient_address' => 'nullable|string|max:1000',
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:50000',
            'status' => 'required|in:draft,sent',
            'notes' => 'nullable|string|max:5000',
        ]);

        $template = null;
        if (! empty($data['letter_template_id'])) {
            $template = LetterTemplate::where('school_id', $request->user()->school_id)
                ->findOrFail($data['letter_template_id']);
        }

        $letter = Letter::create([
            'school_id' => $request->user()->school_id,
            'letter_template_id' => $data['letter_template_id'] ?? null,
            'letter_number' => $this->service->generateLetterNumber(
                $template?->code ?? 'SRM', (string) $request->user()->school_id),
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'] ?? null,
            'recipient_name' => $data['recipient_name'],
            'recipient_address' => $data['recipient_address'] ?? null,
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => $data['status'],
            'issued_by' => $request->user()->id,
            'issued_at' => $data['status'] === 'sent' ? now() : null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json($letter->fresh(), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->requirePermission($request);

        $letter = Letter::where('school_id', $request->user()->school_id)
            ->with('template')->findOrFail($id);

        return response()->json($letter);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->requirePermission($request);
        $data = $request->validate(['status' => 'required|in:draft,sent,archived']);

        $letter = Letter::where('school_id', $request->user()->school_id)->findOrFail($id);
        $letter->update([
            'status' => $data['status'],
            'issued_at' => $data['status'] === 'sent' ? ($letter->issued_at ?? now()) : $letter->issued_at,
        ]);

        return response()->json($letter->fresh());
    }

    private function requirePermission(Request $request): void
    {
        abort_unless(
            $request->user()->hasRole('super_admin')
                || $request->user()->can('school.manage')
                || $request->user()->can('notice.manage'),
            403,
            'Tidak memiliki izin surat-menyurat.'
        );
    }
}
