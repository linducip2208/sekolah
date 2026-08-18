<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private const EVENT_TYPES = [
        'invoice.due'         => 'Tagihan Jatuh Tempo',
        'invoice.overdue'     => 'Tagihan Terlambat',
        'attendance.alert'    => 'Alert Kehadiran',
        'grade.posted'        => 'Nilai Dipublikasikan',
        'exam.scheduled'      => 'Ujian Dijadwalkan',
        'ppdb.status'         => 'Status PPDB',
        'ppdb.new_application' => 'PPDB Pendaftaran Baru',
        'discipline.record'   => 'Catatan Disiplin',
        'announcement.new'    => 'Pengumuman Baru',
        'event.upcoming'      => 'Event Akan Datang',
        'counseling.scheduled' => 'Konseling Dijadwalkan',
        'fee.reminder'        => 'Pengingat SPP',
        'hostel.allocation'   => 'Alokasi Asrama',
        'transport.update'    => 'Update Transportasi',
        'reminder.custom'     => 'Pengingat Kustom',
    ];

    public function index(): View
    {
        $schoolId = $this->schoolId();
        $userId = auth()->id();

        $preferences = NotificationPreference::where('school_id', $schoolId)
            ->where('user_id', $userId)
            ->get()
            ->pluck('event_type')
            ->toArray();

        $existingPrefs = NotificationPreference::where('school_id', $schoolId)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('event_type');

        return view('school-admin.communication.notification-preferences', [
            'eventTypes'     => self::EVENT_TYPES,
            'existingPrefs'  => $existingPrefs,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'event_type'      => 'required|string|max:100',
            'email_enabled'   => 'boolean',
            'push_enabled'    => 'boolean',
            'sms_enabled'     => 'boolean',
            'whatsapp_enabled'=> 'boolean',
        ]);

        $schoolId = $this->schoolId();
        $userId = auth()->id();

        NotificationPreference::updateOrCreate(
            [
                'school_id'  => $schoolId,
                'user_id'    => $userId,
                'event_type' => $data['event_type'],
            ],
            [
                'email_enabled'    => $data['email_enabled'] ?? false,
                'push_enabled'     => $data['push_enabled'] ?? false,
                'sms_enabled'      => $data['sms_enabled'] ?? false,
                'whatsapp_enabled' => $data['whatsapp_enabled'] ?? false,
            ]
        );

        return back()->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }
}
