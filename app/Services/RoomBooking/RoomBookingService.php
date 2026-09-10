<?php

namespace App\Services\RoomBooking;

use App\Models\RoomBooking\BookableRoom;
use App\Models\RoomBooking\RoomBooking;
use App\Models\RoomBooking\RoomBookingRule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class RoomBookingService
{
    public function roomsForSchool(int $schoolId)
    {
        return BookableRoom::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function checkAvailability(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $this->roomForSchool($roomId);
        $query = RoomBooking::where('bookable_room_id', $roomId)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return ! $query->exists();
    }

    public function detectConflicts(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): array
    {
        $this->roomForSchool($roomId);
        $query = RoomBooking::with('user')
            ->where('bookable_room_id', $roomId)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get()->toArray();
    }

    public function createBooking(array $data): RoomBooking
    {
        $schoolId = $this->schoolId();
        $room = $this->roomForSchool($data['bookable_room_id']);
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($data['user_id']);
        $available = $this->checkAvailability(
            $data['bookable_room_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time']
        );

        if (! $available) {
            throw new \RuntimeException('Ruangan tidak tersedia pada waktu yang dipilih.');
        }

        $data['school_id'] = $schoolId;
        $data['status'] = $this->shouldAutoApprove($room->id, $data['user_id'])
            ? 'approved'
            : 'pending';

        $booking = RoomBooking::create($data);

        if ($booking->status === 'approved') {
            $this->syncToCalendar($booking);
        }

        return $booking;
    }

    public function approve(int $bookingId, int $approvedBy): RoomBooking
    {
        $booking = RoomBooking::where('school_id', $this->schoolId())->findOrFail($bookingId);
        User::withoutGlobalScopes()->where('school_id', $booking->school_id)->findOrFail($approvedBy);

        if ($booking->status !== 'pending') {
            throw new \RuntimeException('Hanya booking dengan status pending yang dapat disetujui.');
        }

        if (! $this->checkAvailability(
            $booking->bookable_room_id,
            $booking->date->format('Y-m-d'),
            $booking->start_time,
            $booking->end_time,
            $booking->id
        )) {
            throw new \RuntimeException('Ruangan sudah dibooking pada waktu tersebut.');
        }

        $booking->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
        ]);

        $this->syncToCalendar($booking);

        return $booking;
    }

    public function reject(int $bookingId, string $reason): RoomBooking
    {
        $booking = RoomBooking::where('school_id', $this->schoolId())->findOrFail($bookingId);

        if ($booking->status !== 'pending') {
            throw new \RuntimeException('Hanya booking dengan status pending yang dapat ditolak.');
        }

        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $booking;
    }

    public function cancel(int $bookingId, int $userId): RoomBooking
    {
        $booking = RoomBooking::where('school_id', $this->schoolId())->findOrFail($bookingId);

        if ($booking->user_id !== $userId && ! auth()->user()->hasAnyRole(['admin'])) {
            throw new \RuntimeException('Anda hanya dapat membatalkan booking Anda sendiri.');
        }

        if (! in_array($booking->status, ['pending', 'approved'])) {
            throw new \RuntimeException('Booking ini tidak dapat dibatalkan.');
        }

        $booking->update(['status' => 'cancelled']);

        if ($booking->calendar_event_id) {
            DB::table('academic_events')->where('id', $booking->calendar_event_id)->delete();
        }

        return $booking;
    }

    public function generateRecurringBookings(array $baseData): array
    {
        $bookings = [];
        $startDate = Carbon::parse($baseData['date']);
        $endDate = Carbon::parse($baseData['recurring_until']);

        $pattern = $baseData['recurring_pattern'];
        $interval = match ($pattern) {
            'weekly' => '1 week',
            'biweekly' => '2 weeks',
            'monthly' => '1 month',
            default => '1 week',
        };

        $period = CarbonPeriod::create($startDate, $interval, $endDate);

        foreach ($period as $date) {
            if ($date->equalTo($startDate)) {
                continue;
            }

            $data = array_merge($baseData, [
                'date' => $date->format('Y-m-d'),
                'is_recurring' => false,
                'recurring_pattern' => null,
                'recurring_until' => null,
            ]);

            if ($this->checkAvailability($data['bookable_room_id'], $data['date'], $data['start_time'], $data['end_time'])) {
                $data['status'] = $this->shouldAutoApprove($data['bookable_room_id'], $data['user_id'])
                    ? 'approved'
                    : 'pending';
                $data['school_id'] = $this->schoolId();
                $booking = RoomBooking::create($data);
                if ($booking->status === 'approved') {
                    $this->syncToCalendar($booking);
                }
                $bookings[] = $booking;
            }
        }

        return $bookings;
    }

    public function getRoomRules(int $roomId): array
    {
        $this->roomForSchool($roomId);

        return RoomBookingRule::where('school_id', $this->schoolId())
            ->where('bookable_room_id', $roomId)->get()->toArray();
    }

    public function saveRoomRules(int $roomId, array $rules): void
    {
        $this->roomForSchool($roomId);
        $schoolId = $this->schoolId();
        RoomBookingRule::where('school_id', $schoolId)->where('bookable_room_id', $roomId)->delete();

        foreach ($rules as $rule) {
            RoomBookingRule::create([
                'school_id' => $schoolId,
                'bookable_room_id' => $roomId,
                'rule_type' => $rule['rule_type'],
                'rule_value' => $rule['rule_value'],
            ]);
        }
    }

    public function getBookingsForDateRange(string $start, string $end): array
    {
        return RoomBooking::with(['room', 'user'])
            ->where('school_id', auth()->user()->school_id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->toArray();
    }

    public function getPendingApprovals(): array
    {
        return RoomBooking::with(['room', 'user'])
            ->where('school_id', auth()->user()->school_id)
            ->where('status', 'pending')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }

    public function getMyBookings(int $userId): array
    {
        return RoomBooking::with('room')
            ->where('school_id', $this->schoolId())
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }

    protected function shouldAutoApprove(int $roomId, int $userId): bool
    {
        $schoolId = $this->schoolId();
        $rules = RoomBookingRule::where('school_id', $schoolId)
            ->where('bookable_room_id', $roomId)->get();

        foreach ($rules as $rule) {
            if ($rule->rule_type === 'allowed_roles') {
                $user = User::withoutGlobalScopes()
                    ->where('school_id', $schoolId)->find($userId);
                $allowedRoles = explode(',', $rule->rule_value);
                if (! $user || ! $user->hasAnyRole($allowedRoles)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function syncToCalendar(RoomBooking $booking): void
    {
        if ($booking->calendar_event_id) {
            DB::table('academic_events')->where('id', $booking->calendar_event_id)->delete();
        }

        $eventId = DB::table('academic_events')->insertGetId([
            'school_id' => $booking->school_id,
            'title' => 'Room: '.$booking->title,
            'description' => $booking->purpose ?? 'Booking ruangan '.$booking->room?->name,
            'start_date' => $booking->date->format('Y-m-d').' '.$booking->start_time,
            'end_date' => $booking->date->format('Y-m-d').' '.$booking->end_time,
            'event_type' => 'room_booking',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $booking->updateQuietly(['calendar_event_id' => $eventId]);
    }

    private function schoolId(): int
    {
        return (int) auth()->user()->school_id;
    }

    private function roomForSchool(int $roomId): BookableRoom
    {
        return BookableRoom::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($roomId);
    }
}
