<?php

namespace App\Services;

use App\Models\Academic\Student;
use App\Models\Emergency\EmergencyAlert;
use App\Models\Emergency\EmergencyContact;
use App\Models\Emergency\EmergencyRecipient;
use App\Models\User;
use App\Services\Communication\WhatsAppNotificationService;
use Illuminate\Support\Facades\Log;

class EmergencyAlertService
{
    public function __construct(
        private WhatsAppNotificationService $whatsapp
    ) {}

    public function sendBroadcast(EmergencyAlert $alert): void
    {
        $recipients = $this->resolveRecipients($alert);
        $totalSent = 0;

        foreach ($recipients as $phone) {
            if (empty($phone)) continue;

            $result = $this->whatsapp->send($phone, $alert->message, $alert->school_id);
            if ($result['success']) $totalSent++;

            usleep(20000); // 50/sec throttle
        }

        $alert->update([
            'status'          => 'sent',
            'sent_at'         => now(),
            'recipient_count' => $totalSent,
        ]);
    }

    public function resolveRecipients(EmergencyAlert $alert): array
    {
        $phones = [];
        $schoolId = $alert->school_id;
        $recipientDefs = $alert->recipients;

        foreach ($recipientDefs as $def) {
            switch ($def->recipient_type) {
                case 'all_parents':
                    $students = Student::where('school_id', $schoolId)->with('parents')->get();
                    foreach ($students as $student) {
                        foreach ($student->parents as $parent) {
                            if ($parent->phone) $phones[] = $parent->phone;
                        }
                    }
                    break;

                case 'all_staff':
                    $staffUsers = User::where('school_id', $schoolId)
                        ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'admin', 'accountant', 'nurse']))
                        ->whereNotNull('phone')
                        ->get();
                    foreach ($staffUsers as $user) {
                        if ($user->phone) $phones[] = $user->phone;
                    }
                    break;

                case 'class':
                    if ($def->recipient_id) {
                        $students = Student::where('school_id', $schoolId)
                            ->where('class_section_id', $def->recipient_id)
                            ->with('parents')
                            ->get();
                        foreach ($students as $student) {
                            foreach ($student->parents as $parent) {
                                if ($parent->phone) $phones[] = $parent->phone;
                            }
                        }
                    }
                    break;

                case 'individual':
                    if ($def->recipient_id) {
                        $user = User::find($def->recipient_id);
                        if ($user && $user->phone) $phones[] = $user->phone;
                    }
                    break;
            }
        }

        return array_unique($phones);
    }

    public function sendPanicAlert(int $userId, float $latitude, float $longitude): EmergencyAlert
    {
        $user = User::findOrFail($userId);
        $schoolId = $user->school_id;

        $mapLink = "https://maps.google.com/?q={$latitude},{$longitude}";

        $alert = EmergencyAlert::create([
            'school_id'    => $schoolId,
            'alert_type'   => 'security',
            'title'        => 'PANIC — Darurat Keamanan',
            'message'      => "PANIC ALERT! {$user->name} membutuhkan bantuan segera!\nLokasi: {$mapLink}\nKoord: {$latitude}, {$longitude}\nWaktu: " . now()->format('d/m/Y H:i:s'),
            'triggered_by' => $userId,
            'severity'     => 'critical',
            'status'       => 'draft',
        ]);

        EmergencyRecipient::create([
            'emergency_alert_id' => $alert->id,
            'recipient_type'     => 'all_staff',
        ]);

        $contacts = EmergencyContact::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('priority_order')
            ->get();

        foreach ($contacts as $contact) {
            if ($contact->phone) {
                try {
                    $this->whatsapp->send($contact->phone, $alert->message, $schoolId);
                } catch (\Throwable $e) {
                    Log::warning('Panic alert to contact failed', [
                        'contact' => $contact->name,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->sendBroadcast($alert);

        return $alert;
    }
}
