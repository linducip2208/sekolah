<?php

namespace App\Http\Controllers\Api\Foundation;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationAdmin;
use App\Services\Foundation\FoundationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoundationController extends Controller
{
    public function __construct(private FoundationService $service) {}

    public function myFoundations(Request $request): JsonResponse
    {
        $foundationIds = FoundationAdmin::where('user_id', $request->user()->id)
            ->pluck('foundation_id');

        $foundations = Foundation::whereIn('id', $foundationIds)->get();

        return response()->json(['data' => $foundations]);
    }

    public function dashboard(Request $request, int $id): JsonResponse
    {
        $foundation = Foundation::findOrFail($id);

        if (!$this->service->isAdmin($request->user()->id, $foundation->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'metrics' => $this->service->aggregateMetrics($foundation),
            'schools' => $foundation->schools()->select('schools.id','schools.name','schools.subdomain')->get(),
        ]);
    }

    public function schoolDetail(Request $request, int $foundationId, int $schoolId): JsonResponse
    {
        $foundation = Foundation::findOrFail($foundationId);
        if (!$this->service->isAdmin($request->user()->id, $foundation->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $school = $foundation->schools()->where('schools.id', $schoolId)->firstOrFail();

        return response()->json([
            'school'         => $school,
            'students_count' => \App\Models\Academic\Student::where('school_id', $schoolId)->count(),
            'invoices_total' => \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->sum('amount'),
            'invoices_paid'  => \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->sum('paid_amount'),
        ]);
    }
}
