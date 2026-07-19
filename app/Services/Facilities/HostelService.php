<?php

namespace App\Services\Facilities;

use App\Models\Facilities\HostelAllocation;
use App\Models\Facilities\HostelRoom;
use Illuminate\Support\Facades\DB;

class HostelService
{
    public function allocate(int $studentId, int $roomId, string $fromDate): HostelAllocation
    {
        return DB::transaction(function () use ($studentId, $roomId, $fromDate) {
            $room = HostelRoom::lockForUpdate()->findOrFail($roomId);

            if ($room->occupied >= $room->capacity) {
                abort(422, 'Kamar sudah penuh.');
            }

            HostelAllocation::where('student_id', $studentId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $allocation = HostelAllocation::create([
                'school_id'       => auth()->user()->school_id,
                'student_id'      => $studentId,
                'hostel_room_id'  => $roomId,
                'from_date'       => $fromDate,
                'is_active'       => true,
            ]);

            $occupied = HostelAllocation::where('hostel_room_id', $roomId)->where('is_active', true)->count();
            $room->update([
                'occupied' => $occupied,
                'status'   => $occupied >= $room->capacity ? 'full' : ($occupied > 0 ? 'partial' : 'available'),
            ]);

            return $allocation->load('room.hostel', 'student.user');
        });
    }
}
