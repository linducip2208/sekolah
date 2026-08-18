<?php

namespace App\Services\Facilities;

use App\Models\Facilities\HostelAllocation;
use App\Models\Facilities\HostelBed;
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

            HostelBed::where('student_id', $studentId)
                ->where('status', 'occupied')
                ->update(['status' => 'available', 'student_id' => null]);

            $bed = HostelBed::where('hostel_room_id', $roomId)
                ->where('status', 'available')
                ->first();

            if ($bed) {
                $bed->update(['status' => 'occupied', 'student_id' => $studentId]);
            }

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

    public function checkout(int $studentId): void
    {
        DB::transaction(function () use ($studentId) {
            $allocation = HostelAllocation::where('student_id', $studentId)
                ->where('is_active', true)
                ->first();

            if (!$allocation) {
                abort(422, 'Tidak ada alokasi aktif untuk siswa ini.');
            }

            $roomId = $allocation->hostel_room_id;

            $allocation->update(['is_active' => false, 'to_date' => now()->toDateString()]);

            HostelBed::where('student_id', $studentId)
                ->where('status', 'occupied')
                ->update(['status' => 'available', 'student_id' => null]);

            $occupied = HostelAllocation::where('hostel_room_id', $roomId)->where('is_active', true)->count();
            $room = HostelRoom::findOrFail($roomId);
            $room->update([
                'occupied' => $occupied,
                'status'   => $occupied >= $room->capacity ? 'full' : ($occupied > 0 ? 'partial' : 'available'),
            ]);
        });
    }

    public function allocateBed(int $bedId, int $studentId): void
    {
        DB::transaction(function () use ($bedId, $studentId) {
            $bed = HostelBed::lockForUpdate()->findOrFail($bedId);

            if ($bed->status !== 'available') {
                abort(422, 'Tempat tidur tidak tersedia.');
            }

            HostelBed::where('student_id', $studentId)
                ->where('status', 'occupied')
                ->update(['status' => 'available', 'student_id' => null]);

            $bed->update(['status' => 'occupied', 'student_id' => $studentId]);
        });
    }

    public function deallocateBed(int $bedId): void
    {
        $bed = HostelBed::findOrFail($bedId);
        $bed->update(['status' => 'available', 'student_id' => null]);
    }
}
