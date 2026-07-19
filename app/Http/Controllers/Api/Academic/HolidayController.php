<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\SchoolHoliday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SchoolHoliday::orderBy('date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'date'     => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'type'     => 'nullable|in:national,school,regional',
        ]);

        $holiday = SchoolHoliday::create($data);
        return response()->json($holiday, 201);
    }

    public function destroy(SchoolHoliday $holiday): JsonResponse
    {
        $holiday->delete();
        return response()->json(['message' => 'Holiday deleted.']);
    }
}
