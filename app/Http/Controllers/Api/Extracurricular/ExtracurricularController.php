<?php

namespace App\Http\Controllers\Api\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular\Extracurricular;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtracurricularController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Extracurricular::where('school_id', $request->user()->school_id)
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'icon'          => 'nullable|string|max:200',
            'description'   => 'nullable|string',
            'coach_id'      => 'nullable|integer',
            'schedule'      => 'nullable|array',
            'capacity'      => 'nullable|integer|min:1',
            'fee_per_month' => 'nullable|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);
        $data['school_id'] = $request->user()->school_id;
        return response()->json(Extracurricular::create($data), 201);
    }

    public function enroll(Request $request, int $id): JsonResponse
    {
        $request->validate(['student_id' => 'required|integer']);

        $ekskul = Extracurricular::where('school_id', $request->user()->school_id)->findOrFail($id);

        $enrollment = \DB::table('student_extracurriculars')->updateOrInsert(
            [
                'school_id'           => $request->user()->school_id,
                'extracurricular_id'  => $ekskul->id,
                'student_id'          => $request->input('student_id'),
            ],
            [
                'joined_at'  => now()->toDateString(),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        return response()->json(['enrolled' => true]);
    }

    public function markAttendance(Request $request, int $ekskulId): JsonResponse
    {
        $data = $request->validate([
            'session_date' => 'required|date',
            'attendances'  => 'required|array',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status'     => 'required|in:present,absent,late,excused',
        ]);

        $ekskul = Extracurricular::where('school_id', $request->user()->school_id)->findOrFail($ekskulId);

        foreach ($data['attendances'] as $row) {
            \DB::table('extracurricular_attendances')->updateOrInsert(
                [
                    'extracurricular_id' => $ekskul->id,
                    'student_id'         => $row['student_id'],
                    'session_date'       => $data['session_date'],
                ],
                [
                    'school_id'  => $request->user()->school_id,
                    'status'     => $row['status'],
                    'marked_by'  => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        return response()->json(['ok' => true, 'count' => count($data['attendances'])]);
    }
}
