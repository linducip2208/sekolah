<?php

namespace App\Services\Gate;

use App\Models\Gate\IdGateDevice;
use App\Models\Gate\IdGateEvent;
use App\Models\Gate\StudentIdCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdGateService
{
    public function authenticateDevice(string $token): ?IdGateDevice
    {
        return IdGateDevice::where('is_active', true)
            ->get()
            ->first(fn (IdGateDevice $d) => hash_equals((string) $d->device_token, $token));
    }

    public function scan(IdGateDevice $device, string $cardOrToken, string $direction): IdGateEvent
    {
        $card = StudentIdCard::where('school_id', $device->school_id)
            ->where('is_active', true)
            ->where(function ($q) use ($cardOrToken) {
                $q->where('card_uid', $cardOrToken)
                  ->orWhere('qr_token', $cardOrToken);
            })
            ->firstOrFail();

        $user = User::whereHas('student', fn ($q) =>
            $q->where('id', $card->student_id))->first();

        $event = IdGateEvent::create([
            'school_id'         => $device->school_id,
            'id_gate_device_id' => $device->id,
            'user_id'           => $user?->id,
            'direction'         => $direction,
            'scanned_at'        => now(),
        ]);

        \App\Jobs\NotifyParentGateScanJob::dispatch($event->id);

        return $event;
    }

    public function issueCard(int $schoolId, int $studentId): StudentIdCard
    {
        return DB::transaction(function () use ($schoolId, $studentId) {
            StudentIdCard::where('student_id', $studentId)->update(['is_active' => false]);

            return StudentIdCard::create([
                'school_id'  => $schoolId,
                'student_id' => $studentId,
                'card_uid'   => 'EC-' . strtoupper(Str::random(12)),
                'qr_token'   => Str::random(64),
                'is_active'  => true,
                'issued_at'  => now(),
            ]);
        });
    }

    public function rotateQrToken(StudentIdCard $card): StudentIdCard
    {
        $card->update(['qr_token' => Str::random(64)]);
        return $card->fresh();
    }

    public function eventsForUser(int $userId, int $days = 30)
    {
        return IdGateEvent::where('user_id', $userId)
            ->where('scanned_at', '>=', now()->subDays($days))
            ->orderByDesc('scanned_at')
            ->get();
    }
}
