<?php

namespace App\Services\Communication;

use App\Models\Communication\NotificationProvider;
use App\Services\Communication\Adapters\WhatsAppAdapter;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function send(string $phone, string $message, ?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? auth()->user()?->school_id;

        if (!$schoolId) {
            Log::warning('WhatsApp: no schoolId for notification');
            return ['success' => false, 'error' => 'No school context'];
        }

        $provider = NotificationProvider::where('school_id', $schoolId)
            ->where('transport', 'whatsapp')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if (!$provider) {
            Log::warning('WhatsApp: no active provider configured for school', ['school_id' => $schoolId]);
            return ['success' => false, 'error' => 'No active WhatsApp provider'];
        }

        $adapter = new WhatsAppAdapter($provider);

        return $adapter->send($phone, $message);
    }

    public function sendToStaff(int $staffId, string $message): array
    {
        $staff = \App\Models\Academic\Staff::find($staffId);
        if (!$staff || !$staff->whatsapp_phone) {
            $phone = $staff?->user?->phone ?? null;
            if (!$phone) {
                return ['success' => false, 'error' => 'No WhatsApp number for staff'];
            }
        } else {
            $phone = $staff->whatsapp_phone;
        }

        return $this->send($phone, $message, $staff->school_id);
    }

    public function sendToGuardian(int $studentId, string $message): array
    {
        $student = \App\Models\Academic\Student::with('user')->find($studentId);
        if (!$student) {
            return ['success' => false, 'error' => 'Student not found'];
        }

        $phone = $student->whatsapp_phone ?? $student->guardian_phone ?? $student->user?->phone ?? null;

        if (!$phone) {
            return ['success' => false, 'error' => 'No WhatsApp or guardian phone for student'];
        }

        return $this->send($phone, $message, $student->school_id);
    }
}
