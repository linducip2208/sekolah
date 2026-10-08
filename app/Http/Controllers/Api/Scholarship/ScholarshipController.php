<?php

namespace App\Http\Controllers\Api\Scholarship;

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;
use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipProgram;
use App\Services\Scholarship\ScholarshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    use ConvertsRupiah;

    public function __construct(private ScholarshipService $service) {}

    public function programs(Request $request): JsonResponse
    {
        $programs = ScholarshipProgram::where('school_id', $request->user()->school_id)
            ->where('is_active', true)->get()->map(function ($p) {
                $row = $p->toArray();
                // Fixed discounts are minor units; percentages stay as-is.
                if (($row['discount_type'] ?? null) === 'fixed' && isset($row['discount_value'])) {
                    $row['discount_value'] = intdiv((int) $row['discount_value'], 100);
                }

                return $row;
            });

        return response()->json(['data' => $programs]);
    }

    public function storeProgram(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:200',
            'source'                => 'required|in:internal_school,external_donor,government,foundation',
            'discount_type'         => 'required|in:percentage,fixed,full',
            'discount_value'        => 'required|integer|min:0',
            'eligibility_criteria'  => 'required|array',
            'open_date'             => 'required|date',
            'close_date'            => 'required|date|after_or_equal:open_date',
            'quota'                 => 'nullable|integer|min:1',
            'required_documents'    => 'nullable|array',
        ]);
        $data['school_id'] = $request->user()->school_id;
        // Mobile contract: whole rupiah for fixed discounts (percent untouched).
        if ($data['discount_type'] === 'fixed') {
            $data['discount_value'] = $this->toCents((int) $data['discount_value']);
        }
        return response()->json($this->inRupiah(ScholarshipProgram::create($data)), 201);
    }

    public function applications(Request $request): JsonResponse
    {
        $apps = ScholarshipApplication::where('school_id', $request->user()->school_id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->paginate(50);

        return response()->json($this->inRupiah($apps));
    }

    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'scholarship_program_id' => 'required|integer',
            'student_id'             => 'required|integer',
            'documents'              => 'nullable|array',
            'motivation'             => 'nullable|string|max:5000',
        ]);

        return response()->json($this->inRupiah($this->service->apply(
            $request->user()->school_id,
            $data['scholarship_program_id'],
            $data['student_id'],
            $data,
        )), 201);
    }

    public function grant(Request $request, int $id): JsonResponse
    {
        $app = ScholarshipApplication::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->inRupiah($this->service->grant($app, $request->user()->id, $request->input('note'))));
    }

    public function applyToInvoice(Request $request, int $applicationId): JsonResponse
    {
        $request->validate(['invoice_id' => 'required|integer']);

        $app = ScholarshipApplication::where('school_id', $request->user()->school_id)
            ->findOrFail($applicationId);

        $invoice = FeeInvoice::where('school_id', $request->user()->school_id)
            ->findOrFail($request->input('invoice_id'));

        try {
            return response()->json($this->service->applyToInvoice($invoice, $app));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
