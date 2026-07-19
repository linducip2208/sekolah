<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Notice;
use App\Services\Communication\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(private NoticeService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->getForUser());
    }

    public function all(): JsonResponse
    {
        return response()->json(Notice::with('creator')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'content'               => 'required|string',
            'target_roles'          => 'nullable|array',
            'target_class_sections' => 'nullable|array',
            'publish_at'            => 'nullable|date',
            'expire_at'             => 'nullable|date|after:publish_at',
            'is_published'          => 'sometimes|boolean',
        ]);

        $validated['school_id']  = auth()->user()->school_id;
        $validated['created_by'] = auth()->id();
        return response()->json(Notice::create($validated), 201);
    }

    public function update(Request $request, Notice $notice): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'content'      => 'sometimes|string',
            'is_published' => 'sometimes|boolean',
            'publish_at'   => 'nullable|date',
            'expire_at'    => 'nullable|date',
        ]);
        $notice->update($validated);
        return response()->json($notice->fresh());
    }

    public function destroy(Notice $notice): JsonResponse
    {
        $notice->delete();
        return response()->json(['message' => 'Notice deleted.']);
    }
}
