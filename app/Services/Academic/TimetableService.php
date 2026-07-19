<?php

namespace App\Services\Academic;

use App\Models\Academic\TimetableBreak;
use App\Models\Academic\TimetableSlot;
use Illuminate\Support\Facades\DB;

class TimetableService
{
    public function checkConflict(array $data, ?int $excludeId = null): array
    {
        $schoolId  = auth()->user()->school_id;
        $conflicts = [];

        $timeOverlap = function ($q) use ($data) {
            $q->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            });
        };

        $teacherConflict = TimetableSlot::where('school_id', $schoolId)
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where($timeOverlap)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->with('classSection.classRoom', 'classSection.section', 'subject')
            ->first();

        if ($teacherConflict) {
            $conflicts[] = [
                'type'    => 'teacher_conflict',
                'message' => "Guru sudah mengajar {$teacherConflict->subject->name} pada waktu ini.",
            ];
        }

        $classConflict = TimetableSlot::where('school_id', $schoolId)
            ->where('class_section_id', $data['class_section_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where($timeOverlap)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($classConflict) {
            $conflicts[] = [
                'type'    => 'class_conflict',
                'message' => 'Rombel sudah memiliki jadwal pada waktu ini.',
            ];
        }

        return $conflicts;
    }

    public function store(array $data): TimetableSlot
    {
        $conflicts = $this->checkConflict($data);
        if (!empty($conflicts)) {
            abort(422, $conflicts[0]['message']);
        }

        $data['school_id'] = auth()->user()->school_id;
        return TimetableSlot::create($data);
    }

    public function update(TimetableSlot $slot, array $data): TimetableSlot
    {
        $conflicts = $this->checkConflict(
            array_merge($slot->toArray(), $data),
            $slot->id
        );
        if (!empty($conflicts)) {
            abort(422, $conflicts[0]['message']);
        }

        $slot->update($data);
        return $slot->fresh()->load('subject', 'teacher', 'classSection');
    }

    public function bulkReplace(int $classSectionId, array $slots): int
    {
        return DB::transaction(function () use ($classSectionId, $slots) {
            TimetableSlot::where('class_section_id', $classSectionId)->delete();

            $schoolId = auth()->user()->school_id;
            $count    = 0;
            foreach ($slots as $slot) {
                TimetableSlot::create(array_merge($slot, [
                    'school_id'        => $schoolId,
                    'class_section_id' => $classSectionId,
                ]));
                $count++;
            }
            return $count;
        });
    }

    public function getWeeklyForClass(int $classSectionId): array
    {
        $slots = TimetableSlot::where('class_section_id', $classSectionId)
            ->with('subject', 'teacher', 'classSection.classRoom', 'classSection.section')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn($group) => $group->map(fn($s) => $this->formatSlot($s)))
            ->toArray();

        $breaks = TimetableBreak::orderBy('day_of_week')->orderBy('start_time')->get();

        return ['slots' => $slots, 'breaks' => $breaks];
    }

    public function getWeeklyForTeacher(int $teacherId): array
    {
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->with('subject', 'classSection.classRoom', 'classSection.section')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn($group) => $group->map(fn($s) => $this->formatSlot($s)))
            ->toArray();

        $breaks = TimetableBreak::orderBy('day_of_week')->orderBy('start_time')->get();

        return ['slots' => $slots, 'breaks' => $breaks];
    }

    private function formatSlot(TimetableSlot $slot): array
    {
        return [
            'id'           => $slot->id,
            'subject'      => $slot->subject->name,
            'subject_code' => $slot->subject->code ?? null,
            'teacher'      => $slot->teacher->name,
            'teacher_id'   => $slot->teacher_id,
            'start_time'   => $slot->start_time,
            'end_time'     => $slot->end_time,
            'room'         => $slot->room,
            'day_of_week'  => $slot->day_of_week,
        ];
    }
}
