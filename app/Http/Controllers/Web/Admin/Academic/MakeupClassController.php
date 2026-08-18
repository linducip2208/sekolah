<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\MakeupClass;
use App\Models\Academic\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MakeupClassController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = MakeupClass::where('school_id', $schoolId)
            ->with(['subject:id,name', 'teacher:id,name', 'classRoom:id,name']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->where('new_date', $request->date);
        }

        return view('school-admin.academic.makeup-classes', [
            'makeups'    => $query->orderByDesc('new_date')->paginate(30)->withQueryString(),
            'subjects'   => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'teachers'   => User::where('school_id', $schoolId)
                ->whereHas('roles', fn($q) => $q->where('name', 'teacher'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'classRooms' => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
            'statuses'   => MakeupClass::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id'          => 'required|exists:subjects,id',
            'teacher_id'          => 'required|exists:users,id',
            'class_room_id'       => 'required|exists:class_rooms,id',
            'new_date'            => 'required|date',
            'new_period_number'   => 'required|integer|min:1',
            'new_room'            => 'nullable|string|max:100',
            'reason'              => 'nullable|string',
            'original_timetable_id' => 'nullable|exists:timetable_slots,id',
        ]);

        $data['school_id']  = $this->schoolId();
        $data['created_by'] = auth()->id();
        $data['status']     = 'scheduled';

        MakeupClass::create($data);

        return back()->with('success', 'Kelas pengganti dijadwalkan.');
    }

    public function complete(MakeupClass $makeup): RedirectResponse
    {
        abort_unless($makeup->school_id === $this->schoolId(), 403);
        $makeup->update(['status' => 'completed']);
        return back()->with('success', 'Kelas pengganti ditandai selesai.');
    }

    public function cancel(MakeupClass $makeup): RedirectResponse
    {
        abort_unless($makeup->school_id === $this->schoolId(), 403);
        $makeup->update(['status' => 'cancelled']);
        return back()->with('success', 'Kelas pengganti dibatalkan.');
    }

    public function destroy(MakeupClass $makeup): RedirectResponse
    {
        abort_unless($makeup->school_id === $this->schoolId(), 403);
        $makeup->delete();
        return back()->with('success', 'Kelas pengganti dihapus.');
    }
}
