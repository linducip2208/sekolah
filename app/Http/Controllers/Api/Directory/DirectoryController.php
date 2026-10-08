<?php

namespace App\Http\Controllers\Api\Directory;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Semester;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reference directories for mobile pickers and admin lists.
 * All data is school-scoped via route middleware + explicit school_id.
 */
class DirectoryController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'student.view');

        $students = Student::with(['user:id,name,email', 'classSection'])
            ->when($request->input('search'), function ($q, $s) {
                // Grouped OR so the school scope is never bypassed.
                $q->where(fn ($w) => $w
                    ->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                    ->orWhere('admission_no', 'like', "%{$s}%"));
            })
            ->when($request->input('class_section_id'), fn ($q, $id) => $q->where('class_section_id', $id))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($students);
    }

    public function staff(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'staff.view');

        $staff = Staff::with(['user:id,name,email'])
            ->when($request->input('search'), function ($q, $s) {
                $q->where(fn ($w) => $w
                    ->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                    ->orWhere('employee_id', 'like', "%{$s}%"));
            })
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($staff);
    }

    public function classRooms(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ClassRoom::where('school_id', $request->user()->school_id)
                ->with('medium:id,name')->orderBy('name')->get(),
        ]);
    }

    public function sections(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Section::where('school_id', $request->user()->school_id)
                ->orderBy('name')->get(),
        ]);
    }

    public function classSections(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ClassSection::where('school_id', $request->user()->school_id)
                ->with(['classRoom:id,name', 'section:id,name'])
                ->orderBy('id')->get(),
        ]);
    }

    public function subjects(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Subject::where('school_id', $request->user()->school_id)
                ->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function semesters(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Semester::where('school_id', $request->user()->school_id)
                ->orderByDesc('start_date')->get(),
        ]);
    }

    public function mediums(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Medium::where('school_id', $request->user()->school_id)
                ->orderBy('name')->get(),
        ]);
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('super_admin') || $request->user()->can($permission),
            403,
            'Tidak memiliki izin.'
        );
    }
}
