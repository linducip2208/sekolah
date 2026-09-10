<?php

namespace App\Http\Controllers\Api\Extracurricular;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Extracurricular\Extracurricular;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'name' => 'required|string|max:200',
            'icon' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'coach_id' => 'nullable|integer',
            'schedule' => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
            'fee_per_month' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if (! empty($data['coach_id'])) {
            User::withoutGlobalScopes()->where('school_id', $request->user()->school_id)->findOrFail($data['coach_id']);
        }
        $data['school_id'] = $request->user()->school_id;

        return response()->json(Extracurricular::create($data), 201);
    }

    public function enroll(Request $request, int $id): JsonResponse
    {
        $request->validate(['student_id' => 'required|integer']);

        $schoolId = (int) $request->user()->school_id;
        $studentId = (int) $request->input('student_id');
        Student::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($studentId);
        $ekskul = Extracurricular::where('school_id', $schoolId)->findOrFail($id);

        DB::transaction(function () use ($schoolId, $studentId, $ekskul) {
            $existing = DB::table('student_extracurriculars')
                ->where('school_id', $schoolId)
                ->where('extracurricular_id', $ekskul->id)
                ->where('student_id', $studentId)
                ->lockForUpdate()
                ->first();
            if ($existing?->is_active) {
                return;
            }

            if ($ekskul->capacity !== null) {
                $activeCount = DB::table('student_extracurriculars')
                    ->where('school_id', $schoolId)
                    ->where('extracurricular_id', $ekskul->id)
                    ->where('is_active', true)
                    ->count();
                abort_if($activeCount >= $ekskul->capacity, 422, 'Kuota ekstrakurikuler sudah penuh.');
            }

            $values = [
                'joined_at' => now()->toDateString(),
                'is_active' => true,
                'left_at' => null,
                'updated_at' => now(),
            ];
            if ($existing) {
                DB::table('student_extracurriculars')->where('id', $existing->id)->update($values);
            } else {
                DB::table('student_extracurriculars')->insert($values + [
                    'school_id' => $schoolId,
                    'extracurricular_id' => $ekskul->id,
                    'student_id' => $studentId,
                    'created_at' => now(),
                ]);
            }
        });

        return response()->json(['enrolled' => true]);
    }

    public function markAttendance(Request $request, int $ekskulId): JsonResponse
    {
        $data = $request->validate([
            'session_date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|integer',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
        ]);

        $ekskul = Extracurricular::where('school_id', $request->user()->school_id)->findOrFail($ekskulId);

        $schoolId = (int) $request->user()->school_id;
        DB::transaction(function () use ($data, $ekskul, $schoolId, $request) {
            foreach ($data['attendances'] as $row) {
                Student::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($row['student_id']);
                abort_unless(DB::table('student_extracurriculars')
                    ->where('school_id', $schoolId)
                    ->where('extracurricular_id', $ekskul->id)
                    ->where('student_id', $row['student_id'])
                    ->where('is_active', true)
                    ->exists(), 422, 'Siswa belum terdaftar pada ekstrakurikuler ini.');

                DB::table('extracurricular_attendances')->updateOrInsert(
                    [
                        'extracurricular_id' => $ekskul->id,
                        'student_id' => $row['student_id'],
                        'session_date' => $data['session_date'],
                    ],
                    [
                        'school_id' => $schoolId,
                        'status' => $row['status'],
                        'marked_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });

        return response()->json(['ok' => true, 'count' => count($data['attendances'])]);
    }
}
