<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\ReminderLog;
use App\Models\Communication\ReminderSchedule;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;
        $schedules = ReminderSchedule::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return view('school-admin.communication.reminders.index', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:200',
            'recipient_type'      => 'required|in:parent,student,staff',
            'trigger_days_before'  => 'required|array',
            'trigger_days_before.*' => 'integer|min:0|max:365',
            'reminder_type'       => 'required|in:wa,email,sms',
            'message_template'    => 'required|string',
        ]);

        ReminderSchedule::create([
            'school_id'           => auth()->user()->school_id,
            'name'                => $validated['name'],
            'recipient_type'      => $validated['recipient_type'],
            'trigger_days_before'  => $validated['trigger_days_before'],
            'reminder_type'       => $validated['reminder_type'],
            'message_template'    => $validated['message_template'],
            'is_active'           => true,
        ]);

        return redirect()->route('admin.reminders.index')
            ->with('success', 'Jadwal pengingat berhasil ditambahkan.');
    }

    public function update(Request $request, ReminderSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:200',
            'recipient_type'      => 'required|in:parent,student,staff',
            'trigger_days_before'  => 'required|array',
            'trigger_days_before.*' => 'integer|min:0|max:365',
            'reminder_type'       => 'required|in:wa,email,sms',
            'message_template'    => 'required|string',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.reminders.index')
            ->with('success', 'Jadwal pengingat berhasil diperbarui.');
    }

    public function toggle(ReminderSchedule $schedule): RedirectResponse
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        $status = $schedule->is_active ? 'dilanjutkan' : 'dijeda';
        return back()->with('success', "Pengingat '{$schedule->name}' {$status}.");
    }

    public function destroy(ReminderSchedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal pengingat berhasil dihapus.');
    }

    public function logs(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $logs = ReminderLog::with('schedule:id,name')
            ->where('school_id', $schoolId)
            ->when($request->schedule_id, fn($q) => $q->where('reminder_schedule_id', $request->schedule_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('sent_at')
            ->paginate(50);

        $schedules = ReminderSchedule::where('school_id', $schoolId)->get();

        return view('school-admin.communication.reminders.logs', compact('logs', 'schedules'));
    }

    public function testSend(Request $request, ReminderSchedule $schedule, ReminderService $service): RedirectResponse
    {
        $request->validate([
            'test_phone' => 'required|string|max:30',
        ]);

        $variables = [
            'nama'        => 'Orang Tua (Test)',
            'target_id'   => 0,
            'jumlah'      => 'Rp 500.000',
            'jatuh_tempo'  => now()->addDays(7)->format('d M Y'),
            'link_bayar'   => '#',
            'sekolah'     => config('app.name', 'Sekolah'),
            'kelas'       => '7A',
            'nis'         => '12345',
        ];

        $result = $service->sendReminder($schedule, $variables, $request->test_phone);

        if ($result['success']) {
            return back()->with('success', 'Pesan uji coba berhasil dikirim.');
        }

        return back()->withErrors(['error' => $result['error'] ?? 'Gagal mengirim pesan.']);
    }
}
