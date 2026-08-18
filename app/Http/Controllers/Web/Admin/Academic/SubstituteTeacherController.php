<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\SubstituteTeacher;
use App\Models\Academic\TimetableSlot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubstituteTeacherController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = SubstituteTeacher::where('school_id', $schoolId)
            ->with(['originalTeacher:id,name', 'substituteUser:id,name']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->where('date', $request->date);
        }

        return view('school-admin.academic.substitute-teachers', [
            'substitutes' => $query->orderByDesc('date')->paginate(30)->withQueryString(),
            'teachers'    => User::where('school_id', $schoolId)
                ->whereHas('roles', fn($q) => $q->where('name', 'teacher'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses'    => SubstituteTeacher::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'original_teacher_id'  => 'required|exists:users,id',
            'substitute_teacher_id' => 'required|exists:users,id',
            'timetable_entry_id'   => 'nullable|exists:timetable_slots,id',
            'date'                 => 'required|date',
            'period_number'        => 'nullable|integer|min:1',
            'reason'               => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['status']    = 'pending';

        SubstituteTeacher::create($data);

        return back()->with('success', 'Guru pengganti diajukan.');
    }

    public function approve(SubstituteTeacher $substitute): RedirectResponse
    {
        abort_unless($substitute->school_id === $this->schoolId(), 403);

        $substitute->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Guru pengganti disetujui.');
    }

    public function cancel(SubstituteTeacher $substitute): RedirectResponse
    {
        abort_unless($substitute->school_id === $this->schoolId(), 403);

        $substitute->update(['status' => 'cancelled']);

        return back()->with('success', 'Guru pengganti dibatalkan.');
    }

    public function destroy(SubstituteTeacher $substitute): RedirectResponse
    {
        abort_unless($substitute->school_id === $this->schoolId(), 403);
        $substitute->delete();
        return back()->with('success', 'Data guru pengganti dihapus.');
    }
}
