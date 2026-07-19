<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Visitor\VisitorLog;
use App\Models\Visitor\VisitorQrSession;
use App\Services\Communication\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorRegistrationController extends Controller
{
    public function showForm(): View
    {
        $staff = Staff::with('user:id,name')->orderBy('id')->get();
        return view('visitor.register', compact('staff'));
    }

    public function submit(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'visitor_name'  => 'required|string|max:200',
            'phone'         => 'required|string|max:30',
            'purpose'       => 'required|string|max:200',
            'host_staff_id' => 'nullable|exists:staff,id',
            'expected_arrival' => 'required|date',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        $schoolId = Staff::find($validated['host_staff_id'])?->school_id;

        if (!$schoolId) {
            $schoolId = \App\Models\School::first()?->id ?? 1;
        }

        $qrToken = bin2hex(random_bytes(32));

        $visitor = VisitorLog::create([
            'school_id'        => $schoolId,
            'visitor_name'     => $validated['visitor_name'],
            'phone'            => $validated['phone'],
            'purpose'          => $validated['purpose'],
            'host_staff_id'    => $validated['host_staff_id'],
            'expected_arrival' => $validated['expected_arrival'],
            'vehicle_plate'    => $validated['vehicle_plate'] ?? null,
            'pre_registered'   => true,
            'status'           => 'pending',
            'qr_code'          => $qrToken,
        ]);

        VisitorQrSession::create([
            'visitor_log_id' => $visitor->id,
            'qr_token'       => $qrToken,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        try {
            $hostStaff = Staff::with('user')->find($validated['host_staff_id']);
            $wa = app(WhatsAppNotificationService::class);

            $wa->send($validated['phone'], "✅ *Pendaftaran Kunjungan*\n\nHalo {$validated['visitor_name']}, kunjungan Anda ke sekolah telah terdaftar.\n\n📅 Tanggal: " . date('d M Y H:i', strtotime($validated['expected_arrival'])) . "\n🎯 Tujuan: {$validated['purpose']}\n\nQR Code Anda akan digunakan saat check-in di gerbang.", $schoolId);

            if ($hostStaff?->whatsapp_phone) {
                $wa->send($hostStaff->whatsapp_phone, "🔔 *Tamu Baru Terdaftar*\n\n{$validated['visitor_name']} akan berkunjung pada " . date('d M Y H:i', strtotime($validated['expected_arrival'])) . "\nTujuan: {$validated['purpose']}\nNo HP: {$validated['phone']}", $schoolId);
            }
        } catch (\Throwable $e) {
        }

        $qrDataUrl = $this->generateQrDataUri($qrToken);

        return view('visitor.register-success', [
            'visitor'  => $visitor,
            'qrDataUrl' => $qrDataUrl,
        ]);
    }

    private function generateQrDataUri(string $token): string
    {
        $data = 'https://chart.googleapis.com/chart?cht=qr&chs=250x250&chl=' . urlencode($token) . '&choe=UTF-8';
        return $data;
    }
}
