<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\HomeroomTeacher;
use App\Models\Academic\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeroomTeacherController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.academic.homeroom-teachers', [
            'assignments' => HomeroomTeacher::where('school_id', $schoolId)
                ->with(['staff.user:id,name', 'classRoom:id,name'])
                ->orderByDesc('academic_year')
                ->get(),
            'staff'      => Staff::where('school_id', $schoolId)
                ->with('user:id,name')
                ->orderBy('employee_id')
                ->get(),
            'classRooms' => ClassRoom::where('school_id', $schoolId)
                ->orderBy('name')
                ->get(),
            'currentYear' => now()->year . '/' . (now()->year + 1),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'      => 'required|exists:staffs,id',
            'class_room_id' => 'required|exists:class_rooms,id',
            'academic_year' => 'required|string|max:20',
            'start_date'    => 'required|date',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;

        $exists = HomeroomTeacher::where('school_id', $this->schoolId())
            ->where('class_room_id', $data['class_room_id'])
            ->where('academic_year', $data['academic_year'])
            ->whereNull('end_date')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kelas ini sudah punya wali kelas aktif untuk tahun ajaran ini.');
        }

        HomeroomTeacher::create($data);

        return back()->with('success', 'Wali kelas ditugaskan.');
    }

    public function deactivate(HomeroomTeacher $assignment): RedirectResponse
    {
        abort_unless($assignment->school_id === $this->schoolId(), 403);

        $assignment->update([
            'is_active' => false,
            'end_date'  => now()->toDateString(),
        ]);

        return back()->with('success', 'Penugasan wali kelas diakhiri.');
    }

    public function destroy(HomeroomTeacher $assignment): RedirectResponse
    {
        abort_unless($assignment->school_id === $this->schoolId(), 403);
        $assignment->delete();
        return back()->with('success', 'Penugasan wali kelas dihapus.');
    }
}
