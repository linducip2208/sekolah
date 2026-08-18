<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\PtmSchedule;
use App\Models\Academic\Student;
use App\Models\User;
use App\Services\Notification\FcmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PtmScheduleController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $query = PtmSchedule::where('school_id', $this->schoolId())
            ->with(['student.user', 'parent', 'teacher', 'classRoom'])
            ->orderByDesc('meeting_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('date_from')) {
            $query->where('meeting_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('meeting_date', '<=', $request->date_to);
        }

        $schedules = $query->paginate(20)->withQueryString();

        $teachers = User::where('school_id', $this->schoolId())
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'homeroom_teacher', 'admin']))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('school-admin.academic.ptm-schedules', compact('schedules', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'parent_user_id' => 'required|exists:users,id',
            'teacher_id'     => 'required|exists:users,id',
            'class_room_id'  => 'nullable|exists:class_rooms,id',
            'meeting_date'   => 'required|date|after_or_equal:today',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i|after:start_time',
            'notes'          => 'nullable|string|max:5000',
        ]);

        PtmSchedule::create([
            'school_id'      => $this->schoolId(),
            'student_id'     => $data['student_id'],
            'parent_user_id' => $data['parent_user_id'],
            'teacher_id'     => $data['teacher_id'],
            'class_room_id'  => $data['class_room_id'] ?? null,
            'meeting_date'   => $data['meeting_date'],
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'scheduled',
        ]);

        return redirect()->route('admin.ptm-schedules.index')->with('success', 'Jadwal PTM berhasil dibuat.');
    }

    public function update(Request $request, PtmSchedule $ptmSchedule): RedirectResponse
    {
        abort_unless($ptmSchedule->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'meeting_date' => 'required|date',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'nullable|date_format:H:i|after:start_time',
            'status'       => 'required|in:scheduled,completed,cancelled,no_show',
            'notes'        => 'nullable|string|max:5000',
            'follow_up'    => 'nullable|string|max:5000',
        ]);

        $ptmSchedule->update($data);

        return back()->with('success', 'Jadwal PTM diperbarui.');
    }

    public function destroy(PtmSchedule $ptmSchedule): RedirectResponse
    {
        abort_unless($ptmSchedule->school_id === $this->schoolId(), 403);
        $ptmSchedule->delete();

        return back()->with('success', 'Jadwal PTM dihapus.');
    }

    public function sendReminders(FcmService $fcm): RedirectResponse
    {
        $upcoming = PtmSchedule::where('school_id', $this->schoolId())
            ->where('status', 'scheduled')
            ->where('meeting_date', '<=', now()->addDays(7)->toDateString())
            ->where('reminder_sent', false)
            ->with(['student.user', 'parent', 'teacher'])
            ->get();

        $sent = 0;

        foreach ($upcoming as $schedule) {
            $dateFormatted = $schedule->meeting_date->format('d M Y');
            $timeFormatted = $schedule->start_time;

            $teacherName = $schedule->teacher->name ?? 'Guru';
            $parentName  = $schedule->parent->name ?? 'Orang Tua';
            $studentName = $schedule->student->user->name ?? 'Siswa';

            $teacherTitle = "Ingat: PTM dengan {$parentName} (siswa {$studentName})";
            $teacherBody  = "Jadwal PTM pada {$dateFormatted} pukul {$timeFormatted}";

            $parentTitle = "Ingat: PTM dengan Guru {$teacherName}";
            $parentBody  = "Jadwal PTM {$studentName} pada {$dateFormatted} pukul {$timeFormatted}";

            $teacherUserId = $schedule->teacher_id;
            $parentUserId  = $schedule->parent_user_id;

            $fcm->logAndSend($this->schoolId(), [$teacherUserId], 'ptm_reminder', $teacherTitle, $teacherBody, [
                'type' => 'ptm_reminder',
                'ptm_schedule_id' => $schedule->id,
            ]);

            $fcm->logAndSend($this->schoolId(), [$parentUserId], 'ptm_reminder', $parentTitle, $parentBody, [
                'type' => 'ptm_reminder',
                'ptm_schedule_id' => $schedule->id,
            ]);

            $schedule->update(['reminder_sent' => true]);
            $sent++;
        }

        return back()->with('success', "{$sent} pengingat PTM berhasil dikirim.");
    }
}
