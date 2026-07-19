<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Services\Academic\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function __construct(private AcademicYearService $service) {}

    public function index(): JsonResponse
    {
        return response()->json(AcademicYear::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $year = AcademicYear::create($data);
        return response()->json($year, 201);
    }

    public function update(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:50',
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date|after:start_date',
        ]);

        $academicYear->update($data);
        return response()->json($academicYear->fresh());
    }

    public function activate(AcademicYear $academicYear): JsonResponse
    {
        $result = $this->service->activate($academicYear->id);
        return response()->json($result);
    }
}
