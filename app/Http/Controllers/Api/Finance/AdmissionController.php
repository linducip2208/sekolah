<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\AdmissionEnquiry;
use App\Services\Finance\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function __construct(private AdmissionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $enquiries = AdmissionEnquiry::when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->get();
        return response()->json($enquiries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_name'  => 'required|string|max:255',
            'father_name'   => 'nullable|string|max:255',
            'mother_name'   => 'nullable|string|max:255',
            'phone'         => 'required|string|max:30',
            'email'         => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'class_applying'=> 'nullable|string|max:100',
            'note'          => 'nullable|string',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(AdmissionEnquiry::create($validated), 201);
    }

    public function update(Request $request, AdmissionEnquiry $admissionEnquiry): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:enquiry,applied,enrolled,rejected',
            'note'   => 'nullable|string',
        ]);
        $admissionEnquiry->update($validated);
        return response()->json($admissionEnquiry->fresh());
    }

    public function enroll(Request $request, AdmissionEnquiry $admissionEnquiry): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
        ]);

        $student = $this->service->enroll($admissionEnquiry, $validated['class_section_id']);
        return response()->json($student, 201);
    }

    public function stats(): JsonResponse
    {
        $stats = AdmissionEnquiry::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        return response()->json($stats);
    }
}
